<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Analyze\PreAnalyzer;
use Shieldfy\Shieldfy;

class PreAnalyzerTest extends TestCase
{
    protected $root;

    public function setup()
    {
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/data/', 0700, true);
        $rules = [
            2 => [
                '([^a-z0-9]+)', //just test rule not actual rule
            ],
        ];
        file_put_contents($this->root->url().'/data/pre_rules', json_encode($rules));
        Shieldfy::setRootDir($this->root->url());
    }

    public function testEmptyValues()
    {
        $res = PreAnalyzer::run('x', '');
        $this->assertFalse($res);
    }

    public function testIntegerValues()
    {
        $res = PreAnalyzer::run('x', 5);
        $this->assertFalse($res);

        $res = PreAnalyzer::run('x', -5);
        $this->assertFalse($res);

        $res = PreAnalyzer::run('x', 5.6);
        $this->assertFalse($res);
    }

    public function testPositiveValues()
    {
        $res = PreAnalyzer::run('x', 'some@Complex[value]');
        $this->assertTrue($res);
    }

    public function testNegativeValues()
    {
        $res = PreAnalyzer::run('x', 'basicvaluewithnodanger');
        $this->assertFalse($res);
    }
}
