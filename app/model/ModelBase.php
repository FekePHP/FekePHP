<?php
namespace app\model;

class ModelBase extends \feke\base\ModelBase
{
	public function __construct ()
	{
		$this->usePlugin(__CLASS__);
	}
}