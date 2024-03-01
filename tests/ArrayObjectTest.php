<?php

namespace Moonspot\ValueObjects\Tests;

use Moonspot\ValueObjects\ArrayObject;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class ArrayObjectTest extends \PHPUnit\Framework\TestCase {

    /**
     * @depends testOverrideToArray
     */
    public function testNonExportableObject($obj) {

        $this->expectException(\LogicException::class);

        $obj->append(new \stdClass());
        $obj->toArray();

    }

    public function testOverrideToArray() {

        $child1 = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed {
                return [1,2,3];
            }
        };

        $child2 = new \DateTime('2005-08-15T15:52:01+0000');

        $obj = new class extends ArrayObject {

            public function toArray(?array $output = null): array {
                $output = $this->getArrayCopy();
                foreach ($output as $prop => $value) {
                    if ($value instanceof \DateTime) {
                        $output[$prop] = $value->format(\DateTime::ISO8601);
                    }
                }
                return parent::toArray($output);
            }
        };

        $obj->append($child1);
        $obj->append($child2);

        $this->assertEquals(
            [
                [1,2,3],
                '2005-08-15T15:52:01+0000'
            ],
            $obj->toArray()
        );

        return $obj;
    }


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

        $arr->fromYaml("---\n- 3\n- foo: 5\n...\n");
        $this->assertSame("---\n- 3\n- foo: 5\n...\n", $arr->toYaml());


    }
}
