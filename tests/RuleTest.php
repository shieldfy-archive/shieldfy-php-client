<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Analyze\Rule;

class RuleTest extends TestCase
{
    public function testInfo()
    {
        $rule = new Rule('a1', [
            '([^a-z]+)',
            '1',
        ]);

        $this->assertEquals('a1', $rule->getId());
        $this->assertEquals('1', $rule->getScore());
        $this->assertEquals('([^a-z]+)', $rule->getRule());
    }

    public function testExecute()
    {
        $value = 'hello@world';
        $rule = new Rule('a1', [
            '([^a-z]+)',
            '1',
        ]);
        $result = $rule->execute($value);
        //print_r($result);
        $this->assertTrue($result);

        $this->assertEquals([
            '@', '@',
        ], $rule->getMatches());
        $this->assertEquals(1, $rule->getLength());
    }
}
