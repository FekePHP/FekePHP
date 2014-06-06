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
 * Url クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */
trait Url {

	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Url;

	/**
	 * Upload class のインスタンス
	 * @var object
	 */
	public static $_UrlInstance;

	public function get_UrlInstance ()
	{
		if ( ! \plugin\url::$_UrlInstance) {
			\plugin\url::$_UrlInstance = \Feke::load ('Url','util');
		}
		$this->Url = \plugin\url::$_UrlInstance;
	}

	public function url ($name)
	{
		return \plugin\url::$_UrlInstance->{$name}();
	}
}