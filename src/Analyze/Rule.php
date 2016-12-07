<?php

namespace Shieldfy\Analyze;

class Rule
{
	/**
	 * @var string ruleId
	 */
	protected $id;

	/**
	 * @var regex rule
	 */
	protected $rule;

	/**
	 * @var integer rule score
	 */
	protected $score;

	/**
	 * @var rule matched result
	 */
	protected $matches;

	/**
	 * Constructor
	 * @param string $id 
	 * @param array|array $data 
	 */
	public function __construct($id , array $data = [])
	{
		$this->id = $id;
		$this->rule = $data[0];
		$this->score = $data[1];
	}	

	/**
	 * Execute the rule
	 * @param string $value 
	 * @return boolean
	 */
	public function execute($value)
	{
		$result = preg_match('/'.$this->rule.'/isU', $value, $matches);
		$this->matches = $matches;
		return ($result === 1)? true : false;
	}

	/**
	 * get Id
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * get score
	 * @return integer
	 */
	public function getScore()
	{
		return $this->score;
	}

	/**
	 * get rule
	 * @return regex string
	 */
	public function getRule()
	{
		return $this->rule;
	}

	/**
	 * get matched result
	 * @return mixed
	 */
	public function getMatches()
	{
		return $this->matches;
	}

	/**
	 * get matched result length
	 * @return type
	 */
	public function getLength()
	{
		if($this->matches)
			return strlen($this->matches[0]);
		return 0;
	}
}