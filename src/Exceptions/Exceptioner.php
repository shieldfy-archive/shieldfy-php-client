<?php
namespace Shieldfy\Exceptions;
trait Exceptioner
{
	/* throw new exception */
	public function throwException($exception)
	{
		if($this->config && $this->config['debug'] === true){
			throw $exception;
		}
	}
}
