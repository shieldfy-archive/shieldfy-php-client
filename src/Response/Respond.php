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
        $blockHTML = file_get_contents(__dir__.'/block.html');
        echo str_replace('{incidentId}', $incidentId, $blockHTML);
        $this->halt();
    }


    public function json(array $data = [], $status = 200 , $msg = '', $return = false)
    {
        header('Content-Type: application/json; charset=utf-8');
        header($this->protocol.' '.$status.' '.$msg);
        $data = json_encode($data);
        if($return) return $data;
        echo $data;
        $this->halt();
    }

    public function halt()
    {
        exit;
    }
}
