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

class Normalizer
{
    protected $value;

    protected $normalizers = [
        'base64'               => \Shieldfy\Normalizer\Normalizers\NormalizeBase64::class,
        'serialize'            => \Shieldfy\Normalizer\Normalizers\NormalizeSerialize::class,
        'json'                 => \Shieldfy\Normalizer\Normalizers\NormalizeJson::class,
        'reDos'                => \Shieldfy\Normalizer\Normalizers\NormalizeReDosAttempts::class,
        'comments'             => \Shieldfy\Normalizer\Normalizers\NormalizeComments::class,
        'space'                => \Shieldfy\Normalizer\Normalizers\NormalizeWhiteSpace::class,
        'quotes'               => \Shieldfy\Normalizer\Normalizers\NormalizeQuotes::class,
        'entities'             => \Shieldfy\Normalizer\Normalizers\NormalizeEntities::class,
        'rawEncoding'          => \Shieldfy\Normalizer\Normalizers\NormalizeURLRawEncoding::class,
        'sqlHex'               => \Shieldfy\Normalizer\Normalizers\NormalizeSQLHex::class,
        'sqlKeywords'          => \Shieldfy\Normalizer\Normalizers\NormalizeSQLKeywords::class,
        'controlChars'         => \Shieldfy\Normalizer\Normalizers\NormalizeControlChars::class,
        'outOfRangeChars'      => \Shieldfy\Normalizer\Normalizers\NormalizeOutOfRangeChars::class,
        'UTFHexEncode'         => \Shieldfy\Normalizer\Normalizers\NormalizeUTFHexEncode::class,
        'jsCharcode'           => \Shieldfy\Normalizer\Normalizers\NormalizeJSCharcode::class,
        'jsRegexModifiers'     => \Shieldfy\Normalizer\Normalizers\NormalizeJSRegexModifiers::class,
        'UTF7'                 => \Shieldfy\Normalizer\Normalizers\NormalizeUTF7::class,
        'concatenated'         => \Shieldfy\Normalizer\Normalizers\NormalizeConcatenated::class,
        'proprietaryEncodings' => \Shieldfy\Normalizer\Normalizers\NormalizeProprietaryEncodings::class,
        'urlencodeSqlComment'  => \Shieldfy\Normalizer\Normalizers\NormalizeUrlencodeSqlComment::class,

    ];

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function runAll()
    {
        $value = $this->value;

        foreach ($this->normalizers as $className) {
            $value = (new $className($value))->run();
        }

        return $value;
    }

    public function run($normalizer)
    {
        $value = $this->value;
        if (! isset($this->normalizers[$normalizer])) {
            throw new \Exception('Normalizer Not found use one of supported normalizers ( '.implode(' , ', array_keys($this->normalizers)).' )');
        }

        $className = $this->normalizers[$normalizer];

        return (new $className($value))->run();
    }
}
