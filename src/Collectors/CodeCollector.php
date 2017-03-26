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
        return $this->code;
    }

    public function collectFromText($text = '',$value)
    {

        $content = explode("\n",$text);

        $code = [];
        for ($i=0; $i < count($content); $i++) {
            if(stripos($content[$i],$value) !== false){
                $line = $i;
                $start = $i - 4;
                $code = array_slice($content, $start < 0 ? 0 : $start, 7,true);
                break;
            }
        }

        $this->code = [
            'file' => 'none',
            'line' => $line,
            'content' => $code
        ];
        return $this->code;
    }

    public function getInfo()
    {
        return $this->code;
    }
}
