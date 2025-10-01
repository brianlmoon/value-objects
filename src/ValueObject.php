<?php

namespace Moonspot\ValueObjects;

use Moonspot\ValueObjects\Interfaces\Export;

/**
 * Abstract Value Object Class
 *
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
abstract class ValueObject implements Export, \JsonSerializable {

    /**
     * Name of the property which represents a unique identifier
     * for objects of a given type. E.g. the primary key for data
     * stored in an RDBMS or document id for objects stored in a
     * document store.
     *
     * @var        string
     */
    public const UNIQUE_ID_FIELD = '';

    /**
     * Returns an array representation of the object.
     *
     * @throws     \LogicException  (description)
     *
     * @return     array            Array representation of the object.
     */
    public function toArray(?array $data = null): array {
        $data ??= (array)$this;
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                if ($value instanceof Export) {
                    $data[$key] = $value->toArray();
                } else {
                    throw new \LogicException("Propety $key does not implement the Export interface");
                }
            }
        }

        return $data;
    }

    /**
     * Replaces the exisiting data in the object with data in the array
     *
     * @param      array        $data   The data
     *
     * @throws     \LogicException  (description)
     *
     * @return     self
     */
    public function fromArray(array $data): object {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if (isset($this->$key) && is_object($this->$key)) {
                    if ($this->$key instanceof Export) {
                        $obj        = $this->$key;
                        $this->$key = $obj->fromArray($value);
                    } else {
                        throw new \LogicException("Propety $key does not implement the Export interface");
                    }
                } else {
                    try {
                        $this->$key = $value;
                    } catch (\TypeError $e) {
                        // ignore trying to set values to null that are not nullable
                        if($value !== null) {
                            throw $e;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Implment the JsonSerializable interface function so any
     * calls to json_encode on a ValueObject will return the same
     * value as toJson
     *
     * @return     array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }

    /**
     * Returns a json representation of the object.
     *
     * @return     string  Json representation of the object.
     */
    public function toJson(): string {
        return json_encode($this->toArray());
    }

    /**
     * Replaces the exisiting data in the object with data from JSON
     *
     * @param      string        $data   The data
     *
     * @return     self
     */
    public function fromJson(string $data): object {
        return $this->fromArray(json_decode($data, true));
    }

    /**
     * Returns a yaml representation of the object.
     *
     * @return     string  Yaml representation of the object.
     */
    public function toYaml(): string {
        return yaml_emit($this->toArray());
    }

    /**
     * Replaces the exisiting data in the object with data from YAML
     *
     * @param      string        $data   The data
     *
     * @return     self
     */
    public function fromYaml(string $data): object {
        return $this->fromArray(yaml_parse($data));
    }
}
