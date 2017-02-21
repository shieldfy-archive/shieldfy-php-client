<?php
namespace Shieldfy\Dispatcher;
interface Dispatchable
{
	public function trigger($event, $data = []);
}