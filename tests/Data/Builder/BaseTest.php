<?php

namespace Moonspot\ValueObjects\Tests\Data\Builder;

use Moonspot\ValueObjects\Data\Builder\Base;
use Moonspot\ValueObjects\Tests\Data\ExampleTypedProperty;
use Moonspot\ValueObjects\Tests\Data\ExampleTypedSubProperty;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class BaseTest extends \PHPUnit\Framework\TestCase {
    public function testBuild() {
        $class = new class extends Base {
            public function create(array $data) {
                $obj = new ExampleTypedProperty();

                if ($this->getValue('name', $data)) {
                    $this->setValue($obj, 'name', $data, ['dummy', 'name']);
                    $this->setValue($obj, 'position', $data, ['position']);
                    $this->setValue($obj, 'array_a', $data, ['array_a', 'array']);
                    $this->setValue($obj, 'boolean_a', $data, ['boolean_a']);
                    $this->setValue($obj, 'float_a', $data, ['float_a']);
                    $this->setValue($obj, 'int_a', $data, ['null_int', 'int_a'], false);
                }

                return $obj;
            }
        };

        $input = [
            'name'      => 'Foo',
            'position'  => 'Bar',
            'array'     => [1, 2, 3],
            'boolean_a' => false,
            'float_a'   => 1.234,
            'null_int'  => 0,
            'int_a'     => 10,
        ];

        $expect = [
            'name'      => 'Foo',
            'position'  => 'Bar',
            'array_a'   => [1, 2, 3],
            'boolean_a' => false,
            'float_a'   => 1.234,
            'int_a'     => 10,
            'hire_date' => new ExampleTypedSubProperty(),
        ];

        $obj = $class::build($input);

        $this->assertBuild($expect, $obj);
    }

    protected function assertBuild($expect, $obj) {
        foreach ($expect as $key => $expected_value) {
            $value = $obj->$key;
            if (is_object($expected_value)) {
                $this->assertTrue(
                    is_object($value),
                    'Expected ' . get_class($expected_value) . ', object is an instance of ' . gettype($value)
                );
                $this->assertInstanceOf(
                    $expected_value::class,
                    $value,
                    'Expected ' . get_class($expected_value) . ', object is an instance of ' . get_class($value)
                );
            } else {
                $this->assertEquals(
                    $expected_value,
                    $value
                );
            }
        }
    }
}
