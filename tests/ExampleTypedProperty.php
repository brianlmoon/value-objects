<?php

namespace Moonspot\ValueObjects\Tests;

use Moonspot\ValueObjects\ValueObject;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class ExampleTypedProperty extends ValueObject {
    public ?string $name = null;
    public ExampleTypedSubProperty $hire_date;
    public ?string $position = null;
    public ?array $array_a   = null;
    public ?bool $boolean_a  = null;
    public ?float $float_a   = null;
    public ?int $int_a       = null;

    public function __construct() {
        $this->hire_date = new ExampleTypedSubProperty();
    }
}
