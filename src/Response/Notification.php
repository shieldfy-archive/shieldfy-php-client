<?php
namespace Shieldfy\Response;

class Notification
{
    public function view($file, $data)
    {
        $html = file_get_contents(__dir__.'/Views/' . $file . '.html');
        $data['logo'] = __FILE__.'/Views/img/logo.png';
        foreach ($data as $key => $value) {
            $html = str_replace('{'. $key .'}', $value, $html);
        }
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