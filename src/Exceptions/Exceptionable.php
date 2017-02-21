<?php
namespace Shieldfy\Exceptions;
use Throwable;

interface Exceptionable
{
	public function throwException(Throwable $exception);
}