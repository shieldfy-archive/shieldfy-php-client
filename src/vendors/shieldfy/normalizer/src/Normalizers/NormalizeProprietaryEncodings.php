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

class NormalizeProprietaryEncodings implements NormalizeInterface
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
        //Xajax error reportings
        $this->value = preg_replace('/<!\[CDATA\[(\W+)\]\]>/im', '$1', $this->value);

        //strip false alert triggering apostrophes
        $this->value = preg_replace('/(\w)\"(s)/m', '$1$2', $this->value);

        //strip quotes within typical search patterns
        $this->value = preg_replace('/^"([^"=\\!><~]+)"$/', '$1', $this->value);

        //OpenID login tokens
        $this->value = preg_replace('/{[\w-]{8,9}\}(?:\{[\w=]{8}\}){2}/', null, $this->value);

        //convert Content and \sdo\s to null
        $this->value = preg_replace('/Content|\Wdo\s/', null, $this->value);

        //strip emoticons
        $this->value = preg_replace(
            '/(?:\s[:;]-[)\/PD]+)|(?:\s;[)PD]+)|(?:\s:[)PD]+)|-\.-|\^\^/m',
            null,
            $this->value
        );

        //normalize separation char repetion
        $this->value = preg_replace('/([.+~=*_\-;])\1{2,}/m', '$1', $this->value);

        //normalize multiple single quotes
        $this->value = preg_replace('/"{2,}/m', '"', $this->value);

        //normalize quoted numerical values and asterisks
        $this->value = preg_replace('/"(\d+)"/m', '$1', $this->value);

        //normalize pipe separated request parameters
        $this->value = preg_replace('/\|(\w+=\w+)/m', '&$1', $this->value);

        //normalize ampersand listings
        $this->value = preg_replace('/(\w\s)&\s(\w)/', '$1$2', $this->value);

        //normalize escaped RegExp modifiers
        $this->value = preg_replace('/\/\\\(\w)/', '/$1', $this->value);

        return $this->value;
    }
}
