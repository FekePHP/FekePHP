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

namespace feke\base;

/**
 * controller base
 *
 * @package    Feke
 * @subpackage base
 */
class ControllerBase
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * ローダー用
	 */
	use \plugin\Load;

	/**
	 * テーマクラス用
	 */
	use \plugin\View;

	/**
	 * アクティブレコード用
	 */
	use \plugin\QueryBuilder;

	/**
	 * セッション用
	 */
	use \plugin\Session;

	/**
	 * クッキー用
	 */
	use \plugin\Cookie;

	/**
	 * リダイレクト用
	 */
	use \plugin\Redirect;

	/**
	 * リダイレクト用
	 */
	use \plugin\Output;

	/**
	 * URL読み込み用
	 */
	use \plugin\Url;

	/**
	 * URL読み込み用
	 */
	use \plugin\Input;
}
