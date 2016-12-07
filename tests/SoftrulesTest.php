<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Config;
use Shieldfy\Analyze\SoftRules;

class SoftRulesTest extends TestCase
{	
	
	protected $config;

	public function setup()
	{

		//setup virtual rules
        $root = vfsStream::setup();
        mkdir($root->url().'/data/', 0700, true);
        $softrules = [
            'x0' => [
                '([\.]{2}\/.*)', //just test rule not actual rule,
                '11',
            ],
            'x1' => [
                '(.*(etc|passwd|shadow))', //just test rule not actual rule,
                '15',
            ]
        ];
        file_put_contents($root->url().'/data/soft_rules', json_encode($softrules));
        $config = new Config;
        $config['rootDir'] = $root->url();
        $this->config = $config;
	}

	public function testRulesLoadAndExecutionClean()
	{
		$rules = new SoftRules($this->config);
		$rules->load()->run('GET','author','johndoe');
		$this->assertEquals(0,$rules->getScore());
		$this->assertEquals([],$rules->getRulesIds());
	}

	public function testRulesLoadAndExecutionInfectedGET()
	{
		$rules = new SoftRules($this->config);
		$rules->load()->run('GET','author','johndoe/../../etc/passwd');
		$this->assertEquals(36,$rules->getScore());
		$this->assertEquals(['x0','x1'],$rules->getRulesIds());
	}

	public function testRulesLoadAndExecutionInfectedPOST()
	{
		$rules = new SoftRules($this->config);
		$rules->load()->run('POST','author','johndoe/../../etc/passwd');
		$this->assertEquals(31,$rules->getScore());
		$this->assertEquals(['x0','x1'],$rules->getRulesIds());
	}
	
}