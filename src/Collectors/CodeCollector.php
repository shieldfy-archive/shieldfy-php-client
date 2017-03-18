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
            $content = file($filePath);
            $start = $line - 4;
            $content = array_slice($content, $start < 0 ? 0 : $start, 7,true);
        } else {
            $content = array("Cannot open the file ($filePath) in which the vulnerability exists ");
        }
        $this->code = [
            'file' => $filePath,
            'line' => $line,
            'content'=> $content
        ];
    }

    public function collectFromText($text = '',$line)
    {
        $content = explode("\n",$text);
        $start = $line - 4;
        $content = array_slice($content, $start < 0 ? 0 : $start, 7,true);
        $this->code = [
            'file' => 'none',
            'line' => $line,
            'content' => $content
        ];
    }

    public function getInfo()
    {
        return $this->code;
    }
}
