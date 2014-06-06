<?php
/**
 * Part of the Feke framework.
 *
 * @package    feke
 * @version    0.4
 * @author     Shohei Miyazawa
 * @license    GNU General Public License (GPL)
 * @copyright  Copyright (c) FekePHP (http://fekephp.com/)
 * @link       http://fekephp.com/
 */

namespace plugin;

/**
 * Header クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */

class Header {
	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Header;

	/**
	 * Validation class のインスタンス
	 * @var object
	 */
	public static $_HeaderInstance;


	/**
	 * Read class のインスタンス取得
	 */
	public function get_HeaderInstance ()
	{
		if ( ! \plugin\Header::$_HeaderInstance) {
			\plugin\Header::$_HeaderInstance = \Feke::load ('Header', 'util');
		}
		$this->Header = \plugin\Header::$_HeaderInstance;
	}
}