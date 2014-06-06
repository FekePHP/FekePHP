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
 * Sessionのマスククラスです．
 *
 * php標準のセッション機能を使ってセッションを管理します．
 *
 * **プラグインの読み込み**
 * プラグインとして読み込む場合は、インポートが必要です。
 * ※Controller,Modelbaseには読み込まれています。
 * {{{php|
 * use \plugin\Session;
 * }}}
 *
 * **インスタンスから直接、挿入や取得を行う**
 * インスタンスにプロパティを指定することで直接挿入、取得を行うことができます。
 * ただし、文字列の先頭がアンダーバーの場合のみ、挿入・取得できない仕様となっています。
 * {{{php|
 * //プラグインと併用した使用例
 * //挿入
 * $this->Session->name = 'FekePHP';
 * $this->Session->{'item.id'} = 100;
 *
 * //取得
 * echo $this->Session->name;
 * echo $this->Session->{'item.id'};
 * }}}
 * @package    Feke
 * @subpackage util
 * @plugin \plugin\Session
 * @config /util/session
 */

class Session
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * コンフィグの保存
	 */
	private static $_flash_data;

	/**
	 * コンフィグの保存
	 */
	private static $_config;

	/**
	 * コンストラクタ
	 * @class hide
	 */
	public function __construct ()
	{
		self::$_config = \Feke::loadConfig('/util/session', true)->SESSION;;
	}


	/**
	 * セッションの開始
	 * @class hide
	 */
	public function open ()
	{
		//session名
		session_name(self::$_config->COOKIE_NAME);

		if(!session_start()) $this->throwError('Sessionの開始に失敗しました。', true);

		//SESSTIONの再生成間隔
		if (self::$_config->UPDATE_TIME > 0) {
			if(mt_rand(1, self::$_config->UPDATE_PROBABILITY) == 1) {
				$up_wait_sec = self::$_config->UPDATE_TIME;

				$last_update_time = date("YmdHis",strtotime("$up_wait_sec second" ,strtotime(self::get('session_last_update'))));
				$time = date("YmdHis",strtotime('now'));
				if ($last_update_time < $time) {
					self::regenerate ();
				}
			}
			//SESSIONの最終更新時間のセット
			self::set('session_last_update',date('YmdHis'));
		}

		//IPアドレス取得
		if (self::$_config->MATCH_IP) {
			$old_ip = $this->get('_ip_address');
			$now_ip = ip2long($_SERVER["REMOTE_ADDR"]);

			//IPアドレスでのチェック
			if (isset($old_ip)) {
				if ($old_ip != $now_ip) {
					self::destroy();
					if(!session_start()) $this->throwError('Sessionの開始に失敗しました。', true);
				}
			}
			self::set('_ip_address',$now_ip);
		}

		//ユーザーエージェント取得
		if (self::$_config->MATCH_USERAGENT) {
			$old_ua = self::get('_user_agent');
			$now_ua = $_SERVER['HTTP_USER_AGENT'];

			//IPアドレスでのチェック
			if (isset($old_ua)) {
				if ($old_ua != $now_ua) {
					self::destroy();
					if(!session_start()) $this->throwError('Sessionの開始に失敗しました。', true);
				}
			}
			self::set('_user_agent',$now_ua);
		}
		self::_flash();
	}

	/**
	 * セッションを終了する
	 *
	 *  @class hide
	 */
	public function close ()
	{
		session_write_close();
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
	 * 指定したキーのセッションを取得します。
	 *
	 * $nameが指定されていない場合は、$_SESSIONのすべての値を取得します。
	 *
	 * @param string $name $_SESSION 変数のキー
	 * @return $_SESSIONに指定のキーがあればそのデータを返し、存在しない場合は、falseを返します。
	 * @example echo $Session->get ('name');
	 *
	 *          //インスタンスから直接取得できます。
	 *          echo $Session->name;
	 */
	public static function get ($name = null)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				if (isset($_SESSION[$name])) return $_SESSION[$name];
				else return;
			} else {
				$parm = explode('.', $name);
				$session = $_SESSION;
				foreach ($parm as $key) {
					if (isset($session[$key])) $session = $session[$key];
					else return null;
				}
				return $session;
			}
		}
		return $_SESSION;
	}

	/**
	 * 指定したキーにセッションをセットします。
	 * @param string  $filed
	 * @param mixed   $value
	 * @class hide
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * 指定したキーにセッションをセットします。
	 *
	 *
	 * @param string $name   $_SESSION 変数のキー
	 * @param mixed  $value  書き込む値
	 * @example $Session->set ('name', 'feke');
	 *
	 *          //インスタンスから直接挿入できます。
	 *          $Session->name = 'feke';
	 */
	public static function set ($name, $value)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				$_SESSION[$name] = $value;
			} else {
				$params = explode('.', $name);

				$value = to_array($value);
				$add_array = $value;
				$count = count($params);

				$array_name = null;
				for ($i = $count - 1; $i >= 0; $i--) {
					$add_array = array();
					$add_array[$params[$i]] = $value;
					$value = $add_array;
				}
				
				
				$function = function ($array, $add_array) use (&$function) {
					foreach ($add_array as $key => $value) {
						if (isset($array[$key])) {
							if (is_array($value)){
								$array[$key] = $function ($array[$key], $value);
							}
							else $array[$key] = $value;
						} else {
							if (!is_array($array)) $array = array();
							$array[$key] = $value;
							return $array;
						}
					}
					return $array;
				};
				
				
				$_SESSION = $function($_SESSION, $add_array);
				/*
				if ($is_live) {
					$add_array = array_merge_recursive($add_array, $new_array);

					$_SESSION = array_merge($_SESSION, $add_array);
				} else {
					$_SESSION = array_merge($_SESSION, $add_array);
				}*/
			}
		}
	}

	/**
	 * 指定したキーのセッションを削除する
	 *
	 * @param string $name $_SESSION 変数のキー
	 * @example $Session->del ('name');
	 */
	public static function del ($name)
	{
		//self::set($name , null);

		if ($name) {
			if (strpos($name, '.') === false) {
				unset($_SESSION[$name]);
			} else {
				$parm = explode('.', $name);
				$count = count($parm);

				if ($count == 2) {
					unset($_SESSION[$parm[0]][$parm[1]]);
				} elseif ($count == 3) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]]);
				} elseif ($count == 4) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]]);
				} elseif ($count == 5) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]][$parm[4]]);
				} elseif ($count == 6) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]][$parm[4]][$parm[5]]);;
				} elseif ($count == 7) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]][$parm[4]][$parm[5]][$parm[6]]);
				} elseif ($count == 8) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]][$parm[4]][$parm[5]][$parm[6]][$parm[7]]);
				} elseif ($count == 9) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]][$parm[4]][$parm[5]][$parm[6]][$parm[7]][$parm[8]]);
				} elseif ($count == 10) {
					unset($_SESSION[$parm[0]][$parm[1]][$parm[2]][$parm[3]][$parm[4]][$parm[5]][$parm[6]][$parm[7]][$parm[8]][$parm[9]]);
				}
			}
		}
	}

	/**
	 * フラッシュの管理
	 */
	private static function _flash ()
	{
		if (!self::$_flash_data) {
			self::$_flash_data = self::get('flash');
			self::del('flash');
		}
	}

	/**
	 * フラッシュの書き込みを行います。
	 *
	 * @param string $name   登録したいフラッシュ名
	 * @param mixed  $value  書き込む値
	 * @example $Session::setFlash ('name', 'feke');
	 */
	public static function setFlash ($name, $value)
	{
		$name = "flash.{$name}";
		self::set ($name, $value);
	}

	/**
	 * フラッシュの読み込みを行います。
	 *
	 *  @param string $name フラッシュ名
	 *  @example $Session->getFlash ('name', 'feke');
	 */
	public static function getFlash ($name)
	{
		if ($name) {
			if (strpos($name, '.') === false) {
				if (isset(self::$_flash_data[$name])) return self::$_flash_data[$name];
				else return null;
			} else {
				$parm = explode('.', $name);
				$session = self::$_flash_data;
				foreach ($parm as $key) {
					if (isset($session[$key])) $session = $session[$key];
					else return null;
				}
				return $session;
			}
		}
		return self::$_flash_data;
	}

	/**
	 * フラッシュの維持を行います。
	 *
	 * @param string $name フラッシュ名
	 * @example $Session->keepFlash ();
	 */
	public static function keepFlash ()
	{
		self::set('flash',self::$_flash_data);
	}

	/**
	 * セッションの破棄
	 *
	 * 参考
	 * @link http://www.php.net/manual/ja/function.session-destroy.php
	 * @example $Session->destroy ();
	 */
	public static function destroy ()
	{
		//セッション変数の解除
		$_SESSION = array();

		//セッションIDを保存しているクッキーの破棄
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		//セッションの破棄
		session_destroy();
	}

	/**
	 * セッションIDを取得を行います。
	 *
	 * @return セッションID
	 * @example $Session->id ();
	 */
	public static function id ()
	{
		return session_id();
	}

	/**
	 * セッションIDを再生成を行います。
	 *
	 * @example $Session->regenerate ();
	 */
	public static function regenerate ()
	{
		session_regenerate_id(true);
	}

	/**
	 * セッション名を取得します。
	 * @example $Session->name ();
	 */
	public static function name ()
	{
		return session_name();
	}

	/**
	 * トークンの発行
	 *
	 * トークンの発行と，フラッシュへの書き込みを行います．
	 * トークンはセッションID+マイクロ秒+rand()を設定されているハッシュで作成します
	 *
	 * @example $Sesion->token ();
	 */
	public static function token()
	{
		$token = hash(\Feke::config("HASH")->type, sha1(mt_rand()) . self::id() . md5(microtime()));
		self::setFlash('token', $token);
		self::setFlash('csrftoken', $token);
		return $token;
	}

	/**
	 * セッション名の整形
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
