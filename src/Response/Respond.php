<?php
namespace Shieldfy\Response;
class Respond
{
    public function block()
    {

    }


    public function json(array $data = [], $status = 200 , $msg = '', $return = false)
    {
        header('Content-Type: application/json');
        header($_SERVER['SERVER_PROTOCOL'].' '.$status.' '.$msg);
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
