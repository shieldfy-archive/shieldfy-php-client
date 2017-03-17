<?php
namespace Shieldfy\Collectors;

class CodeCollector implements Collectable
{
    protected $code = [];

    public function __construct()
    {

    }

    public function collectFromFile($filePath = '',$line = '')
    {
        if ($filePath && file_exists($filePath)) {
            $lines = file($filePath);
            $start = $line - 4;
            $lines = array_slice($lines, $start < 0 ? 0 : $start, 7);
        } else {
            $lines = array("Cannot open the file ($filePath) in which the vulnerability exists ");
        }
        $this->code = $lines;
    }

    public function collectFromText($text = '',$line)
    {
        
    }

    public function getInfo()
    {

    }
}
