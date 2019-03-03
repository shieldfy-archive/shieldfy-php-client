<?php

namespace Shieldfy\Queue;

class UserConfig
{
    public function getData()
    {
        $data = file_get_contents(__DIR__.'/userConfig.json');
        return (array) json_decode($data);
    }
}