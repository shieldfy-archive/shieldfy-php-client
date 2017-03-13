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

    /**
     * run the rule against value
     * @param  mixed $value
     * @param  string $target target scope
     * @param  string $tag    target tag
     * @return array|false
     */
    public function run($value,$target = '*',$tag = '*')
    {
        //echo $target.' !== '.$this->data['target'].'<br />';
        if($target !== '*' && $target !== $this->data['target']) return;
        if($tag !== '*' && $tag !== $this->data['tag']) return;

        if($this->data['type'] == 'EQUAL') $result = $this->runEqual($value);
        if($this->data['type'] == 'CONTAIN') $result = $this->runContain($value);
        if($this->data['type'] == 'PREG') $result = $this->runPreg($value);
        if($this->data['type'] == 'RPREG') $result = $this->runRPreg($value);
        var_dump($result);
        if($result){
            return $this->getInfo();
        }
        return false;
    }

    /**
     * [runEqual description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    private function runEqual($value)
    {
        if(trim($value) === $this->data['rule']) return true;
        return false;
    }

    /**
     * [runContain description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    private function runContain($value)
    {
        if(strpos($value, $this->data['rule']) !== false) return true;
        return false;
    }

    /**
     * [runPreg description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    private function runPreg($value)
    {
        if(preg_match('/'.$this->data['rule'].'/isU',$value)) return true;
        return false;
    }

    /**
     * [runRPreg description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    private function runRPreg($value)
    {
        if(preg_match('/'.$this->data['rule'].'/isU',$value) == false) return true;
        return false;
    }

    /**
     * [getInfo description]
     * @return [type] [description]
     */
    public function getInfo()
    {
        return [
            'id' => $this->id,
            'score' => $this->data['score']
        ];
    }
}
