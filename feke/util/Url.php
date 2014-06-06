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

namespace feke\util;

use feke\config\Config as C;

/**
 * url取得クラスです。
 *
 * 各種URL等を保存・管理しているクラスです．
 *
 * このクラスで取得できるURLは、
 * -ドメイン
 * -ルートURL?
 * -現在のURL
 * -現在のURL(get無し)
 * -get部分のみ
 * となります。
 *
 *
 * **プラグインとして読み込み**
 * {{{php|
 * use \plugin\Url;
 * }}}
 * プラグインとして読み込む場合は、インポートが必要です。
 * ※Controller,Modelbaseに読み込まれています。
 * @package    Feke
 * @subpackage util
 * @plugin \plugin\Url
 */
class Url
{
	/**
	 * プロトコル+ドメイン名
	 * @var string
	 */
	private static $_root_url;

	/**
	 * 現在動作しているオブジェクトのURL
	 * @var string
	 */
	private static $_obj_url;

	/**
	 * 現在のURL
	 * @var string
	 */
	private static $_now_url;
	
	/**
	 * https通信のフラグ
	 * @var booren
	 */
	private static $_https_flag = false;


	/**
	 * コンストラクタ
	 *
	 * 一回目のインスタンス時のみ動作
	 *
	 * @class hide
	 */
	public static function construct()
	{
		if (!self::$_root_url) {
			self::_setRootUrl ();
			self::_setNowUrl();
			self::_setObjUrl();
			self::_setHttps();
		}
	}

	/**
	 * ドメインを取得します。
	 * @return string
	 * @example //名前空間使用時
	 *          \feke\util\Url::domain ();
	 *
	 *          //プラグイン使用時
	 *          $this->Url->domain ();
	 */
	public static function domain ()
	{
		if (!self::$_root_url) self::construct();
		return $_SERVER["SERVER_NAME"];
	}

	/**
	 * ドメインのURLを取得します。
	 *
	 * @param  string $flag 指定されていた場合、強制的にURLのプロトコル部(http,https)をその指定ワードに書き換えます。
	 * @return string
	 * @example //名前空間使用時
	 *          \feke\util\Url::root ();
	 *
	 *          //プラグイン使用時
	 *          $this->Url->root ();
	 */
	public static function root ($flag = null)
	{
		if (!self::$_root_url) self::construct();
		return self::_plusHttp(self::$_root_url, $flag);
	}

	/**
	 * 実行しているオブジェクトのURLを取得します。
	 *
	 * @param  string $flag 指定されていた場合、強制的にURLのプロトコル部(http,https)をその指定ワードに書き換えます。
	 * @example //名前空間使用時
	 *          \feke\util\Url::obj ();
	 *
	 *          //プラグイン使用時
	 *          $this->Url->obj ();
	 */
	public static function obj ($flag = null)
	{
		if (!self::$_root_url) self::construct();
		return self::_plusHttp(self::$_obj_url, $flag);
	}

	/**
	 * 現在のURLを取得します。
	 *
	 * @param  string $flag 指定されていた場合、強制的にURLのプロトコル部(http,https)をその指定ワードに書き換えます。
	 * @return string
	 * @example //名前空間使用時
	 *          \feke\util\Url::now ();
	 *
	 *          //プラグイン使用時
	 *          $this->Url->now ();
	 */
	public static function now ($flag = null)
	{
		if (!self::$_root_url) self::construct();
		return self::_plusHttp(self::$_now_url, $flag);
	}

	/**
	 * 現在のURLを取得(GETなし)を取得します。
	 *
	 * @param  string $flag 指定されていた場合、強制的にURLのプロトコル部(http,https)をその指定ワードに書き換えます。
	 * @return string
	 * @example //名前空間使用時
	 *          \feke\util\Url::noGet ();
	 *
	 *          //プラグイン使用時
	 *          $this->Url->noGet ();
	 */
	public static function noGet ($flag = null)
	{
		if (!self::$_root_url) self::construct();
		return preg_replace('/\?.+/', '', self::_plusHttp(self::$_now_url, $flag));
	}

	/**
	 * URLのGET部分を取得します。
	 *
	 * @return string
	 * @example //名前空間使用時
	 *          \feke\util\Url::get ();
	 *
	 *          //プラグイン使用時
	 *          $this->Url->get ();
	 */
	public static function get ()
	{
		if (!self::$_root_url) self::construct();
		
		return $_SERVER['QUERY_STRING'];
	}

	/**
	 * https通信か確認します。
	 *
	 * @return boolen https通信ならtrue,それ以外ならfalseを返します。
	 * @example if (\feke\util\Url::isHttps() {
	 *              echo 'https通信です。';
	 *          }
	 */
	public static function isHttps ()
	{
		if (!self::$_root_url) self::construct();
		return self::$_https_flag;
	}

	/**
	 * FekeフレームワークのwebrootのトップURLをセット
	 */
	private function _setRootUrl ()
	{
		self::$_root_url =  $_SERVER["HTTP_HOST"];
	}

	/**
	 * 現在のURLをセット
	 */
	private function _setNowUrl ()
	{
		self::$_now_url =  $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	}


	/**
	 * オブジェクトのルートURLをセット
	 */
	private function _setObjUrl ()
	{
		$floer = \Feke::_('floer');

		// パラメーター取得
		$param = preg_replace("/^\//u", '', $_SERVER['REQUEST_URI']);

		// パラメーターを / で分割
		if ($param != '') $params = explode('/',  $param);

		$url = $_SERVER["HTTP_HOST"];
		
		for ($i=0; $i<$floer; $i++) {
			$url .= "/{$params[$i]}";
		}
		self::$_obj_url = $url;
	}
	
	/**
	 * Httpsをセット
	 */
	private function _setHttps ()
	{
		if( (FALSE === empty($_SERVER['HTTPS'])) && ('off' !== $_SERVER['HTTPS'])) {
			self::$_https_flag = true;
		} else {
			self::$_https_flag = false;
		}
	}
	
	/**
	 * urlを先頭部分を補完
	 */
	private function _plusHttp ($url, $flag)
	{
		if ($flag) {
			return $flag.'://'.$url;
		} elseif (self::$_https_flag) {
			return 'https://'.$url;
		} else {
			return 'http://'.$url;
		}
		
	}

}