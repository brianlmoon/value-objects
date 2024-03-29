<?php

namespace Moonspot\ValueObjects\Tests;

use Moonspot\ValueObjects\TypedArray;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class ExampleTypedPropertySet extends TypedArray {
    public const REQUIRED_TYPE = [
        ExampleTypedProperty::class
    ];
}
