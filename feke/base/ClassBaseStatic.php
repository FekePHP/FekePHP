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

namespace feke\base;

/**
 * クラスのエラーメソッドベースです。
 *
 * エラーの入出力、パスの補完等を行えるメソッドを所持したトレイトです。
 *
 *
 * @package    Feke
 * @subpackage base
 */

trait ClassBaseStatic
{

	/**
	 * エラーメッセージ
	 * @var string
	 */
	protected static $_error_message;

	/**
	 * エラーを返す方法の設定
	 * @var string
	 */
	protected static $_errorMode = 'exce';

	/**
	 * システム用例外クラス
	 * @var string
	 */
	protected static $_sys_exception = '\feke\error\FekeError';

	/**
	 * ユーザー用例外クラス
	 * @var string
	 */
	protected static $_user_exception = '\Error';

	/**
	 * 検証する設定
	 * @var array
	 */
	protected static $_data = array();

	/**
	 * 設定
	 * @var object
	 */
	protected static $_Config;

	/**
	 * 設定を行います
	 */
	public static function setConfig ($config)
	{
		self::$_Config = $config;
	}

	/**
	 * エラーメッセージを取得します。
	 * @return エラーメッセージがあた場合はメッセージ（文字列or配列）を、なかった場合はtrueを返します。
	 * @example //トレイトのため、インポート先クラスの名前によって呼び出し方法が変わります。
	 *          //インスタンスから呼び出す場合
	 *          $インスタンス->getError ();
	 *
	 *          //クラス内から呼び出す場合
	 *          $this->getError ();
	 */
	public static function getError ()
	{
		if ($this->_error_message) {
			return self::$_error_message;
		}
		return false;
	}

	/**
	 * エラーの有無を取得します。
	 * @return エラーが有った場合はtrue、なかった場合はfalseを返します。
	 * @example //トレイトのため、インポート先クラスの名前によって呼び出し方法が変わります。
	 *          //インスタンスから呼び出す場合
	 *          $インスタンス->error ();
	 *
	 *          //クラス内から呼び出す場合
	 *          $this->error ();
	 */
	public static function error ()
	{
		if (self::$_error_message) {
			return true;
		}
		return false;
	}

	/**
	 * データ配列を取得します。
	 * @return boolean
	 * @example //トレイトのため、インポート先クラスの名前によって呼び出し方法が変わります。
	 *          //インスタンスから呼び出す場合
	 *          $インスタンス->getData ();
	 *
	 *          //クラス内から呼び出す場合
	 *          $this->getData ();
	 */
	public static function getData ()
	{
		if (self::$_data) {
			return self::$_data;
		}
		return false;
	}

	/**
	 * エラーモードの変更
	 * @class hide
	 * @param string $flag
	 */
	public static function errorMode ($flag)
	{
		if ($flag == 'bool') {
			self::$_errorMode = 'bool';
		} else {
			self::$_errorMode = 'exce';
		}
	}

	/**
	 * エラーのモードごとの出力変更
	 * @param string $msg
	 */
	protected static function throwError ($error, $system_fg = null)
	{
		if (is_string($error)) {
			if (preg_match("/\[string:(.*?):(.*?)\]/",$error, $m)) {
				$error = "\${$m[1]}引数には文字列を指定してください。<br>渡された内容<pre>".print_r($m[2],true)."</pre>";
			} elseif (preg_match("/\[numeric:(.*?):(.*?)\]/",$error, $m)) {
				$error = "\${$m[1]}引数には整数を指定してください。<br>渡された内容<pre>".print_r($m[2],true)."</pre>";
			} elseif (preg_match("/\[array:(.*?):(.*?)\]/",$error, $m)) {
				$error = "\${$m[1]}引数には配列を指定してください。<br>渡された内容<pre>".print_r($m[2],true)."</pre>";
			} elseif (preg_match("/\[object:(.*?):(.*?)\]/",$error, $m)) {
				$error = "\${$m[1]}引数にはオブジェクトを指定してください。<br>渡された内容<pre>".print_r($m[2],true)."</pre>";
			} elseif (preg_match("/\[boolen:(.*?):(.*?)\]/",$error, $m)) {
				$error = "\${$m[1]}引数には論理型を指定してください。<br>渡された内容<pre>".print_r($m[2],true)."</pre>";
			}
			$msg = $error;
		} else {
			$msg = $error[0]['message'];
		}

		if (self::$_errorMode === 'bool') {
			//boolenを返す
			self::$_error_message = $msg;
			return false;
		} else {
			//例外を発生させる
			self::$_error_message = $msg;

			if (!is_array($error)) {
				$error = array();
				$backtraces = debug_backtrace();
				foreach ($backtraces as $key => $list) {
					if (isset($backtraces[1 + $key]['class'])) $class_name = $backtraces[1 + $key]['class'];
					if (isset($backtraces[1 + $key]['function'])) $method_name = $backtraces[1 + $key]['function'];
					if (isset($backtraces[$key]['line'])) $line = $backtraces[$key]['line'];

					$object = new \stdClass;
					$object->class = $class_name;
					$object->method = $method_name;
					$object->line = $line;
					$object->message = $msg;
					$error[] = $object;
				}
			}

			if($system_fg === true) {
				throw new self::$_sys_exception ($error);
			}
			else {
				throw new self::$_user_exception ($error);
			}
		}
	}

	/**
	 * 補完されたファイルパスを取得
	 *
	 * 1.そのまま
	 * 2.FEKE_APP_PATH を補完
	 * 3.FekePHPディレクトリを補完
	 * 3.FEKE_ROOT_PATH を補完
	 *
	 * @class hide
	 * @param string $file_name  補完したいファイル名
	 * @param string $connect    補完したいファイル名の前につけるディレクトリ
	 * @param array  $$extension 読み込みをさせい拡張子
	 * @return string|false
	 */
	public static function compPath ($file_name, $connect = null, $extension = null)
	{
		$object = \Feke::_('object');

		$readable = function ($file_name) use ($extension)  {
			if (is_array($extension)) {
				foreach ($extension as $name) {
					$name = $file_name.$name;
					if (is_readable($name)) {
						return $name;
					}
				}
				return false;
			} else {
				if (is_readable($file_name)) {
					return $file_name;
				}
				return false;
			}
		};

		//絶対パス
		if (false !== ($new_path = $readable ($file_name))) {
			return $new_path;
			//オブジェクト名を補完
		} elseif (false !== ($new_path = $readable (FEKE_ROOT_PATH."/{$object}".$connect.$file_name))) {
			return $new_path;
			//アプリケーション名を補完（パッケージ動作時のみ）
		} elseif (\Feke::_('package') and false !== ($new_path = $readable (FEKE_ROOT_PATH.'/'.FEKE_APP_NAME.'/'.$connect.$file_name))) {
			return $new_path;
			//FekePHPのパスを補完
		} elseif (false !== ($new_path = $readable (FEKE_ROOT_PATH.'/feke'.$connect.$file_name))) {
			return $new_path;
			//ルートパスを補完
		} elseif (false !== ($new_path = $readable (FEKE_ROOT_PATH.$connect.$file_name))) {
			return $new_path;
		}
		return false;
	}


	/**
	 * メゾットが呼び出し可能か確認
	 *
	 * メソッドが存在かつ、呼び出せる場合のみtrueを返します。
	 * @class hide
	 * @param string|object $class_name
	 * @param string        $method_name
	 *
	 * @return boolen
	 */
	public static function _allowCall ($controllerInstance, $method_name) {
		if (method_exists($controllerInstance,$method_name)) {
			if (is_callable(array($controllerInstance, $method_name))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * トレイトのクラスインスタンス作成
	 *
	 * クラス内で使用されているインスタンス作成メソッドの実行します。
	 * クラスの指定があった場合は、そのクラス内のトレイトを、
	 * ない場合は、全体のトレイの「トレイト名_instance」を実行します。
	 *
	 *
	 * サンプル
	 *     \Feke::uses(__CLASS__)
	 *
	 * @param  string
	 * @return boolen
	 */
	public static function usePlugin ($class_name = null)
	{
		if ($class_name) {
			$trait_list = class_uses($class_name);
		} else {
			$trait_list = get_declared_traits();
		}

		if (!$trait_list) return;
		foreach ($trait_list as $name) {
			preg_match('/[A-Za-z0-9_]{1,}$/',$name,$trait_name);
			if($trait_name[0] !== false) {
				$trait_name = 'get_'.$trait_name[0].'Instance';
				if(method_exists($name,$trait_name)) {
					$this->{$trait_name}();
				}
			}
		}
		return true;
	}
}
