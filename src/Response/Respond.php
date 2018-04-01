<?php
namespace Shieldfy\Response;

class Respond
{
    private $blockStatus = 403;
    private $blockMessage = 'Dangerous Request Blocked :: Shieldfy Web Shield';

    protected $protocol;
    protected $blockPage = null;

    public function __construct($protocol = 'HTTP/1.1')
    {
        $this->protocol = $protocol;
    }

    public function setBlockPage($blockPage)
    {
        $this->blockPage = $blockPage;
    }

    public function block($incidentId)
    {
        header($this->protocol.' '.$this->blockStatus.' '.$this->blockMessage);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Shieldfy-Status: blocked');
        header('X-Shieldfy-Block-Id: '.$incidentId);
        echo $this->prepareBlockResponse($incidentId);
        $this->halt();
    }

    public function returnBlock($incidentId)
    {
        header($this->protocol.' '.$this->blockStatus.' '.$this->blockMessage);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Shieldfy-Status: blocked');
        header('X-Shieldfy-Block-Id: '.$incidentId);
        $response = $this->prepareBlockResponse($incidentId);
        return $response;
    }

    private function prepareBlockResponse($incidentId)
    {
        if ($this->blockPage && file_exists($this->blockPage) && is_readable($this->blockPage)) {
            $blockHTML = file_get_contents($this->blockPage);
        } else {
            $blockHTML = file_get_contents(__dir__.'/block.html');
        }

        return str_replace('{incidentId}', $incidentId, $blockHTML);
    }

    public function json(array $data = [], $status = 200, $msg = '', $return = false)
    {
        header('Content-Type: application/json; charset=utf-8');
        header($this->protocol.' '.$status.' '.$msg);
        $data = json_encode($data);
        if ($return) {
            return $data;
        }
        echo $data;
        $this->halt();
    }

    public function halt()
    {
        if (defined('PHPUNIT_SHIELDFY_TESTSUITE') === true) {
            return;
        }
        exit;
    }
}
