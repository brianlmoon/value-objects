<?php

namespace Moonspot\ValueObjects\Data;

use Moonspot\ValueObjects\Interfaces\Data\Export;

/**
 * Class TypedArray
 *
 * A child of this class must declare one type of variable. Any item placed
 * into a child object must match that required type.
 *
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
abstract class TypedArray extends ArrayObject {

    /**
     * Required type(s) for values added to the ArrayObject
     *
     * Built in PHP types supported by the settype() function and class
     * names are supported.
     *
     * @var        array
     */
    public const REQUIRED_TYPE = [];

    /**
     * Constructs a new instance.
     */
    public function __construct() {
        // don't allow access to the parent constructor
        parent::__construct();
    }

    /**
     * Part of the ArrayObject requirements and allows for array access of this object.
     *
     * @param mixed $value
     *
     * @throws \UnexpectedValueException
     */
    public function append($value) {
        $value = $this->filterType($value, $this::REQUIRED_TYPE);
        parent::append($value);
    }

    /**
     * Part of the ArrayObject requirements and allows for array access of this object.
     *
     * @param mixed $values
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function exchangeArray($values): array {
        foreach ($values as $k => $value) {
            $values[$k] = $this->filterType($value, $this::REQUIRED_TYPE);
        }

        return parent::exchangeArray($values);
    }

    /**
     * Creates a copy of the ArrayObject
     *
     * @return     array
     */
    public function getArrayCopy(): array {
        $output = parent::getArrayCopy();

        foreach ($output as $key => $value) {
            if (is_object($value)) {
                if (is_subclass_of($value, Export::class)) {
                    $output[$key] = $value->toArray();
                } else {
                    throw new \LogicException("Propety $key of type " . get_class($value) . ' does not implement the Export interface');
                }
            }
        }

        return $output;
    }

    /**
     * Part of the ArrayObject requirements and allows for array access of this object.
     *
     * @param mixed $index
     * @param mixed $value
     *
     * @throws \UnexpectedValueException
     */
    public function offsetSet($index, $value) {
        $value = $this->filterType($value, $this::REQUIRED_TYPE);
        parent::offsetSet($index, $value);
    }

    /**
     * Takes another TypedArray and tries to merge in any data that isn't already in the current object.
     * Matches by value (not strict), and only appends unique data.  If the data is already found in the current object,
     * it is skipped.  Note that this ignores all keys and only looks at data, but will try and set a key if it has one and it isn't already set.
     *
     * @param TypedArray $input
     *
     * @throws \UnexpectedValueException
     */
    public function mergeInUniqueData(TypedArray $input) {
        foreach ($input as $key => $item) {
            $item = $this->filterType($item, $this::REQUIRED_TYPE);

            if (!in_array($item, parent::getArrayCopy())) {
                if ($this->offsetExists($key)) {
                    $this->append($item);
                } else {
                    $this->offsetSet($key, $item);
                }
            }
        }
    }

    /**
     * Takes another TypedArray and tries to merge the data with the same rules as array_merge.
     *
     * Specifically:
     *      "If the input arrays have the same string keys, then the [input] value for that key will overwrite the [current] one.
     *      If, however, the arrays contain numeric keys, the later value will not overwrite the original value, but will be appended."
     *
     * @param TypedArray $input
     *
     * @throws \UnexpectedValueException
     */
    public function mergeIn(TypedArray $input) {
        foreach ($input as $key => $item) {
            $item = $this->filterType($item, $this::REQUIRED_TYPE);

            if (!is_int($key)) {
                $this->offsetSet($key, $item);
            } else {
                $this->append($item);
            }
        }
    }

    /**
     * Filters the given value for the given type
     *
     * @param      mixed      $value  The value
     * @param      array      $types  The types
     *
     * @throws     \UnexpectedValueException
     *
     * @return     mixed
     */
    protected function filterType(mixed $value, array $types): mixed {
        $new_value = $value;

        foreach ($types as $type) {

            switch ($type) {

                case 'array':
                    if (!is_array($value)) {
                        $new_value = null;
                        if (is_object($value) && $value instanceof \ArrayObject) {
                            $new_value = $value->getArrayCopy();
                        }
                    }
                    break;

                case 'boolean':
                    if (!is_bool($value)) {
                        $new_value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    }
                    break;

                case 'float':
                case 'double':
                    // normalize to value that gettype returns
                    $type = 'double';
                    if (!is_float($value)) {
                        $new_value = filter_var($value, FILTER_VALIDATE_FLOAT);
                    }
                    if ($new_value === false) {
                        $new_value = null;
                    }
                    break;

                case 'integer':
                    if (!is_int($value)) {
                        $int_value = (int)$value;
                        if ($value == $int_value) {
                            $new_value = $int_value;
                        } else {
                            $new_value = filter_var($value, FILTER_VALIDATE_INT);
                        }
                    }
                    if ($new_value === false) {
                        $new_value = null;
                    }
                    break;

                case 'string':
                    if (!is_scalar($value)) {
                        $new_value = null;
                    } else {
                        $new_value = (string)$value;
                    }
                    break;

                default:
                    // assume we have a class name
                    if (is_array($value) && is_subclass_of($type, Export::class)) {
                        $new_value = new $type();
                        $new_value->fromArray($value);
                    } else {
                        $new_value = null;
                    }
                    break;
            }

            if ($new_value !== null) {
                $value = $new_value;
                break;
            }
        }

        $valid = is_null($value) ||
                 gettype($value) == $type ||
                 (
                     is_object($value) &&
                    $value instanceof $type // @phan-suppress-current-line PhanUndeclaredClass
                 );

        if (!$valid) {
            throw new \UnexpectedValueException(
                get_class($this) . ' only accepts values that are of type ' . $type . ', ' .
                (is_object($value) ? get_class($value) : gettype($value)) . ' given.'
            );
        }

        return $value;
    }
}
