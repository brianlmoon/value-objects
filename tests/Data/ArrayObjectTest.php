<?php

namespace Moonspot\ValueObjects\Tests\Data;

use Moonspot\ValueObjects\Data\ArrayObject;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class ArrayObjectTest extends \PHPUnit\Framework\TestCase {
    public function testBehavior() {
        $arr = new class extends ArrayObject {
        };
        $arr->fromArray([1]);
        $arr[] = 2;
        $arr[] = new ExampleTypedProperty();

        $this->assertSame(1, $arr[0]);
        $this->assertSame(2, $arr[1]);

        $this->assertSame(
            [
                1,
                2,
                [
                    'name'      => null,
                    'hire_date' => [
                        'time'                  => null,
                        'date'                  => null,
                        'daylight_savings_time' => null,
                    ],
                    'position'  => null,
                    'array_a'   => null,
                    'boolean_a' => null,
                    'float_a'   => null,
                    'int_a'     => null,
                ],
            ],
            $arr->toArray()
        );

        $arr->fromJson('[3,{"foo":4}]');

        $this->assertSame('[3,{"foo":4}]', $arr->toJson());
        $this->assertSame('[3,{"foo":4}]', json_encode($arr));
    }
}
