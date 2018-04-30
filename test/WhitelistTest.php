<?php

namespace Reliv\WhiteRat\Tests;

require_once __DIR__ . '/../src/Whitelist.php';
require_once __DIR__ . '/../src/StructureException.php';

use PHPUnit\Framework\TestCase;
use Reliv\WhiteRat\Whitelist;

class WhitelistTest extends TestCase
{
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
            'b' => ['b', 'B'],
            'c' => ['c', 'C'],
            'd' => [
                'f' => [
                    'g' => 'G',
                    'h' => 'H',
                ],
                'i' => 'I'
            ],
            'j' => [
                ['k' => 'K1', 'l' => 'L1'],
                ['k' => 'K1', 'l' => 'L2']
            ],
            'm' => 'M'
        ];

        $expectedResult = [
            'a' => 'A',
            'b' => ['b', 'B'],
            'd' => [
                'f' => [
                    'h' => 'H',
                ],
                'i' => 'I'
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
}
