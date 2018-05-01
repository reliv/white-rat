<?php

namespace Reliv\WhiteRat\Tests;

require_once __DIR__ . '/../src/Whitelist.php';
require_once __DIR__ . '/../src/WhitelistValidationException.php';

use PHPUnit\Framework\TestCase;
use Reliv\WhiteRat\Whitelist;
use Reliv\WhiteRat\WhitelistValidationException;

class WhitelistTest extends TestCase
{
    public function testValidateFirstIndexedValueType()
    {
        $this->expectExceptionObject(new WhitelistValidationException(
            '[(root)] => [0]: First indexed value must be string or array'
        ));

        new Whitelist([ 2 ]);
    }

    public function testValidateOtherIndexedValueTypes()
    {
        $this->expectExceptionObject(new WhitelistValidationException(
            '[(root)] => [1]: Indexed values after [0] must be strings'
        ));

        new Whitelist([ 'a', 2 ]);
    }

    public function testValidateKeyedValueTypes()
    {
        $this->expectExceptionObject(new WhitelistValidationException(
            '[(root)] => [a]: Keyed values must be string, bool, or array'
        ));

        new Whitelist([ 'a' => 2 ]);
    }

    public function testValidateDoubleArray()
    {
        $this->expectExceptionObject(new WhitelistValidationException(
            '[(root)] => [0]: Double-array should have exactly one child'
        ));

        new Whitelist([['a'], 'b' ]);
    }

    public function testAllFilters()
    {
        $whitelist = new Whitelist([
            'a',
            'b' => true,
            'c' => false,
            'd' => [
                'e',
                'f' => [ 'h' ],
                'i'
            ],
            'j' => [[ 'l' ]],
            'm'
        ]);

        $subject = [
            'a' => 'A',
            'b' => ['b', 111],
            'c' => [222, 'C'],
            'd' => [
                'f' => [
                    'g' => 'G',
                    'h' => 'H',
                ],
                'i' => 333
            ],
            'j' => [
                ['k' => 'K1', 'l' => 'L1'],
                ['k' => 'K1', 'l' => 'L2']
            ],
            'm' => 'M'
        ];

        $expectedResult = [
            'a' => 'A',
            'b' => ['b', 111],
            'd' => [
                'f' => [
                    'h' => 'H',
                ],
                'i' => 333
            ],
            'j' => [
                ['l' => 'L1'],
                ['l' => 'L2']
            ],
            'm' => 'M'
        ];

        $actualResult = $whitelist->filter($subject);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetsRules()
    {
        $whitelist = new Whitelist(['the' => 'rule']);
        $this->assertEquals(['the' => 'rule'], $whitelist->getRules());
    }
}
