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
        $this->rules = (new Rules($this->config,$name))->build();
    }

    public function normalize($value)
    {
        //normalizer
        $value = (new Normalizer($value))->runAll();
        return $value;
    }

    /* the judge */
    public function sentence($value,$target = '*',$tag = '*')
    {
        // $result = [
        //     'score'=>0,
        //     'ruleIds'=>[]
        // ];
        $score = 0;
        $ruleIds = [];
        foreach($this->rules as $rule){
            $res = $rule->run($value,$target,$tag);
            if($res['score'] > 0){
                $score += $res['score'];
                $ruleIds[] = $res['id'];
            }
        }
        return [
            'score'=>$score,
            'ruleIds'=>$ruleIds
        ];
    }
}
