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

namespace feke\util;

/**
 * $_POST,$_GET,$_SERVERなどのマスククラスです．
 *
 * **プラグインの読み込み**
 * プラグインとして読み込む場合は、インポートが必要です。
 * ※Controller,Modelbaseには読み込まれています。
 * {{{php|
 * use \plugin\Get;
 * }}}
 *
 * **インスタンスから取得を行う**
 * インスタンスにプロパティを指定することで直接、取得を行うことができます。
 * ただし、文字列の先頭がアンダーバーの場合は、取得できない仕様となっています。
 * {{{php|
 * //プラグインと併用した使用例
 * //取得
 * echo $this->Input->name;
 * echo $this->Input->{'item.id'};
 * }}}
 * @package    Feke
 * @subpackage util
 * @plugin \plugin\Input
 * @config /util/Input
 */

class Input
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * リクエストの配列
	 */
	private static $_request_data;

	public function __construct()
	{
		if (!isset(self::$_request_data)) {
			self::$_request_data = array_merge(array_merge($_GET, $_POST), \Feke::_('url_params'));
		}
	}

	/**
	 * 値の取得
	 * @param string $filed
	 * @class hide
	 */
	public function __get($filed)
	{
		return self::param($filed);
	}

	/**
	 * 指定したキーの取得値を取得します。
	 *
	 * $nameが指定されていない場合は、URLパラメータ,$_POST,$_GETのすべての値を取得します。
	 *
	 * @param string $name $_SESSION 変数のキー
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return URLパラメータ,$_POST,$_GETに指定のキーがあればそのデータを返し、存在しない場合は、falseを返します。
	 * @example echo $Input->param ('name');
	 *
	 *          //インスタンスから直接取得できます。
	 *          echo $Input->name;
	 */
	public function param ($name = null, $default = null)
	{
		if (is_value($name)) {
			if (strpos($name, '.') === false) {
				if (isset(self::$_request_data[$name])) return self::$_request_data[$name];
				else return $default;
			} else {
				$parm = explode('.', $name);
				$get_array = self::$_request_data;
				foreach ($parm as $key) {
					if (isset($get_array[$key])) $get_array = $get_array[$key];
					else return $default;
				}
				return $get_array;
			}
		}
		return self::$_request_data;
	}

	/**
	 * 指定したキーの$_GETの値を取得します。
	 *
	 * $nameが指定されていない場合は、$_Getのすべての値を取得します。
	 *
	 * @param string $name $_GET 変数のキー
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Input->get ('name');
	 *
	 *          //インスタンスから直接取得できます。
	 *          echo $Input->name;
	 */
	public function get ($name = null, $default = null)
	{
		if (is_value($name)) {
			if (strpos($name, '.') === false) {
				if (isset($_GET[$name])) return $_GET[$name];
				else return;
			} else {
				$parm = explode('.', $name);
				$get_array = $_GET;
				foreach ($parm as $key) {
					if (isset($get_array[$key])) $get_array = $get_array[$key];
					else return null;
				}
				return $get_array;
			}
		}
		return $_GET;
	}

	/**
	 * 指定したキーの$_POSTの値を取得します。
	 *
	 * $nameが指定されていない場合は、$_Getのすべての値を取得します。
	 *
	 * @param string $name $_POST 変数のキー
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Input->post ('name');
	 *
	 *          //インスタンスから直接取得できます。
	 *          echo $Input->name;
	 */
	public function post($name = null, $default = null)
	{
		if (is_value($name)) {
			if (strpos($name, '.') === false) {
				if (isset($_POST[$name])) return $_POST[$name];
				else return;
			} else {
				$parm = explode('.', $name);
				$get_array = $_POST;
				foreach ($parm as $key) {
					if (isset($get_array[$key])) $get_array = $get_array[$key];
					else return null;
				}
				return $get_array;
			}
		}
		return $_POST;
	}

	/**
	 * 指定したキーの$_FILEの値を取得します。
	 *
	 * $nameが指定されていない場合は、$_FILEのすべての値を取得します。
	 *
	 * @param string $name $_FILE 変数のキー
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Session->file ('name');
	 */
	public function file ($name = null, $default = null)
	{
		if (is_value($name)) {
			if (strpos($name, '.') === false) {
				if (isset($_FILES[$name])) return $_FILES[$name];
				else return;
			} else {
				$parm = explode('.', $name);
				$get_array = $_FILES;
				foreach ($parm as $key) {
					if (isset($get_array[$key])) $get_array = $get_array[$key];
					else return null;
				}
				return $get_array;
			}
		}
		return $_FILES;
	}

	/**
	 * 指定したキーの$_SERVERの値を取得します。
	 *
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Input->server();
	 */
	public function server ($name = null, $default = null)
	{
		if (is_value($name)) {
			if (isset($_SERVER[$name])) return $_SERVER[$name];
			else return $default;
		}
		return $_SERVER;
	}

	/**
	 * USER_AGENTを取得します。
	 *
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Input->ua();
	 */
	public function ua ($default = null)
	{
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			return $_SERVER['HTTP_USER_AGENT'];
		}
		return $default;
	}

	/**
	 * クライアントのディバイスを簡易に特定します。
	 *
	 * ***返り値のリスト***
	 * |~種類|返り値|
	 * |PC|pc|
	 * |Androidタブレット,iPad|tab|
	 * |スマートフォン|sp|
	 * |携帯(ガラゲー)|mobi|
	 *
	 * @return 上記表に記載されているいずれかの返り値が返されます。
	 * @example echo $Input->device();
	 */
	public function device()
	{
		$ua_text = $this->ua();

		//iPad
		if (stripos($ua_text, 'iPad') !== false) {
			return 'tab';
		}

		//スマートフォン
		//Android, iPhone,iPod,BlackBerry,Windows Phone,その他UAにMobileが入っている場合
		$target = ['Mobile','BlackBerry','Windows Phone','Symbian'];
		foreach ($target as $name) {
			if (stripos($ua_text, $name) !== false) return 'sp';
		}

		//Android タブレット
		if (stripos($ua_text, 'Android') !== false) {
			return 'tab';
		}

		//ガラゲー
		$target = ['DoCoMo','UP.Browser','SoftBank','Vodafone','J-PHONE','SMOT','WILLCOM','emobile'];
		foreach ($target as $name) {
			if (stripos($ua_text, $name) !== false) return 'mobi';
		}
		//その他
		return 'pc';
	}

	/**
	 * リファラを取得します。
	 *
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Input->referer ();
	 */
	public function referer ($default = null)
	{
		if (isset($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}
		return $default;
	}

	/**
	 * IPアドレスを取得します。
	 *
	 * @param string $default 要素が見つからなかった場合に返す値
	 * @return 指定のキーがあればそのデータを返し、存在しない場合は、$defaultの値をを返します。
	 * @example echo $Input->referer ();
	 */
	public function ip ($default = '0.0.0.0')
	{
		if (isset($_SERVER["REMOTE_ADDR"])) {
			return $_SERVER["REMOTE_ADDR"];
		}
		return $default;
	}


	/**
	 * リクエストがPOSTか確認します。
	 *
	 * @return POSTならtrue,その他の場合はfalseを返します。
	 */
	public function isPost ()
	{
		if ($_SERVER["REQUEST_METHOD"] === "POST") return true;
		return false;
	}

	/**
	 * リクエストがGETか確認します。
	 *
	 * @return POSTならtrue,その他の場合はfalseを返します。
	 */
	public function isGet ()
	{
		if ($_SERVER["REQUEST_METHOD"] === "GET") return true;
		return false;
	}
}
