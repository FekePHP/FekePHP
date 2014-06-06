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

/**
 * クッキーのマスククラスです．
 *
 * **プラグインの読み込み**
 * プラグインとして読み込む場合は、インポートが必要です。
 * ※Controller,Modelbaseには読み込まれています。
 * {{{php|
 * use \plugin\Cookie;
 * }}}
 *
 * **インスタンスから直接、挿入や取得を行う**
 * インスタンスにプロパティを指定することで直接挿入、取得を行うことができます。
 * ただし、文字列の先頭がアンダーバーの場合のみ、挿入・取得できない仕様となっています。
 * {{{php|
 * //プラグインと併用した使用例
 * //挿入
 * $this->Cookie->name = 'FekePHP';
 * $this->Cookie->{'item.id'} = 100;
 *
 * //取得
 * echo $this->Cookie->name;
 * echo $this->Cookie->{'item.id'};
 * }}}
 *
 *
 * @package    Feke
 * @subpackage util
 * @plugin     \plugin\Cookie
 */

class Cookie
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBaseStatic;

	/**
	 * 第一配列で使用する配列名
	 *
	 * クッキーの第一配列で使用する名前です．
	 * 他のアプリケーションと衝突しにくくなります．
	 *
	 * デフォルトでは，
	 * /app/config/config.phpの「COOKIE_MAME」定数が使用されます．
	 *
	 * @var string
	 */
	private static $_cookie_name;

	public function __construct ()
	{
		self::$_cookie_name = \Feke::loadConfig('/util/cookie')->CONFIG->cookie_name;
	}

	/**
	 * 値の取得
	 * @param string $filed
	 * @class hide
	 */
	public function __get($filed)
	{
		return self::get($filed);
	}

	/**
	 * クッキーの内容を取得します。
	 *
	 * @param  string $name 取得したいクッキーの配列名
	 * @return mixed クッキーに指定のデータがあればそのデータを返し、存在しない場合は、falseを返します。
	 * @example //名前空間使用時
	 *          echo \feke\util\Cookie::get ('name');
	 *
	 *          //プラグイン使用時
	 *          echo $this->Cookie->get ('name');
	 */
	public static function get ($name = null)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				if (isset($_COOKIE[self::$_cookie_name][$name])) {
					return $_COOKIE[self::$_cookie_name][$name];
				}
				else return false;
			} else {
				$parm = explode('.', $name);
				$cookie = $_COOKIE[self::$_cookie_name];
				foreach ($parm as $key) {
					$cookie = $cookie[$key];
				}
				if ('' == strval($cookie) and !is_array($cookie)) return false;
				else return $cookie;
			}
		}
		return $_COOKIE;
	}

	/**
	 * 指定したキーにセッションをセットします。
	 * @param string  $filed
	 * @param mixed   $value
	 * @class hide
	 */
	public function __set ($name, $value)
	{
		return $this->set ($name, $value);
	}

	/**
	 * クッキーをセットします。
	 *
	 * @param string $name セットしたいクッキー名
	 * @param mixed  $value セットしたい値
	 * @param string $expire
	 * @param string $domain
	 * @example //名前空間使用時
	 *          \feke\util\Cookie::set ('name','feke');
	 *
	 *          //プラグイン使用時
	 *          $this->Cookie->set ('name','feke');
	 */
	public static function set ($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				setcookie("{self::$_cookie_name}[$name]", $value, $expire,  $path, $domain ,$secure ,$httponly);
			} else {
				$name = $this->_moldName($name);
				setcookie("{self::$_cookie_name}[$name]", $value, $expire,  $path, $domain ,$secure ,$httponly);
			}
		}
		$_COOKIE[self::$_cookie_name][$name] = $value;
	}

	/**
	 * クッキーを消去します。
	 *
	 * @param string $name 削除したいクッキーの配列名
	 * @example //名前空間使用時
	 *          echo \feke\util\Cookie::del ('name');
	 *
	 *          //プラグイン使用時
	 *          echo $this->Cookie->del ('name');
	 */
	public static function del ($name, $path = null, $domain = null, $secure = null, $httponly = null)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				return setcookie("{self::$_cookie_name}[$name]", '', time() - 10000, $path, $domain ,$secure ,$httponly);
			} else {
				$name = $this->_moldName($name);
				return setcookie("{self::$_cookie_name}[$name]", '', time() - 10000, $path, $domain ,$secure ,$httponly);
			}
		}
		$_COOKIE[self::$_cookie_name][$name] = '';
	}

	/**
	 * クッキーの存在を確認します。
	 *
	 * @param string $name 存在を確認したいクッキーの配列名
	 * @example //名前空間使用時
	 *          echo \feke\util\Cookie::check ('name');
	 *
	 *          //プラグイン使用時
	 *          echo $this->Cookie->check ('name');
	 * @return クッキーの存在すればtrue、しなければfalseを返します。
	 */
	public static function check ($name)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				if (isset($_COOKIE[self::$_cookie_name][$name])) {
					return true;
				}
				else return false;
			} else {
				$parm = explode('.', $name);
				$cookie = $_COOKIE[self::$_cookie_name];
				foreach ($parm as $key) {
					$cookie = $cookie[$key];
				}
				if ('' == strval($cookie) and !is_array($cookie)) return false;
				else return true;
			}
		}
		return false;
	}

	/**
	 * クッキー名の整形
	 */
	private static function _moldName ($name)
	{
		$parm = explode('.', $name);
		$path = '';
		foreach ($parm as $key) {
			if ($path) $path .= "][";
			$path .= "$key";
		}
		return $path;
	}
}