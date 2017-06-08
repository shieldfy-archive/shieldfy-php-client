<?php
namespace Shieldfy\Response;

class Respond
{
    const BLOCKSTATUS = 403;
    const BLOCKMESSAGE = 'Dangerous Request Blocked :: Shieldfy Web Shield';

    protected $protocol;

    public function __construct($protocol = 'HTTP/1.1')
    {
        $this->protocol = $protocol;
    }

    public function block($incidentId)
    {
        header($this->protocol.' '.self::BLOCKSTATUS.' '.self::BLOCKMESSAGE);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Shieldfy-Status: blocked');
        header('X-Shieldfy-Block-Id: '.$incidentId);
        echo $this->prepareBlockResponse($incidentId);
        $this->halt();
    }

    public function returnBlock($incidentId)
    {
        header($this->protocol.' '.self::BLOCKSTATUS.' '.self::BLOCKMESSAGE);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Shieldfy-Status: blocked');
        header('X-Shieldfy-Block-Id: '.$incidentId);
        $response = $this->prepareBlockResponse($incidentId);
        ;
        return $response;
    }

    private function prepareBlockResponse($incidentId)
    {
        $blockHTML = file_get_contents(__dir__.'/block.html');
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
