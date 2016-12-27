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

class NormalizeComments implements NormalizeInterface
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
        $this->preSearch = ['<!', '/*', '--', '#'];
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

        // check for existing comments
        if (preg_match('/(?:\<!-|-->|\/\*|\*\/|\/\/\W*\w+\s*$)|(?:--[^-]*-)/ms', $this->value)) {
            $pattern = [
                '/(?:(?:<!)(?:(?:--(?:[^-]*(?:-[^-]+)*)--\s*)*)(?:>))/ms',
                '/(?:(?:\/\*\/*[^\/\*!]*)+\*\/)/ms', //add ! to avoid remove sql target comments /*! */
                '/(?:--[^-]*-)/ms',
            ];
            $converted   = preg_replace($pattern, ';', $this->value);
            $this->value = "\n".$converted;
        }
        //make sure inline comments are detected and converted correctly
        $this->value = preg_replace('/(<\w+)\/+(\w+=?)/m', '$1/$2', $this->value);
        $this->value = preg_replace('/\/\*([^!]+)?!([^\*\/]+)\*\//m', ' $1 $2 ', $this->value);
        $this->value = preg_replace('/[^\\\:]\/\/(.*)$/m', '/**/$1', $this->value);
        $this->value = preg_replace('/([^\-&])#.*[\r\n\v\f]/m', '$1', $this->value);
        $this->value = preg_replace('/([^&\-])#.*\n/m', '$1 ', $this->value);
        $this->value = preg_replace('/^#.*\n/m', ' ', $this->value);

        return $this->value;
    }
}
