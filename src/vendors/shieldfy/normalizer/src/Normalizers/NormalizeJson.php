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

namespace Shieldfy\Normalizer\Normalizers;

use Shieldfy\Normalizer\NormalizeInterface;
use Shieldfy\Normalizer\PreSearchTrait;

class NormalizeJson implements NormalizeInterface
{
    use PreSearchTrait;

    protected $value;

    /**
     * Constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value     = $value;
        $this->preSearch = [':', '{', '['];
    }

    /**
     * Run the Normalizer.
     *
     * @return mixed normalized $value
     */
    public function run()
    {
        if (! $this->runPreSearch()) {
            return $this->value;
        }

        $decoded = json_decode($this->value, 1);
        $result  = (json_last_error() == JSON_ERROR_NONE) && is_array($decoded);
        if ($result) {
            /* decoded is array */
            if (is_array($decoded)) {
                $arrayValue = '';
                array_walk_recursive($decoded, function ($value, $key) use (&$arrayValue) {
                    $arrayValue .= $key.' '.$value;
                });
                $this->value = $arrayValue;
            }
        }

        return $this->value;
    }
}
