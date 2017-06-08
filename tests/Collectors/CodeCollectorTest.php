<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Collectors\CodeCollector;
use org\bovigo\vfs\vfsStream;

class CodeCollectorTest extends TestCase
{
    protected $root;

    public function setUp()
    {
        //set virtual filesystem
        $this->root = vfsStream::setup();
    }

    public function testCollectFromFile()
    {
        $sample = "1\n2\n3\n4\n5\n6\n7\n8\n9\n10\n11\n12\n";
        file_put_contents($this->root->url().'/testfile', $sample);
        $collector = new CodeCollector;
        $result = $collector->collectFromFile($this->root->url().'/testfile', 6);

        $this->assertEquals(6, $result['line']);
        $this->assertEquals(7, count($result['content']));
    }
    public function testCollectFromText()
    {
        $sample = "1\n2\n3\n4\n5\n6\n7\n8\n9\n10\n11\n12\n";
        $collector = new CodeCollector;
        $result = $collector->collectFromText($sample, '6');

        $this->assertEquals(6, $result['line']);
        $this->assertEquals(7, count($result['content']));
    }
}
