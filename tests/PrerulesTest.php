<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Analyze\PreRules;
use Shieldfy\Config;

class PrerulesTest extends TestCase
{
    protected $config;

    public function setup()
    {

        //setup virtual rules
        $root = vfsStream::setup();
        mkdir($root->url().'/data/', 0700, true);
        $rules = [
            'x2' => [
                '([^a-z0-9]+)', //just test rule not actual rule
                '10',
            ],
        ];
        file_put_contents($root->url().'/data/pre_rules', json_encode($rules));
        $config = new Config();
        $config['rootDir'] = $root->url();
        $this->config = $config;
    }

    public function testEmptyValues()
    {
        $rules = new PreRules($this->config);
        $rules->load()->run('GET', 'author', '');
        $this->assertEquals(0, $rules->getScore());
    }

    public function testIntegerValues()
    {
        $rules = new PreRules($this->config);
        $rules->load();
        $rules->run('GET', 'author', 5);
        $this->assertEquals(0, $rules->getScore());

        $rules->run('GET', 'author', '5');
        $this->assertEquals(0, $rules->getScore());

        $rules->run('GET', 'author', -5);
        $this->assertEquals(0, $rules->getScore());

        $rules->run('GET', 'author', 5.6);
        $this->assertEquals(0, $rules->getScore());
    }

    public function testPositiveValues()
    {
        $rules = new PreRules($this->config);
        $rules->load()->run('GET', 'author', 'some@Complex[value]');
        $this->assertEquals(10, $rules->getScore());
    }

    public function testNegativeValues()
    {
        $rules = new PreRules($this->config);
        $rules->load()->run('GET', 'author', 'basicvaluewithnodanger');
        $this->assertEquals(0, $rules->getScore());
    }
}
