# phpstan-redundant-inherited-phpdoc

A PHPStan rule that reports PHPDoc on an overriding method that repeats what it already inherits from the parent method, and descriptions that belong on the parent.

## Installation

```sh
composer require --dev ianflanagan1/phpstan-redundant-inherited-phpdoc
```

With [phpstan/extension-installer](https://github.com/phpstan/extension-installer) the rule is enabled automatically. Otherwise, include it in your `phpstan.neon`:

```neon
includes:
    - vendor/ianflanagan1/phpstan-redundant-inherited-phpdoc/rules.neon
```

Requires PHP 8.3+ and PHPStan 2.1+.

## What it reports

PHPDoc on a method that overrides or implements another, where PHPStan would inherit it anyway. Keeping it means two copies that can drift apart.

Reported:

```php
interface Cache
{
    /**
     * @param non-empty-string $key
     */
    public function get(string $key): mixed;
}

final class RedisCache implements Cache
{
    /**
     * Gets a value from Redis
     *
     * @param non-empty-string $key
     */
    #[Override]
    public function get(string $key): mixed
```

Preferred, with the documentation left on the interface:

```php
final class RedisCache implements Cache
{
    #[Override]
    public function get(string $key): mixed
```

What's reported:

- **A `@param`, `@return` or `@throws` tag** (or its `@phpstan-`/`@psalm-` form) whose type equals the inherited one. Inherited types come from the parent class if it has the method, otherwise from the first interface that does. The parent's templates are resolved, and parameters are matched by position, the same way PHPStan inherits them.
- **A description** on an overriding method. Document the parent method instead, and put notes about the implementation inside the method body.
- **`{@inheritDoc}` or `@inheritDoc`**, since PHPStan inherits without it.

Left alone:

- Tags whose type differs from the inherited one, such as a narrower `@return` or a wider `@param`.
- Tags with no inherited counterpart, such as an extra parameter.
- Other tags, such as `@deprecated`.
- Constructors, and methods whose parent method is private.

Identifiers:

- `phpDoc.redundantInherited` for repeated tags and `{@inheritDoc}`
- `phpDoc.overrideDescription` for descriptions

To ignore one occurrence, add `// @phpstan-ignore phpDoc.overrideDescription` (or the other identifier) to the line.

## Development

```sh
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/pint --test
```

The test in `tests/` is built on PHPStan's `RuleTestCase`, with fixture files in `tests/data/`. The fixtures break the rule on purpose, so PHPStan and Pint skip them.

## License

MIT
