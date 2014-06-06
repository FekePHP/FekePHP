<?php
/**
 * Part of the Feke framework.
 *
 * @package Feke
 * @author Shohei Miyazawa
 * @since PHP 5.3
 */

namespace plugin;

/**
 * Outputのインスタンス取得
 * @param  string|arra$name
 * @return string|array
 */

trait Output
{
	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Output;

	/**
	 * Output class のインスタンス
	 * @var object
	 */
	public static $_OutputInstance;

	/**
	 * インスタンス取得
	 * @param  string|arra$name
	 * @return string|array
	 */
	public function get_OutputInstance ()
	{
		if ( ! \plugin\Output::$_OutputInstance) {
			\plugin\Output::$_OutputInstance = \Feke::load ('Output', 'util');
		}
		$this->Rest = \plugin\Output::$_OutputInstance;
	}
}