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

namespace feke\core;

/**
 * 簡易デバッククラスです。
 *
 * POST,GET,SESSION,COOKIEや実行したSQL,オートロードしたクラス、読み込んだ設定ファイルなどの確認ができます。
 *
 * \feke\config\Coreconfig内の「const DEBUG」が、''1以上''の時に動作し、''2以上''の場合は、でバックバーが表示されます。
 *
 * @package    feke
 * @subpackage util
 */
class Debug
{
	/**
	 * デバックレベル
	 */
	private static $_debugLevel = 0;

	/**
	 * FekePHP開始時間
	 * @var unknown
	 */
	private static $_srartTime;

	/**
	 * FekePHP終了時間
	 * @var unknown
	 */
	private static $_endTime;

	/**
	 * オートロードリスト
	 */
	private static $_loadClass;

	/**
	 * 読み込んだ設定ファイル
	 */
	private static $_loadConfig;

	/**
	 * SQLリスト
	 */
	private static $_setSqlList;

	/**
	 * SQLバインドリスト
	 */
	private static $_setBindList;

	/**
	 * デバックメッセージリスト
	 */
	private static $_setMessageList;


	/**
	 * デバッグレベルのセット
	 *
	 * @param string $time
	 */
	public static function setDebugLevel ($level)
	{
		self::$_debugLevel = $level;
	}

	/**
	 * デバッグレベルのセット
	 *
	 * @param string $time
	 */
	public static function getDebugLevel ()
	{
		return self::$_debugLevel;
	}

	/**
	 * 開始時間のセット
	 *
	 * @param string $time
	 */
	public static function setSrartTime ($time)
	{
		self::$_srartTime = $time;
	}

	/**
	 * 終了時間のセット
	 *
	 * @param string $time
	 */
	public static function setEndTime ($time)
	{
		self::$_endTime = $time;
	}

	/**
	 * オートロードしたクラスを記録します。
	 *
	 * @param string オートロードしたクラスのパス
	 * @param string オートロードしたクラスの名前
	 * @class hide
	 */
	public static function setLoadClass ($path, $name)
	{
		self::$_loadClass[$name] = $path;
	}

	/**
	 * 読み込んだ設定ファイルを記録します。
	 *
	 * @param string 設定ファイルのパス
	 * @class hide
	 */
	public static function setLoadConfig ($path)
	{
		self::$_loadConfig[] = $path;
	}

	/**
	 * 発行したSQLのリスト
	 *
	 * @param array $array クエリのデータ
	 * @class hide
	 */
	public static function setSqlList ($array)
	{
		self::$_setSqlList[] = $array;
	}

	/**
	 * 発行したSQLのバインドのリスト
	 *
	 * @param array $sql クエリに使用したバインドの値の配列
	 * @class hide
	 */
	public static function setBindList ($sql)
	{
		self::$_setBindList[] = $sql;
	}

	/**
	 * デバック用にメッセージを埋め込みます。
	 *
	 * @param mixed $data デバックバーに表示したい任意のなにか
	 */
	public static function setMessage ($data)
	{
		self::$_setMessageList[] = $data;
	}


	/**
	 * PDO用のデバックを取得する。
	 *
	 * @class hide
	 */
	public static function debugPdo ($msg, $file, $tree)
	{
		//最後に発行されたSQL
		self::$_setSqlList[] = "<span style='color:red'>".\feke\util\QueryBuilder::$_do_sql."</span>";
		echo "<p>{$msg}</p>";
		echo "<p>{$file}</p>";
		echo "<pre>";
		print_r($tree);
		echo "</pre>";
	}


	/**
	 * 簡易デバックバーの表示をする
	 *
	 * @return string
	 */
	public static function debugBar ()
	{
		//デバックの設定値が2未満なら終了
		if (debug_level() < 2) return;

		$tmp_path = FEKE_ROOT_PATH."/feke/core/Debug/debugBar.php";

		//スクリプトの実行時間を計測
		if(!self::$_srartTime) self::$_srartTime = $_SERVER['REQUEST_TIME_FLOAT'];
		if (!self::$_endTime)  self::$_endTime   = microtime(true);

		//実行時間
		$run_time = number_format((self::$_endTime - self::$_srartTime)*1000, 1);

		//最大使用メモリ
		$memory = number_format(memory_get_peak_usage()/pow(2,20),1);

		//FekePHPの情報
		$feke = array(
			'FekePHP' => 'ver 0.4',
		);

		//作成する要素
		$set = array(
				'FekeDebug' => $feke,
				'Message' => self::$_setMessageList,
				'SQL' => self::$_setSqlList,
				'COOKIE' => $_COOKIE,
				'POST' => $_POST,
				'GET' => $_GET,
				'AutoLoad' => self::$_loadClass,
				'Include' => get_included_files(),
				'Config' => self::$_loadConfig,

		);
		if (isset($_SESSION)) {
			$set['SESSION'] = $_SESSION;
		} else {
			$set['SESSION'] = ['セッションは動作していません。'];
		}

		$datalist = "";
		foreach ($set as $filed => $data) {
			$datalist .=  "<div id=\"{$filed}_show\" class=\"box\"><table class=\"list\">";
			if (is_array($data)) {
				foreach ($data as $key => $value) {
					$datalist .= "<tr>";

					//SQL
					if ($filed == 'SQL') {
						$datalist .= "<th style=\"width:50px;\">{$key}</th>";
						$datalist .= "<td>{$value['sql']}<br>";

						if (!self::$_setBindList[$key]) {
							$datalist .= "</td>";
						} else {
							foreach (self::$_setBindList[$key] as $bkey => $bvalue) {
								if (is_string($bvalue)) $bvalue = "'{$bvalue}'";
								$datalist .=  "{$bkey} = <b>{$bvalue}</b><br>";
							}
							$datalist .= "</td>";
						}

						$datalist .= "<td>{$value['class']}</td><td>{$value['function']} ()</td><td>{$value['line']}</td>";

					} else if ($filed == 'AutoLoad') {
						$datalist .=  "<th>{$key}</th><td><pre>".print_r($value, true)."</pre></td>";
					} else if ($filed == 'Message') {
						$datalist .=  "<th style=\"width:50px;\">{$key}</th><td><pre>".print_r($value['data'], true)."</pre></td>";
						$datalist .= "<td>{$value['debug']['class']}</td><td>{$value['debug']['function']} ()</td><td>{$value['line']}</td>";

					} else if ($filed == 'Include') {
						$datalist .=  "<th style=\"width:50px;\">{$key}</th><td><pre>".print_r($value, true)."</pre></td>";
					} else if ($filed == 'Config') {
						$datalist .=  "<th style=\"width:50px;\">{$key}</th><td><pre>".print_r($value, true)."</pre></td>";
					}else {
						$datalist .=  "<th>{$key}</th><td><pre>".print_r($value, true)."</pre></td>";
					}
					$datalist .= "</tr>";
				}
			}
			$datalist .=  "</table>";
			$datalist .=  "</div>";
		}

		//js
		$target = ['FekeDebug','Message','COOKIE','SESSION','POST','GET','SQL','AutoLoad','Include','Config'];
		$tab_list = "";
		$js_tab_list = "";
		foreach ($target as $value) {
			$js_tab_list .= "document.getElementById(\"{$value}_show_tab\").className = \"\";";
			$tab_list .= "<li onClick='display_debug(\"{$value}_show\");' id=\"{$value}_show_tab\">{$value}</li>";
		}

		require_once $tmp_path;
	}
}
