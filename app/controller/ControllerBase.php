<?php
namespace app\controller;

class ControllerBase extends \feke\base\ControllerBase
{
	public function __construct ()
	{
		$this->usePlugin();
	}
}