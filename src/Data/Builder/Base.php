<?php

namespace Moonspot\ValueObjects\Data\Builder;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
abstract class Base {
    abstract public function create(array $data);

    public static function build(array $data) {
        static $builder;
        if (empty($builder)) {
            $class   = get_called_class();
            $builder = new $class();
        }

        return $builder->create($data);
    }

    /**
     * Set the property $key on the object $obj by finding the first valid
     * value in array $data by searching the array keys named in $data_key.
     *
     * @param object $obj                   Object which property we are setting.
     * @param string $key                   Property name being set.
     * @param array  $data                  Data to search for set value.
     * @param array  $data_key              Keys to match for value, use first found.
     * @param bool   $return_not_null       Values of not null will be considered found,
     *                                       otherwise we consider not empty as found.
     */
    protected function setValue(object $obj, string $key, array $data, array $data_key = [], bool $return_not_null = true): void {
        array_push($data_key, $key);
        foreach ($data_key as $dk) {
            if (array_key_exists($dk, $data)) {
                $obj->$key = $data[$dk];
                if ($return_not_null) {
                    if ($data[$dk] !== null) {
                        break;
                    }
                } elseif (!empty($data[$dk])) {
                    break;
                }
            }
        }
    }

    protected function getValue($key, array $data): mixed {
        $value = null;
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
        }

        return $value;
    }
}
