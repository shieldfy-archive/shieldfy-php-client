<?php

namespace Shieldfy\Sniffer\Types;

use Shieldfy\Sniffer\TypeInterface;

class StringType implements TypeInterface
{
    public function sniff($input)
    {
        return !preg_match('/[^a-z0-9\.\s]/isU', $input);
    }

    public function __toString()
    {
        return 'string';
    }
}
