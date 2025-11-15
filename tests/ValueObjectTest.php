<?php

namespace Moonspot\ValueObjects\Tests;

use Moonspot\ValueObjects\ValueObject;

/**
 * @author      Brian Moon <brian@moonspot.net>
 * @copyright   2023-present Brian Moon
 * @package     Moonspot\ValueObjects
 */
class ValueObjectTest extends \PHPUnit\Framework\TestCase {

    public function testOverrideToArray() {

        $obj = new class extends ValueObject {
            public int $id = 0;
            public \DateTime $dt;
            public $child;

            public function __construct() {
                $this->dt = new \DateTime();
                $this->child = new class implements \JsonSerializable {
                    public function jsonSerialize(): mixed {
                        return [1,2,3];
                    }
                };
            }

            public function toArray(?array $data = null): array {
                $data       = (array)$this;
                $data['dt'] = $data['dt']->format(\DateTime::ISO8601);

                return parent::toArray($data);
            }
        };

        $obj->dt->setTimestamp(strtotime('2005-08-15T15:52:01+0000'));

        $this->assertEquals(
            [
                'id'    => 0,
                'dt'    => '2005-08-15T15:52:01+0000',
                'child' => [1,2,3],
            ],
            $obj->toArray()
        );
    }

    public function mockObject() {
        return new class extends ValueObject {
            public int    $id   = 0;
            public string $name = '';
            public string $description;
            public object $foo;
            public array  $bar;

            public function __construct() {
                $this->foo     = new class extends ValueObject {
                    public $id = 0;
                };
            }
        };
    }

    public function mockBadObject() {
        return new class extends ValueObject {
            public int    $id   = 0;
            public string $name = '';
            public string $description;
            public object $foo;
            public object $bar;

            public function __construct() {
                $this->foo     = new class extends ValueObject {
                    public $id = 0;
                };
                $this->bar     = new class {
                    public $id = 0;
                };
            }
        };
    }

    public function testBehavior() {
        $obj = $this->mockObject();

        $obj->id = 1;

        $this->assertSame(1, $obj->id);
    }

    public function testFromJson() {
        $obj = $this->mockObject();
        $obj->fromJson(
            json_encode([
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
            ])
        );

        $this->assertSame(
            1,
            $obj->id
        );

        $this->assertSame(
            'Test',
            $obj->name
        );

        $this->assertSame(
            2,
            $obj->foo->id
        );
    }

    public function testFromArray() {
        $obj = $this->mockObject();
        $obj->fromArray(
            [
                'id'          => 1,
                'name'        => 'Test',
                'description' => 'Test',
                'foo'         => [
                    'id' => 2,
                ],
                'bar'  => [
                    'id' => 2,
                ],
            ]
        );

        $this->assertSame(
            1,
            $obj->id
        );
        $this->assertSame(
            'Test',
            $obj->name
        );

        $this->assertSame(
            2,
            $obj->foo->id
        );
    }

    public function testFromArrayNulls() {
        $obj = $this->mockObject();
        $obj->fromArray(
            [
                'id'          => 1,
                'name'        => null,
                'description' => 'Test',
                'foo'         => [
                    'id' => 2,
                ],
                'bar'  => [
                    'id' => 2,
                ],
            ]
        );

        $this->assertSame(
            [
                'id'          => 1,
                'name'        => '',
                'description' => 'Test',
                'foo'         => [
                    'id' => 2,
                ],
                'bar'  => [
                    'id' => 2,
                ],
            ],
            $obj->toArray()
        );
    }

    public function testFromArrayException() {
        $this->expectException('\\LogicException');

        $obj = $this->mockBadObject();
        $obj->fromArray(
            [
                'id'          => 1,
                'name'        => 'Test',
                'description' => 'Test',
                'foo'         => [
                    'id' => 2,
                ],
                'bar'  => [
                    'id' => 2,
                ],
            ]
        );
    }

    public function testToJson() {
        $obj = $this->mockObject();

        $obj->id      = 1;
        $obj->name    = 'Test';
        $obj->foo->id = 2;

        $this->assertEquals(
            json_encode([
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
            ]),
            $obj->toJson()
        );

        $this->assertEquals(
            json_encode([
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
            ]),
            json_encode($obj)
        );
    }

    public function testToYaml() {
        $obj = $this->mockObject();

        $obj->id      = 1;
        $obj->name    = 'Test';
        $obj->foo->id = 2;

        $this->assertEquals(
            yaml_emit([
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
            ]),
            $obj->toYaml()
        );
    }

    public function testFromYaml() {
        $obj = $this->mockObject();

        $obj->fromYaml(
            yaml_emit([
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
            ])
        );

        $this->assertEquals(
            [
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
            ],
            $obj->toArray()
        );
    }

    public function testToArray() {

        $sub_obj = new class extends ValueObject {
            public $id = 0;
        };

        $obj = $this->mockObject();

        $obj->id      = 1;
        $obj->name    = 'Test';
        $obj->foo->id = 2;
        $obj->bar[] = $sub_obj;

        $this->assertEquals(
            [
                'id'   => 1,
                'name' => 'Test',
                'foo'  => [
                    'id' => 2,
                ],
                'bar' => [
                    [
                        'id'   => 0,
                    ]
                ]
            ],
            $obj->toArray()
        );
    }

    public function testToArrayException() {
        $obj = $this->mockBadObject();

        $obj->id      = 1;
        $obj->name    = 'Test';
        $obj->foo->id = 2;
        $obj->bar->id = 2;

        $this->expectException('\\LogicException');

        $obj->toArray();
    }
}
