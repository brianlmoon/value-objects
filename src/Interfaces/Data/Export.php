<?php

namespace Moonspot\ValueObjects\Interfaces\Data;

/**
 * Defines an interface for exporting and importing data
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\BusinessObjects
 */
interface Export {
    /**
     * Returns an array representation of the object.
     *
     * @return     array  Array representation of the object.
     */
    public function toArray(): array;

    /**
     * Builds a new object from an array
     *
     * @param      array   $data   The data
     *
     * @return     object
     */
    public function fromArray(array $data): object;

    /**
     * Implment the JsonSerializable interface function so any
     * calls to json_encode on an Export object will return the same
     * value as toJson
     *
     * @return     array
     */
    public function jsonSerialize(): array;

    /**
     * Returns a json representation of the object.
     *
     * @return     string  Json representation of the object.
     */
    public function toJson(): string;

    /**
     * Builds a new object from JSON
     *
     * @param      string  $data   The data
     *
     * @return     object
     */
    public function fromJson(string $data): object;
}
