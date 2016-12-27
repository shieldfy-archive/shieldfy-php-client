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

class NormalizeEntities implements NormalizeInterface
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
        $this->preSearch = ['&amp;', '&#', ':'];
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

        $converted = null;
        //deal with double encoded payload
        $this->value = preg_replace('/&amp;/', '&', $this->value);
        if (preg_match('/&#x?[\w]+/ms', $this->value)) {
            $converted = preg_replace('/(&#x?[\w]{2,6}\d?);?/ms', '$1;', $this->value);
            $converted = html_entity_decode($converted, ENT_QUOTES, 'UTF-8');
            $this->value .= "\n".str_replace(';;', ';', $converted);
        }
        // normalize obfuscated protocol handlers
        $this->value = preg_replace(
            '/(?:j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:)|(d\s*a\s*t\s*a\s*:)/ms',
            'javascript:',
            $this->value
        );

        return $this->value;
    }
}
