# white-rat
A whitelisting library for PHP that supports deep arrays

## Installation

```bash
composer require reliv/white-rat
```

## Use

```php
use Reliv\WhiteRat\Whitelist;

$whitelist = new Whitelist([
    /* White-list rules go here; see further explanation below */
]);

$subject = [/* Data to be filtered */];

$filteredSubject = $whitelist->filter($subject);
```

## Whitelist Rules

Whitelist rule sets are designed to closely mirror the structure of the data they are applied to. A rule set takes the form of an array that is a mix of associative and indexed values, although the order of indexed values is irrelevant. When a value is indexed, it must be a string. When it is associative, it must be either an array or a boolean. Each string, whether it is a key or a value, correlates with a key in the data.

If a string appears as an indexed value, the correlating key in the data, including all fields below it, are whitelisted.

If a string appears as a key, and the value is a boolean, it indicates whether the associated data is whitelisted or not.

If a string appears as a key, and the value is an array, this indicates a more specific whitelist rule for sub-keys of the associated data. Whitelisting rules then proceed recursively.
 
It is also possible to whitelist indexed arrays. To do this, create an array within in array, where the sub-array is the only child of its parent and is an indexed child. This looks like a set of double brackets, and we refer to it as the "double-array."

Whitelist rules are validated upon construction of the whitelist. An exception of type Reliv\WhiteRat\WhitelistValidationException will be thrown if there are any problems detected in the rules given, and the path to the rule and an explanation of the error will be provided.

By default, no fields are whitelisted and all data will be filtered out, leaving you with an empty array. However, any fields present in the whitelist but absent in the data being filtered are ignored in the whitelist. This means its safe to whitelist optional data.

## Examples

```php
$whitelist = new Whitelist([
    'foo',
    'bar' => true,
    'bob' => false,
    'baz' => [
        'flip' => true,
        'flop' => [ ['flummox'] ],
        'quux',
    ]
]);

$data = [
    'foo' => 'FOO!',
    'bar' => 'BAR!',
    'bob' => 'BOB!',
    'baz' => [
        'flip' => 'FLIP!',
        'flop' => [
            ['flimsy' => 111, 'flummox' => 222],
            ['flummox' => 333, 'flopsy' => 444]
        ]
    ]
]

$result = $whitelist->filter($data);

var_dump($result);
```

Output:
```text
array(3) {
    ["foo"] => "FOO!"
    ["bar"] => "BAR!"
    ["baz"] => array(2) {
        ["flip"] => "FLIP!"
        ["flop"] => array(2) => {
            array(1) => {
                ["flummox"] => int(222)
            }
            array(1) => {
                ["flummox"] => int(333)
            }
        }
    }
}
```