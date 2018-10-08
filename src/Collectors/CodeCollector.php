<?php
namespace Shieldfy\Collectors;

use Shieldfy\Config;

class CodeCollector implements Collectable
{
    /**
     * @var code Code block
     * @var stack Stack trace
     */
    private $code = [];
    private $stack = [];

    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Push stack trace
     * @param Array|array $stack
     * @return void
     */
    public function pushStack(array $stack = array())
    {
        $this->stack = $stack;
        //exit;
        return $this;
    }

    public function collectFromStack()
    {
        $stack = array_reverse($this->stack);

        foreach ($this->stack as $trace):
            if (!isset($trace['file'])) {
                continue;
            }
        //dirty fix START
        if (strstr($trace['file'], 'shieldfy-php-client')) {
            continue;
        }
        //dirty fix ENDS
        if (strpos($trace['file'], $this->config['paths']['vendors']) === false) {
            //this is probably our guy ( the last file called outside vendor file)
            return [
                    'stack' => $stack,
                    'code'  => $this->collectFromFile($trace['file'], $trace['line'])
                ];
        }

        endforeach;


        return [
            'stack' => $stack,
            'code'  => []
        ];
    }

    public function collectFromFile($filePath = '', $line = '')
    {
        if ($filePath && file_exists($filePath)) {
            $content = file($filePath);
            array_unshift($content, 'x');
            unset($content[0]);
            $start = $line - 4;
            $content = array_slice($content, $start < 0 ? 0 : $start, 7, true);
        } else {
            $content = array("Cannot open the file ($filePath) in which the vulnerability exists ");
        }

        $this->code = [
            'file' => $filePath,
            'line' => $line,
            'content' => $content
        ];
        return $this->code;
    }

    public function collectFromText($text = '', $value)
    {
        $content = explode("\n", $text);
        $line = 0;
        $code = [];
        for ($i = 0; $i < count($content); $i++) {
            if (stripos($content[$i], $value) !== false) {
                $line = $i;
                $start = $i - 4;
                $code = array_slice($content, $start < 0 ? 0 : $start, 8, true);
                break;
            }
        }

        $this->code = [
            'file' => 'none',
            'line' => $line + 1, //to fix array 0 index
            'content' => $code
        ];
        return [
            'stack' => [],
            'code'  => $this->code
        ];
        ;
    }

    public function getInfo()
    {
        return [
            'code' => $this->code,
            'stack' => $this->stack
        ];
    }
}
