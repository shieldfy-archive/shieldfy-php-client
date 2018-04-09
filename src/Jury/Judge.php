<?php
namespace Shieldfy\Jury;

use Shieldfy\Jury\Rules;
use Shieldfy\Normalizer\Normalizer;

trait Judge
{
    protected $issue = null;
    protected $rules = [];
    protected $judgment = null;

    /* load issue rules */
    public function issue($name)
    {
        $this->rules = (new Rules($this->config, $name))->build();
    }

    /**
     * Normalize data
     * @param  mixed $value
     * @param  string $normalizedValue
     * @return normalized value
     */
    public function normalize($value, $normalizedValue = '')
    {
        //no need to normalize if it already normalized
        if ($normalizedValue != '') {
            return $normalizedValue;
        }

        //normalizer
        $value = (new Normalizer($value))->runAll();
        return $value;
    }

    /**
     * The Judge Result
     * @param  mixed $value
     * @param  string $target
     * @param  string $tag
     * @return array $result
     */
    public function sentence($value, $target = '*', $tag = '*')
    {
        $score = 0;
        $ruleIds = [];
        $normalizedValue = '';
        foreach ($this->rules as $rule) {
            if ($rule->needNormalize()) {
                $normalizedValue = $this->normalize($value, $normalizedValue);
                $res = $rule->run($normalizedValue, $target, $tag);
            } else {
                $res = $rule->run($value, $target, $tag);
            }

            if ($res['score'] > 0) {
                $score += $res['score'];
                $ruleIds[] = $res['id'];
            }
        }
        return [
            'score'=>$score,
            'rulesIds'=>$ruleIds
        ];
    }
}
