<?php

namespace Shieldfy\Analyze;

use Shieldfy\Config;

interface RulesBagInterface
{
    public function __construct(Config $config);

    public function load();

    public function run($method, $key, $value);

    public function getScore();

    public function getRulesIds();
}
