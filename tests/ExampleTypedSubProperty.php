<?php

namespace Moonspot\ValueObjects\Tests;

use Moonspot\ValueObjects\ValueObject;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class ExampleTypedSubProperty extends ValueObject {
    public ?string $time                = null;
    public ?string $date                = null;
    public ?bool $daylight_savings_time = null;
}
