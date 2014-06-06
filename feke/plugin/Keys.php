<?php
/**
 * Part of the Feke framework.
 *
 * @package    feke
 * @version    0.4.0
 * @author     Miyazawa
 * @license    GNU General Public License (GPL)
 * @copyright  Copyright (c) FekePHP (http://fekephp.com/)
 * @link       http://fekephp.com/
 */

namespace plugin;

/**
 * Urlパラメータ(Key) 取得用のプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */
trait Keys {
	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Keys;

	/**
	 * Keys class のインスタンス
	 * @var object
	 */
	public static $_KeysInstance;

	/**
	 * keys クラスのインスタンス取得
	 */
	public function get_Keysinstance ()
	{
		if ( ! \plugin\Keys::$_KeysInstance) {
			$Request = \Feke::load ('Keys','util');
			\plugin\Keys::$_KeysInstance = $Request->get_keys();
		}
		$this->Keys = \plugin\Keys::$_KeysInstance;
	}

	/**
	 * urlパラメータを取得
	 * @param unknown $name
	 */
	protected function key ($name)
	{
		if ($name) return $this->Keys[$name];
		return $this->Keys;
	}
}