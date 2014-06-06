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

namespace feke\error;

/**
 * エラークラス
 *
 * ただ，404エラー処理するだけのクラスです．
 * Disbapatcherで標準で呼び出され，主にルーティング周りとオートローダの例外をキャッチします．
 *
 * @package    Feke
 * @subpackage error
 *
 */
class ErrorBase
{
	/**
	 * 404エラーの処理
	 *
	 * ログは取りません
	 * @param unknown $e
	 */
	public function NotFound ($e, $name = null)
	{
		if (debug_level() === 0) {
			//self::write_log ('notfound', $e->getError ());
			self::show_errorPage(404);
		} else {
			self::show_debug ($e->getMessage(), $name);
		}
	}

	/**
	 * PDO例外の処理
	 */
	public static function PDOException ($e)
	{
		if (debug_level() === 0) {
			self::write_log ('PDO', $e->getError ());
			self::show_errorPage(500);
		}
		//デバック用
		\feke\core\Debug::debugPdo($e->getMessage (), $e->getFile ()."line : ".$e->getLine(), $e->getTraceAsString());
		self::show_debug ($e, 'PDOException');
	}

	/**
	 * その他
	 */
	public static function Exception ($e, $name = null)
	{
		//コンフィグが読めている場合
		if (debug_level() === 0) {
			self::write_log ('unkwown', $e->getMessage());
			self::show_errorPage(500);
		} else {
			self::show_debug ($e->get(), $name);
		}
		exit;
	}

	/**
	 * shutdown_function用のメソッド
	 */
	public static function error_handler ($e_code, $e_massage, $e_file, $e_line, $e_context)
	{
		// error_reporting 設定に含まれていないエラーコード
		//の場合はreturn
		if (!(error_reporting() & $e_code)) {
			return;
		}

		$throwError = function ($e_massage) {
			//例外を発生させる
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
				$object->message = $e_massage;
				$error[] = $object;
			}
			throw new \feke\error\FekeError ($error);
		};

		//エラーレベルにより変更
		switch ($e_code) {
			case E_USER_ERROR:
				$error = "<h4>WARNING</h4><p> {$e_massage} <br>in {$e_file} on line {$e_line}</p>";
				$throwError($error);
				die();
			case E_WARNING:
			case E_USER_WARNING:
				$error = "<h4>WARNING</h4><p> {$e_massage} <br>in {$e_file} on line {$e_line}</p>";
				$throwError($error);
				die();
			case E_NOTICE:
			case E_USER_NOTICE:
				echo "[NOTICE] {$e_massage} in {$e_file} on line {$e_line}<br>";
				return true;
				//こない
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_CORE_WARNING:
			case E_COMPILE_ERROR:
			case E_COMPILE_WARNING:
			case E_RECOVERABLE_ERROR:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			case E_STRICT:
				return;
			default:
				return true;
				die();
		}
	}

	/**
	 * デバッグツールの表示
	 */
	public static function show_debug ($data, $name = null)
	{
		$tmp_path = FEKE_ROOT_PATH."/feke/error/debug.html";

		$table = "";
		if(is_array($data)) {
			foreach ($data as $key => $value) {
				if ($key === 0) {
					$message = "{$value->message}";
					$class_name = $value->class;
					$line = $value->line;
				}
				$table .= "<tr><td>{$key}</td><td>{$value->class}</td><td>{$value->method}()</td><td align=\"right\">{$value->line} 行目 </td></tr>";
				$last_class = $value->class;
			}
		} else {
			$message = "{$data}";
		}

		$expletion_text = null;
		if ($name) {
			$expletion_text = "<h4>キャッチした例外</h4><p>".h($name)."</p>";
		}

		require_once $tmp_path;
		echo \feke\core\Debug::debugBar(debug_level());
		exit;
	}

	/**
	 * エラー用ページの表示
	 *
	 *
	 * @param unknown $error_code
	 */
	public static function show_errorPage ($error_code)
	{

		//テンプレート読み込み
		$tmp_path = app_path()."/template/error/{$error_code}.html";

		//オブジェクトのテンプレート読み込み
		if (is_readable($tmp_path)) {
			require_once $tmp_path;
		} else {
			//判別不明エラーオブジェクトのテンプレート読み込み
			$tmp_path = app_path()."/template/error/unknown.html";
			if (is_readable($tmp_path)) {
				require_once $tmp_path;
			} else {
				//エラーコードに対するFekePHPのテンプレート読み込み
				$tmp_path = FEKE_ROOT_PATH."/feke/error/{$error_code}.html";
				if (is_readable($tmp_path)) {
					require_once $tmp_path;
				} else {
					//判別不明エラーFekePHPのテンプレート読み込み
					$tmp_path = FEKE_ROOT_PATH."/feke/error/unknown.html";
					if (is_readable($tmp_path)) {
						require_once $tmp_path;
					} else {
						//何もなかった
						echo '<div style="margin: 0 auto;font-size: 200%;text-align: center;">
								<b>ページがみつかりませんでした。</b>
							</div>';
					}
				}
			}
		}

		//php 5.4から
		http_response_code($error_code);
		exit;
	}

	/**
	 * ログ書き込み
	 */
	public static function write_log ($type, $error_msg)
	{
		$path = app_path().'/log/fekephp_error.txt';

		$fp = fopen($path, "a");
		$date = date('Y/m/d H:i:s');
		fprintf($fp, "[%s]  [%10s]  %s\n",$date, $type, $error_msg);
		fclose($fp);
	}

}