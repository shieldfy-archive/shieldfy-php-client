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

class NormalizeControlChars implements NormalizeInterface
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
        $this->preSearch = ['%', '&#'];
    }

    /**
     * Run the Normalizer.
     *
     * @return mixed normalized $value
     */
    public function run()
    {


        // critical ctrl values
        $search = [
            chr(0), chr(1), chr(2), chr(3), chr(4), chr(5),
            chr(6), chr(7), chr(8), chr(11), chr(12), chr(14),
            chr(15), chr(16), chr(17), chr(18), chr(19), chr(24),
            chr(25), chr(192), chr(193), chr(238), chr(255), '\\0',
        ];

        $this->value = str_replace($search, '%00', $this->value);


        if (! $this->runPreSearch()) {
            return $this->value;
        }

        //take care for malicious unicode characters
        $this->value = urldecode(
            preg_replace(
                '/(?:%E(?:2|3)%8(?:0|1)%(?:A|8|9)\w|%EF%BB%BF|%EF%BF%BD)|(?:&#(?:65|8)\d{3};?)/i',
                null,
                urlencode($this->value)
            )
        );
        $this->value = urlencode($this->value);
        $this->value = preg_replace('/(?:%F0%80%BE)/i', '>', $this->value);
        $this->value = preg_replace('/(?:%F0%80%BC)/i', '<', $this->value);
        $this->value = preg_replace('/(?:%F0%80%A2)/i', '"', $this->value);
        $this->value = preg_replace('/(?:%F0%80%A7)/i', '\'', $this->value);
        $this->value = urldecode($this->value);

        $this->value = preg_replace('/(?:%ff1c)/', '<', $this->value);
        $this->value = preg_replace('/(?:&[#x]*(200|820|200|820|zwn?j|lrm|rlm)\w?;?)/i', null, $this->value);
        $this->value = preg_replace(
            '/(?:&#(?:65|8)\d{3};?)|'.
            '(?:&#(?:56|7)3\d{2};?)|'.
            '(?:&#x(?:fe|20)\w{2};?)|'.
            '(?:&#x(?:d[c-f])\w{2};?)/i',
            null,
            $this->value
        );

        $this->value = str_replace(
            [
                '«',
                '〈',
                '＜',
                '‹',
                '〈',
                '⟨',
            ],
            '<',
            $this->value
        );
        $this->value = str_replace(
            [
                '»',
                '〉',
                '＞',
                '›',
                '〉',
                '⟩',
            ],
            '>',
            $this->value
        );

        return $this->value;
    }
}
