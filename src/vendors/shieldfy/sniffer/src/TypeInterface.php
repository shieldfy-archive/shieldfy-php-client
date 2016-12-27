<?php

namespace Shieldfy\Sniffer;

/**
 * String Type Interface.
 */
interface TypeInterface
{
    public function sniff($str);

    public function __toString();
}
