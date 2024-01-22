# Value Objects

This library provides a base for creating value objects that can be easily
created from and converted into YAML and JSON for use in creating API endpoints.

## \Moonspot\ValueObjects\ValueObject

This class provides methods for converting arrays, JSON, and YAML into objects
and exporting them back to those forms.

```php
<?php

namespace Example;

use Moonspot\ValueObjects\ValueObject;

class Car extends ValueObject {

    public int $id = 0;

    public string $model = '';

    public string $make = '';


}

$car = new Car();
$car->fromArray(
    [
        'id'    => 1,
        'model' => 'Kia',
        'make'  => 'Sportage'
    ]
);

echo $car->toJSON();
```
```json
{
    "id":    1,
    "model": "Kia",
    "make":  "Sportage"
}
```

## \Moonspot\ValueObjects\TypedArray

This class is a base class for creating an ArrayObject that requires a specific
type or types added to the ArrayObject. And, the class will provide the Export
interface. Also, any objects within the internal array will be deeply exported.

```php
<?php

namespace Example;

use Moonspot\ValueObjects\TypedArray;
use Moonspot\ValueObjects\ValueObject;

class CarSet extends TypedArray {
    public const REQUIRED_TYPE = [Car::class];
}

class Fleet extends ValueObject {
    public int $id = 0;

    public string $name = '';

    public CarSet $cars;

    public function __construct() {
        // Any properties which are ValueObjects or TypedArrays should be
        // initialized in the ValueObject's constructor
        $this->cars = new CarSet();
    }
}

$car = new Car();
$car->fromArray(
    [
        'id'    => 1,
        'model' => 'Kia',
        'make'  => 'Sportage'
    ]
);

$fleet = new Fleet();
$fleet->id = 1;
$fleet->name = "New Fleet";
$fleet->cars[] = $car;

echo $fleet->toJSON();
```
```json
{
    "id": 1,
    "name": "New Fleet",
    "cars": [
        {
            "id":    1,
            "model": "Kia",
            "make":  "Sportage"
        }
    ]
}
```

## \Moonspot\ValueObjects\ArrayObject

This is the base class for TypedArray and extends the native ArrayObject
classes adding the methods for the Export interface. This is useful by itself
when there is unstructured array data that needs to be a part of a value
object.

```php
<?php

namespace Example;

use Moonspot\ValueObjects\ValueObject;
use Moonspot\ValueObjects\ArrayObject;

class Car extends ValueObject {

    public int $id = 0;

    public string $model = '';

    public string $make = '';

    public ArrayObject $attributes;

    public function __construct() {
        // Any properties which are ValueObjects or TypedArrays should be
        // initialized in the ValueObject's constructor
        $this->attributes = new ArrayObject();
    }
}

$car        = new Car();
$car->id    = 1;
$car->model = 'Kia';
$car->make  = 'Sportage';

$car->attributes['color'] = 'Blue';
$car->attributes['passengers'] = 5;
echo $car->toJSON();
```
```json
{
    "id":    1,
    "model": "Kia",
    "make":  "Sportage",
    "attributes": {
        "color": "Blue",
        "passengers": 5
    }
}
```
