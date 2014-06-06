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
 * Redirect クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */
trait Redirect {

	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Redirect;

	/**
	 * Redirect class のインスタンス
	 * @var object
	 */
	public static $_RedirectInstance;


	/**
	 * リダイレクトクラスのインスタンス
	 */
	public function get_RedirectInstance ()
	{
		if ( ! \plugin\Redirect::$_RedirectInstance) {
			\plugin\Redirect::$_RedirectInstance = \Feke::load('Redirect','util');
		}
		$this->Redirect = \plugin\Redirect::$_RedirectInstance;
	}

	/**
	 * リダイレクトメソッド
	 * @param unknown $que
	 */
	protected function redirect($que = null)
	{
		\plugin\Redirect::$_RedirectInstance->redirect($que);
	}

	/**
	 * reairect メソッドのエイリアス
	 * @param unknown $que
	 */
	protected function jump($que = null)
	{
		\plugin\Redirect::$_RedirectInstance->redirect($que);
	}
}