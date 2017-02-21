<?php
namespace Shieldfy\Exceptions;
use Throwable;
trait Exceptioner
{
	/* throw new exception */
	public function throwException(Throwable $exception)
	{
		if($this->config['debug'] === true){
			throw $exception;
		}
	}
}