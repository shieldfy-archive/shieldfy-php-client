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

class NormalizeUrlencodeSqlComment implements NormalizeInterface
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
        $this->preSearch = ['%'];
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

        if (preg_match_all('/(?:\%23.*?\%0a)/im', $this->value, $matches)) {
            $converted = $this->value;
            foreach ($matches[0] as $match) {
                $converted = str_replace($match, ' ', $converted);
            }
            $this->value .= "\n".$converted;
        }

        return $this->value;
    }
}
