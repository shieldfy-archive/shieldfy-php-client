<?php
namespace Shieldfy\Jury;
class Rule
{
    protected $id;
    protected $data = [];
    protected $normalize;

    /**
     * [Construct Rule Object]
     * @param integer  $id        [rule id]
     * @param array  $data      [rule data]
     * @param boolean $normalize [use normalizer before rule]
     */
    public function __construct($id, $data = [], $normalize = false)
    {
        $this->id = $id;
        $this->data = $data;
        $this->normalize = $normalize;
    }

    public function run($key,$value)
    {
        if($this->data['type'] == 'EQUAL') $this->runEqual($key,$value);
        if($this->data['type'] == 'CONTAIN') $this->runContain($key,$value);
        if($this->data['type'] == 'PREG') $this->runPreg($key,$value);
        if($this->data['type'] == 'RPEG') $this->runRPreg($key,$value);
    }

    private function runEqual($key,$value)
    {
        if(trim($value) == $data['rule']) return true;
        return false;
    }
    private function runContain($key,$value)
    {
        if(strpos($value, $data['rule']) !== false) return true;
        return false;
    }
    private function runPreg($key,$value)
    {
        if(preg_match('/'.$data['rule'].'/isU',$value)) return true;
        return false;
    }
    private function runRPreg($key,$value)
    {
        if(preg_match('/'.$data['rule'].'/isU',$value) == false) return true;
        return false;
    }

    public function getInfo()
    {
        return [
            'id' => $this->id,
            'score' => $data['score']
        ];
    }
}
