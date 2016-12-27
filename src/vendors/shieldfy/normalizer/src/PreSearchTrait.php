<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Shieldfy Normaization Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Shieldfy Normaization Package
 * License: The MIT License (MIT)
 * Link:    https://shieldfy.com
 */

namespace Shieldfy\Normalizer;

trait PreSearchTrait
{
    protected $preSearch; //characters to search before normalize to speed up the process

    public function runPreSearch()
    {
        $needles = $this->preSearch;
        foreach ($needles as $needle) {
            if (strpos($this->value, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
