# Add signature and return types to php samples.

This script helps to change the old PHP function signatures (version < 7.4.0) to new PHP function signatures with type declarations(version >= 7.4.0).

More information on the type declaration is available [here](https://www.php.net/manual/en/language.types.declarations.php)


## How it works?
This script crawls through all the php files in a particular samples src folder and check if function signature has return type and argument type specified or not. If not then it adds them by taking the info from `@params` comment.

## Requirements
* All the php samples should have proper `@param` comments for all the variables in the function signature.
* The variable name in comments and function signature should exactly match case-sensitivly.

### For eg

```php
 /*
 * @param string $var Lorem.
 * @param string $tempVar Ipsum.
 */
function foo($var, $tempVar)
```

## Results

### Input

```php
 /*
 * @param string $var Lorem.
 * @param string $tempVar Ipsum.
 */
function foo($var, $tempVar)
```

### Output
```php
 /*
 * @param string $var Lorem.
 * @param string $tempVar Ipsum.
 */
function foo(string $var, string $tempVar): void
```

## Corner cases covered
* Works with multiline function signature too.
* Works for the case when variable type in comments is like `string[]` or `int[]` but in function signature, we need to write array

