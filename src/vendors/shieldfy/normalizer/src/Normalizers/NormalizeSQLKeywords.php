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

class NormalizeSQLKeywords implements NormalizeInterface
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
        $this->preSearch = null;
    }

    /**
     * Run the Normalizer.
     *
     * @return mixed normalized $value
     */
    public function run()
    {
        $pattern = [
            '/(?:is\s+null)|(like\s+null)|'.
            '(?:(?:^|\W)in[+\s]*\([\s\d"]+[^()]*\))/ims',
        ];
        $this->value = preg_replace($pattern, '"=0', $this->value);

        $this->value = preg_replace('/[^\w\)]+\s*like\s*[^\w\s]+/ims', '1" OR "1"', $this->value);
        $this->value = preg_replace('/null([,"\s])/ims', '0$1', $this->value);
        $this->value = preg_replace('/\d+\./ims', ' 1', $this->value);
        $this->value = preg_replace('/,null/ims', ',0', $this->value);
        $this->value = preg_replace('/(?:between)/ims', 'or', $this->value);
        $this->value = preg_replace('/(?:and\s+(\d+)\.?\d*)/ims', ' and $1', $this->value);
        $this->value = preg_replace('/(?:\s+and\s+)/ims', ' or ', $this->value);

        $pattern = [
            '/(?:not\s+between)|(?:is\s+not)|(?:not\s+in)|'.
            '(?:xor|<>|rlike(?:\s+binary)?)|'.
            '(?:regexp\s+binary)|'.
            '(?:sounds\s+like)/ims',
        ];
        $this->value = preg_replace($pattern, '!', $this->value);
        $this->value = preg_replace('/"\s+\d/', '"', $this->value);
        $this->value = preg_replace('/(\W)div(\W)/ims', '$1 OR $2', $this->value);
        $this->value = preg_replace('/\/(?:\d+|null)/', null, $this->value);

        return $this->value;
    }
}
