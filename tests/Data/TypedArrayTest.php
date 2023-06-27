<?php

namespace Moonspot\ValueObjects\Tests\Data;

use Moonspot\ValueObjects\Data\TypedArray;

/**
 * Class TypedArrayTest
 *
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class TypedArrayTest extends \PHPUnit\Framework\TestCase {
    public function testImportArrayToObject() {
        $array_data = [
            [
                'name'      => 'test',
                'hire_date' => [
                    'time'                  => '10:00:00',
                    'date'                  => '2022-01-01',
                    'daylight_savings_time' => true,
                ],
                'position'  => 'foo',
                'array_a'   => [1],
                'boolean_a' => false,
                'float_a'   => 1.23,
                'int_a'     => 123,
            ],
        ];

        $set = new ExampleTypedPropertySet();
        $set->exchangeArray($array_data);

        $this->assertEquals(1, count($set));
    }

    public function testMergeInUniqueData() {
        $left     = new ExampleTypedPropertySet();
        $right    = new ExampleTypedPropertySet();
        $expected = new ExampleTypedPropertySet();

        $listing_a       = new ExampleTypedProperty();
        $listing_a->name = 'Testing A';

        $listing_b       = new ExampleTypedProperty();
        $listing_b->name = 'Testing B';

        $listing_c       = new ExampleTypedProperty();
        $listing_c->name = 'Testing C';

        $listing_c2       = new ExampleTypedProperty();
        $listing_c2->name = 'Testing C';

        $left[] = $listing_a;
        $left[] = $listing_b;

        $right[] = $listing_b;
        $right[] = $listing_c;
        $right[] = $listing_c2;

        $expected[] = $listing_a;
        $expected[] = $listing_b;
        $expected[] = $listing_c;

        $left->mergeInUniqueData($right);

        $this->assertCount(3, $left);
        $this->assertEquals($expected, $left);
    }

    public function testMergeInUniqueDataComplexKeys() {
        $left     = new ExampleTypedPropertySet();
        $right    = new ExampleTypedPropertySet();
        $expected = new ExampleTypedPropertySet();

        $listing_a       = new ExampleTypedProperty();
        $listing_a->name = 'Testing A';

        $listing_b       = new ExampleTypedProperty();
        $listing_b->name = 'Testing B';

        $listing_c       = new ExampleTypedProperty();
        $listing_c->name = 'Testing C';

        $listing_c2       = new ExampleTypedProperty();
        $listing_c2->name = 'Testing C';

        $listing_d       = new ExampleTypedProperty();
        $listing_d->name = 'Testing D';

        $left['a'] = $listing_a;
        $left['b'] = $listing_b;

        $right[]    = $listing_b;
        $right['c'] = $listing_c;
        $right[]    = $listing_c2;
        $right['a'] = $listing_d;

        $expected['a'] = $listing_a;
        $expected['b'] = $listing_b;
        $expected['c'] = $listing_c;
        $expected[]    = $listing_d;

        $left->mergeInUniqueData($right);

        $this->assertCount(4, $left);
        $this->assertEquals($expected, $left);
    }

    public function testMergeIn() {
        $left     = new ExampleTypedPropertySet();
        $right    = new ExampleTypedPropertySet();
        $expected = new ExampleTypedPropertySet();

        $listing_a       = new ExampleTypedProperty();
        $listing_a->name = 'Testing A';

        $listing_b       = new ExampleTypedProperty();
        $listing_b->name = 'Testing B';

        $listing_c       = new ExampleTypedProperty();
        $listing_c->name = 'Testing C';

        $listing_d       = new ExampleTypedProperty();
        $listing_d->name = 'Testing D';

        $left[8]     = $listing_a;
        $left['foo'] = $listing_b;

        $right[]      = $listing_b;
        $right['foo'] = $listing_d;
        $right[8]     = $listing_c;

        $expected[8]     = $listing_a;
        $expected[9]     = $listing_b;
        $expected[10]    = $listing_c;
        $expected['foo'] = $listing_d;

        $left->mergeIn($right);

        $this->assertCount(4, $left);
        $this->assertEquals($expected, $left);
    }

    public function testArrayLikeBehavior() {
        $array    = [1, 2, 3];
        $array[0] = null;

        $typed_array                   = new class extends TypedArray {
            public const REQUIRED_TYPE = 'integer';
        };

        $typed_array[0] = 1;
        $typed_array[1] = 2;
        $typed_array[2] = 3;

        $typed_array[0] = null;

        $this->assertEquals($array, $typed_array->toArray());
    }

    public function testToArray() {
        $object = new ExampleTypedPropertySet();
        $item   = new ExampleTypedProperty();

        $item->name      = 'test';
        $item->position  = 'foo';
        $item->array_a   = [1];
        $item->boolean_a = false;
        $item->float_a   = 1.23;
        $item->int_a     = 123;

        $item->hire_date->time                  = '10:00:00';
        $item->hire_date->date                  = '2022-01-01';
        $item->hire_date->daylight_savings_time = true;

        $object->append($item);

        $output   = $object->toArray();
        $expected = [
            [
                'name'      => 'test',
                'hire_date' => [
                        'time'                  => '10:00:00',
                        'date'                  => '2022-01-01',
                        'daylight_savings_time' => true,
                    ],
                'position'  => 'foo',
                'array_a'   => [1],
                'boolean_a' => false,
                'float_a'   => 1.23,
                'int_a'     => 123,
            ],
        ];

        $this->assertEquals($expected, $output);
    }

    public function testNonExportObjectException() {
        $object                        = new class extends TypedArray {
            public const REQUIRED_TYPE = \stdClass::class;
        };

        $object[] = (object)['foo' => 1];

        $this->expectException(\LogicException::class);

        $object->toArray();
    }

    /**
     * @dataProvider filterTypeData
     */
    public function testFilterType($value, $type, $expect, $exception = false) {
        $class = new class extends TypedArray {
            public function testFilterType($value, string $type) {
                return $this->filterType($value, $type);
            }
        };

        if ($exception) {
            $this->expectException(\UnexpectedValueException::class);
        }

        $new_value = $class->testFilterType($value, $type);

        $this->assertSame($expect, $new_value);
    }

    public function filterTypeData() {
        return [
            'array' => [
                [1, 2, 3],
                'array',
                [1, 2, 3],
            ],
            'ArrayObject' => [
                new \ArrayObject([1, 2, 3]),
                'array',
                [1, 2, 3],
            ],
            'boolean' => [
                true,
                'boolean',
                true,
            ],
            'boolean int' => [
                1,
                'boolean',
                true,
            ],
            'boolean string' => [
                'true',
                'boolean',
                true,
            ],
            'boolean string int' => [
                '1',
                'boolean',
                true,
            ],
            'double' => [
                1.0,
                'double',
                1.0,
            ],
            'double from string' => [
                '1.0',
                'double',
                1.0,
            ],
            'double from int' => [
                1,
                'double',
                1.0,
            ],
            'double from int string' => [
                '1',
                'double',
                1.0,
            ],

            'int' => [
                1,
                'integer',
                1,
            ],
            'int from string' => [
                '1',
                'integer',
                1,
            ],
            'int from double' => [
                1.0,
                'integer',
                1,
            ],
            'int from double string' => [
                '1.0',
                'integer',
                1,
            ],

            'string' => [
                '1.0',
                'string',
                '1.0',
            ],

            'string from double' => [
                1.2,
                'string',
                '1.2',
            ],

            'array to string exception' => [
                [1, 2],
                'string',
                null,
                true,
            ],

            'string to int exception' => [
                'foo',
                'integer',
                null,
                true,
            ],

            'string to double exception' => [
                'foo',
                'double',
                null,
                true,
            ],

        ];
    }
}
