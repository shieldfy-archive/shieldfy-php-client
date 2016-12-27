<?php

namespace Shieldfy\Sniffer\Types;

use Shieldfy\Sniffer\TypeInterface;

class JsonType implements TypeInterface
{
    public function sniff($input)
    {
        $decoded = json_decode($input);
        $result = (json_last_error() == JSON_ERROR_NONE);

        return $result && (is_object($decoded) || is_array($decoded));
    }

    public function __toString()
    {
        return 'json';
    }
}
