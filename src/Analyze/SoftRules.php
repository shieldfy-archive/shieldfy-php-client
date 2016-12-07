<?php

namespace Shieldfy\Analyze;

use Shieldfy\Config;

class SoftRules implements RulesBagInterface
{
    /**
     * @var config
     */
    protected $config;
    /**
     * @var array rules bag
     */
    protected $rules = [];

    /**
     * @var int score
     */
    protected $score = 0;

    /**
     * @var array rule ids bag
     */
    protected $rulesIds = [];

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Load rules from store.
     */
    public function load()
    {
        $rules = json_decode(file_get_contents($this->config['rootDir'].'/data/soft_rules'), 1);
        foreach ($rules as $key=> $rule) {
            $this->rules[] = new Rule($key, $rule);
        }

        return $this;
    }

    /**
     * Run the rules against request.
     *
     * @param string $method
     * @param string $key
     * @param mixed  $value
     */
    public function run($method, $key, $value)
    {
        $length = 0;
        $score = 0;
        $rulesIds = [];

        foreach ($this->rules as $rule) {
            $result = $rule->execute($value);
            if ($result) {
                $length += $rule->getLength();
                $score += $rule->getScore();
                $rulesIds[] = $rule->getId();
            }
        }

        if ($score > 0) {
            if (($length >= (strlen($value) / 3))) {
                $score += 5;
            }

            if (($method == 'GET')) {
                $score += 5;
            }
        }

        $this->score = $score;
        $this->rulesIds = $rulesIds;

        return $this;
    }

    /**
     * Get score.
     *
     * @return int score
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * get founded rules ids.
     *
     * @return array rulesIds
     */
    public function getRulesIds()
    {
        return $this->rulesIds;
    }
}
