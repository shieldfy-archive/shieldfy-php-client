<?php
namespace Shieldfy\Jury;
use Shieldfy\Jury\Rules;

trait Judge
{

    protected $issue = null;
    protected $rules = [];
    protected $judgment = null;

    /* load issue rules */
    public function issue($name)
    {
        $this->rules = (new Rules($name))->build();
    }

    /* the judge */
    public function sentence($key,$value)
    {
        foreach($this->rules as $rule){
            $result = $rule->run($key,$value);
        }
    }
}
