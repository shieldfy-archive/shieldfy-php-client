<?php
namespace Shieldfy\Exceptions;

interface Exceptionable
{
	public function throwException($exception);
}