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

class NormalizeJSCharcode implements NormalizeInterface
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
        $matches = [];

        // check if value matches typical charCode pattern
        if (preg_match_all('/(?:[\d+-=\/\* ]+(?:\s?,\s?[\d+-=\/\* ]+)){4,}/ms', $this->value, $matches)) {
            $converted = '';
            $string    = implode(',', $matches[0]);
            $string    = preg_replace('/\s/', '', $string);
            $string    = preg_replace('/\w+=/', '', $string);
            $charcode  = explode(',', $string);

            foreach ($charcode as $char) {
                $char = preg_replace('/\W0/s', '', $char);

                if (preg_match_all('/\d*[+-\/\* ]\d+/', $char, $matches)) {
                    $match = preg_split('/(\W?\d+)/', implode('', $matches[0]), null, PREG_SPLIT_DELIM_CAPTURE);

                    if (array_sum($match) >= 20 && array_sum($match) <= 127) {
                        $converted .= chr(array_sum($match));
                    }
                } elseif (! empty($char) && $char >= 20 && $char <= 127) {
                    $converted .= chr($char);
                }
            }

            $this->value .= "\n".$converted;
        }

        // check for octal charcode pattern
        if (preg_match_all('/(?:(?:[\\\]+\d+[ \t]*){8,})/ims', $this->value, $matches)) {
            $converted = '';
            $charcode  = explode('\\', preg_replace('/\s/', '', implode(',', $matches[0])));

            foreach (array_map('octdec', array_filter($charcode)) as $char) {
                if (20 <= $char && $char <= 127) {
                    $converted .= chr($char);
                }
            }
            $this->value .= "\n".$converted;
        }

        // check for hexadecimal charcode pattern
        if (preg_match_all('/(?:(?:[\\\]+\w+\s*){8,})/ims', $this->value, $matches)) {
            $converted = '';
            $charcode  = explode('\\', preg_replace('/[ux]/', '', implode(',', $matches[0])));

            foreach (array_map('hexdec', array_filter($charcode)) as $char) {
                if (20 <= $char && $char <= 127) {
                    $converted .= chr($char);
                }
            }
            $this->value .= "\n".$converted;
        }

        return $this->value;
    }
}
