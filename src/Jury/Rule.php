<?php
namespace Shieldfy\Jury;

class Rule
{
    protected $id;
    protected $data = [];
    protected $normalize = false;

    /**
     * [Construct Rule Object]
     * @param integer  $id        [rule id]
     * @param array  $data      [rule data]
     * @param boolean $normalize [use normalizer before rule]
     */
    public function __construct($id, $data = [])
    {
        $this->id = $id;
        $this->data = $data;

        if (isset($data['normalize'])) {
            $this->normalize = $data['normalize'];
        }
    }

    /**
     * run the rule against value
     * @param  mixed $value
     * @param  string $target target scope
     * @param  string $tag    target tag
     * @return mixed $result array|false
     */
    public function run($value, $target = '*', $tag = '*')
    {
        if ($target !== '*' && $target !== $this->data['target']) {
            return;
        }
        if ($tag !== '*' && $tag !== $this->data['tag']) {
            return;
        }

        if ($this->data['type'] == 'EQUAL') {
            $result = $this->runEqual($value);
        }
        if ($this->data['type'] == 'CONTAIN') {
            $result = $this->runContain($value);
        }
        if ($this->data['type'] == 'PREG') {
            $result = $this->runPreg($value);
        }
        if ($this->data['type'] == 'RPREG') {
            $result = $this->runRPreg($value);
        }

        if ($result) {
            return $this->getInfo();
        }
        return false;
    }

    /**
     * is value equal
     * @param  mixed $value
     * @return boolean $result
     */
    private function runEqual($value)
    {

        //multiple equals
        if (strpos($this->data['rule'], '|') !== false) {
            $rules = explode('|', $this->data['rule']);
            foreach ($rules as $rule) {
                if (trim($value) === $rule) {
                    return true;
                }
            }
            return false;
        }

        //single equal
        if (trim($value) === $this->data['rule']) {
            return true;
        }
        return false;
    }

    /**
     * is value contain
     * @param  mixed $value
     * @return boolean $result
     */
    private function runContain($value)
    {

        //multiple contain
        if (strpos($this->data['rule'], '|') !== false) {
            $rules = explode('|', $this->data['rule']);
            foreach ($rules as $rule) {
                if (strpos($value, $rule) !== false) {
                    return true;
                }
            }
            return false;
        }

        //single contain
        if (strpos($value, $this->data['rule']) !== false) {
            return true;
        }
        return false;
    }

    /**
     * PregMatch
     * @param  mixed $value
     * @return boolean $result
     */
    private function runPreg($value)
    {
        if (preg_match('/'.$this->data['rule'].'/isU', $value)) {
            return true;
        }
        return false;
    }

    /**
     * Reverse PregMatch
     * @param  mixed $value
     * @return boolean $result
     */
    private function runRPreg($value)
    {
        if (preg_match('/'.$this->data['rule'].'/isU', $value) === false) {
            return true;
        }
        return false;
    }

    public function needNormalize()
    {
        return $this->normalize;
    }

    /**
     * get rule info
     * @return array $info;
     */
    public function getInfo()
    {
        return [
            'id' => $this->id,
            'score' => $this->data['score']
        ];
    }
}
