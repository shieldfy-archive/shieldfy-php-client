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

class NormalizeUTF7 implements NormalizeInterface
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
        $this->preSearch = ['+A', '+I'];
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

        if (preg_match('/\+A\w+-?/m', $this->value)) {
            if (function_exists('mb_convert_encoding')) {
                $this->value .= "\n".mb_convert_encoding($this->value, 'UTF-8', 'UTF-7');
            } else {
                //list of all critical UTF7 codepoints
                $schemes = [
                    '+ACI-'      => '"',
                    '+ADw-'      => '<',
                    '+AD4-'      => '>',
                    '+AFs-'      => '[',
                    '+AF0-'      => ']',
                    '+AHs-'      => '{',
                    '+AH0-'      => '}',
                    '+AFw-'      => '\\',
                    '+ADs-'      => ';',
                    '+ACM-'      => '#',
                    '+ACY-'      => '&',
                    '+ACU-'      => '%',
                    '+ACQ-'      => '$',
                    '+AD0-'      => '=',
                    '+AGA-'      => '`',
                    '+ALQ-'      => '"',
                    '+IBg-'      => '"',
                    '+IBk-'      => '"',
                    '+AHw-'      => '|',
                    '+ACo-'      => '*',
                    '+AF4-'      => '^',
                    '+ACIAPg-'   => '">',
                    '+ACIAPgA8-' => '">',
                ];

                $this->value = str_ireplace(
                    array_keys($schemes),
                    array_values($schemes),
                    $this->value
                );
            }
        }

        return $this->value;
    }
}
