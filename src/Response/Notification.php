<?php
namespace Shieldfy\Response;

class Notification
{
    public function view($file, $message)
    {
        $html = file_get_contents(__dir__.'/Views/' . $file . '.html');
        $html = str_replace('{message}', $message, $html);
        return $html;
    }

    public function success($data)
    {
        echo $this->view('notification-success', $data);
    }

    public function error($data)
    {
        echo $this->view('notification-error', $data);
    }
}