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
 * Cookie クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */

trait Cookie {

	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Cookie;

	/**
	 * Cookie class のインスタンス
	 * @var object
	 */
	public static $_CookieInstance;

	/**
	 * Cookei class のインスタンス取得
	 *
	 * @class hide
	 */
	public function get_CookieInstance ()
	{
		if (!\plugin\cookie::$_CookieInstance) {
			\plugin\cookie::$_CookieInstance = \Feke::load ('Cookie', 'util');
		}
		$this->Cookie = \plugin\cookie::$_CookieInstance;
	}
}