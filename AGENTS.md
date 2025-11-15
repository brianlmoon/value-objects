# AGENTS.md - AI Agent Guide for moonspot/value-objects

## Project Overview

**moonspot/value-objects** is a PHP 8.2+ library providing base classes for creating value objects with seamless JSON/YAML serialization. Built for API development, it simplifies converting between structured data (arrays, JSON, YAML) and typed PHP objects.

**Primary Use Case**: Creating domain models and DTOs for JSON APIs that need bidirectional conversion between PHP objects and serialization formats.

## Quick Start Context

```php
// Core pattern: extend ValueObject, define public properties
class Car extends ValueObject {
    public int $id = 0;
    public string $model = '';
    public string $make = '';
}

$car = new Car();
$car->fromArray(['id' => 1, 'model' => 'Kia', 'make' => 'Sportage']);
echo $car->toJson(); // {"id":1,"model":"Kia","make":"Sportage"}
```

## Architecture

### Core Components

1. **`ValueObject`** (abstract) - Base class for domain objects
   - Implements `Export` interface and `\JsonSerializable`
   - Provides bidirectional conversion: array ↔ JSON ↔ YAML
   - Handles nested objects recursively
   - Located: `src/ValueObject.php`

2. **`TypedArray`** (abstract) - Type-safe collection wrapper
   - Extends `ArrayObject` with type enforcement
   - Requires child classes to declare `REQUIRED_TYPE` constant
   - Supports scalar types + class names
   - Auto-converts compatible values (e.g., strings to ints)
   - Located: `src/TypedArray.php`

3. **`ArrayObject`** - Unstructured data container
   - Native `\ArrayObject` + `Export` interface
   - Use when property structure is dynamic/unknown
   - Located: `src/ArrayObject.php`

4. **`Export`** interface - Contract for serialization
   - Methods: `toArray()`, `fromArray()`, `toJson()`, `fromJson()`, `toYaml()`, `fromYaml()`
   - Located: `src/Interfaces/Export.php`

### Key Design Patterns

**Recursive Export**: All `toArray()` implementations walk object graphs, converting nested `Export` or `JsonSerializable` objects.

**Constructor Initialization**: Complex properties (ValueObjects, TypedArrays) MUST be initialized in `__construct()`:
```php
class Fleet extends ValueObject {
    public CarSet $cars; // TypedArray property
    
    public function __construct() {
        $this->cars = new CarSet(); // Required!
    }
}
```

**Type Coercion**: `TypedArray` attempts safe conversions (string "42" → int 42) but throws `\UnexpectedValueException` on failure.

## Coding Standards

### Naming Conventions
- **Variables/Properties**: `snake_case` (e.g., `$hire_date`, `$user_id`)
- **Methods**: `camelCase` (e.g., `fromArray()`, `toJson()`)
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `REQUIRED_TYPE`, `UNIQUE_ID_FIELD`)

### Visibility
- **Default**: `protected` for methods/properties (NOT `private` unless explicitly required)
- **Public**: Expose only data properties and interface methods
- **Protected**: Internal helpers, type filters, recursive workers

### PHPDoc Requirements
- Every public class/method MUST have a complete PHPDoc block
- Include `@param`, `@return`, `@throws` tags
- Document nullable parameters and default values
- Example:
```php
/**
 * Filters the given value for the given type
 *
 * @param      mixed  $value  The value to filter
 * @param      array  $types  Allowed type names
 *
 * @throws     \UnexpectedValueException  When value cannot match any allowed type
 *
 * @return     mixed  The type-coerced value
 */
protected function filterType(mixed $value, array $types): mixed {
```

### Code Style (PHP-CS-Fixer)
- PSR-2 baseline
- Short array syntax: `[]` not `array()`
- Single quotes for strings
- Align binary operators with single space
- Opening braces on same line
- Run `composer fix` before committing

## Testing Strategy

### PHPUnit Setup
- **Version**: 11.x
- **Config**: `phpunit.xml.dist`
- **Bootstrap**: `tests/bootstrap.php`
- **Run**: `composer test` (runs lint + unit)
- **Coverage**: Text output to stdout

### Test Structure
- Test classes: `tests/*Test.php`
- Example fixtures: `tests/ExampleTyped*.php`
- Pattern: Use anonymous classes for one-off mocks:
```php
$obj = new class extends ValueObject {
    public int $id = 0;
    public function toArray(?array $data = null): array {
        // custom logic
        return parent::toArray($data);
    }
};
```

### What to Test
- ✅ Round-trip conversions (array → object → array)
- ✅ Type coercion in TypedArray (valid + invalid inputs)
- ✅ Nested object serialization
- ✅ Null handling for nullable properties
- ✅ Exception cases (`\UnexpectedValueException`, `\LogicException`)

### Edge Cases to Cover
- Null values on non-nullable typed properties (should be ignored)
- Objects not implementing Export/JsonSerializable (should throw)
- Mixed scalar/object arrays
- DateTime and custom object serialization

## Common Workflows

### Adding a New ValueObject
1. Create class extending `ValueObject`
2. Define public typed properties with defaults
3. Initialize complex properties in `__construct()`
4. Add PHPDoc block documenting purpose
5. Write test covering `fromArray()` → `toArray()` round-trip

### Adding a New TypedArray
1. Create class extending `TypedArray`
2. Declare `public const REQUIRED_TYPE = [ClassName::class];`
3. Can specify multiple types: `['integer', 'string']`
4. Test type enforcement with valid/invalid values

### Customizing Serialization
Override `toArray()` to transform properties before export:
```php
public function toArray(?array $data = null): array {
    $data = (array)$this;
    $data['created_at'] = $this->created_at->format(\DateTime::ISO8601);
    return parent::toArray($data);
}
```

## Build & Quality Tools

### Composer Scripts
```bash
composer lint      # Parallel-lint PHP files
composer unit      # Run PHPUnit
composer test      # Lint + unit tests
composer fix       # Auto-fix code style
```

### CI/CD (GitHub Actions)
- **Trigger**: Every push
- **PHP Versions**: 8.2, 8.3, 8.4
- **Steps**: Composer install → PHPUnit → Phan static analysis
- **Extensions Required**: `yaml`, `pcov`

### Static Analysis
- **Phan**: Runs in CI (see `.phan/` directory)
- Suppression example: `// @phan-suppress-current-line PhanUndeclaredClass`

## Troubleshooting Guide

### Common Errors

**"Property $x does not implement Export interface"**
- **Cause**: Nested object lacks `Export` or `JsonSerializable`
- **Fix**: Extend `ValueObject` or implement interface

**"TypeError when setting property"**
- **Cause**: Type mismatch, often with null values
- **Fix**: Make property nullable (`?string`) or provide default

**"UnexpectedValueException in TypedArray"**
- **Cause**: Value cannot be coerced to REQUIRED_TYPE
- **Fix**: Check input data matches expected type

### Debugging Tips
- Use `print_r($obj->toArray())` to inspect serialization output
- Check `__construct()` initializes all object properties
- Verify property types match incoming data structure
- Test with `composer test` after any changes

## Dependencies

### Required
- **PHP**: ^8.2 (uses typed properties, union types, mixed type)
- **ext-yaml**: YAML parsing/emission

### Development
- `phpunit/phpunit`: ^11
- `php-parallel-lint/php-parallel-lint`: ^1.4
- `friendsofphp/php-cs-fixer`: ^3.88

## File Structure

```
src/
├── Interfaces/
│   └── Export.php          # Core serialization interface
├── ArrayObject.php         # Unstructured array wrapper
├── TypedArray.php          # Type-enforced collections
└── ValueObject.php         # Base domain object class

tests/
├── bootstrap.php           # PHPUnit bootstrap
├── ValueObjectTest.php     # ValueObject tests
├── TypedArrayTest.php      # TypedArray tests
├── ArrayObjectTest.php     # ArrayObject tests
└── ExampleTyped*.php       # Test fixtures
```

## Key Implementation Details

### Type Coercion Logic (TypedArray::filterType)
- **array**: Converts `\ArrayObject` to array
- **boolean**: Uses `filter_var()` with `FILTER_VALIDATE_BOOLEAN`
- **float/double**: Uses `filter_var()` with `FILTER_VALIDATE_FLOAT`
- **integer**: Casts if safe, validates with `FILTER_VALIDATE_INT`
- **string**: Casts scalars only
- **Class names**: Auto-instantiates from array if class implements `Export`

### Null Handling
`fromArray()` catches `\TypeError` and silently ignores null assignments to non-nullable properties. This prevents crashes when API data omits optional fields.

### UNIQUE_ID_FIELD Constant
`ValueObject::UNIQUE_ID_FIELD` is a placeholder for domain-specific primary keys. Set in child classes:
```php
class User extends ValueObject {
    public const UNIQUE_ID_FIELD = 'user_id';
    public int $user_id = 0;
}
```

## When Modifying This Codebase

### Always:
- Run `composer test` before committing
- Update PHPDoc blocks when changing signatures
- Follow snake_case/camelCase conventions
- Use `protected` visibility by default
- Initialize object properties in `__construct()`

### Never:
- Use `private` without specific reason
- Break round-trip conversion (array → object → array should match)
- Add dependencies without updating composer.json
- Skip type hints on parameters/returns
- Leave TODOs or commented-out code

### Review Checklist:
- [ ] PHPDoc blocks complete?
- [ ] Tests pass (`composer test`)?
- [ ] Code style fixed (`composer fix`)?
- [ ] Backward compatibility maintained?
- [ ] Edge cases covered (null, invalid types)?

## Performance Considerations

- **Reflection**: Not used; property access is direct (faster)
- **Recursion**: `toArray()` recursively walks object graphs—deep nesting impacts performance
- **Type Coercion**: `filterType()` called per array element in TypedArray—validate types early if possible

## Security Notes

- **No input sanitization**: This library does NOT sanitize data—validate before calling `fromArray()`
- **YAML parsing**: Uses native `yaml_parse()` which can execute code—only parse trusted YAML
- **Type safety**: TypedArray enforces types but allows coercion—validate business logic separately

## Documentation Style

When writing docs for this project:
- **Voice**: Conversational but direct ("We use...", "Heads-up:")
- **Audience**: PHP engineers (assume SPL, OOP knowledge)
- **Format**: Markdown with code snippets showing usage
- **Completeness**: Include edge cases, defaults, exceptions
- **Examples**: Real-world snippets that compile

---

**Last Updated**: 2025-11-15  
**Maintainer**: Brian Moon <brian@moonspot.net>  
**License**: BSD-3-Clause
