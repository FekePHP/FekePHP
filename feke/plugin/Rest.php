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
 * アクティブレコードのインスタンス取得
 * @param  string|arra$name
 * @return string|array
 */

trait Rest
{
	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Rest;

	/**
	 * Read class のインスタンス
	 * @var object
	 */
	public static $_RestInstance;

	/**
	 * アクティブレコードのインスタンス取得
	 * @param  string|arra$name
	 * @return string|array
	 */
	public function get_RestInstance ()
	{
		if ( ! \plugin\Rest::$_RestInstance) {
			\plugin\Rest::$_RestInstance = \Feke::load ('Rest', 'util');
		}
		$this->Rest = \plugin\Rest::$_RestInstance;
	}

	/**
	 *
	 */
	public function set($a,$b)
	{
		$this->Rest->set($a,$b);
	}

	/**
	 * ディスプレイメソッド
	 */
	public function display()
	{
		$this->Rest->output();
		return 'rest';
	}
}