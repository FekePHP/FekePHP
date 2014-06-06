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
 * URLを解析して、コントローラ名とアクション名を取得するクラスです。
 *
 * @package     Feke
 * @subpackage  Core
 */
class ConfigLoader
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBaseStatic;

	/**
	 * 設定ファイル読み込み
	 * @param unknown $path
	 * @return multitype:
	 */
	public static function load ($path)
	{
		$path = ltrim($path, '/');
		//ini
		if (false !== ($load_path = self::_moldPath ($path.'.ini'))) {
			return to_object(parse_ini_file($load_path, true));
		} elseif (false !== ($load_path = self::_moldPath ($path.'.yml'))) {
			return parse_ini_file($load_path);
		} else {
			self::throwError("設定ファイル「/config/{$path}」が見つかりませんでした。");
		}
	}

	/**
	 * 環境別設定を読み込み
	 */
	public static function environment ($domain_list)
	{
		foreach ($domain_list as $place => $domain) {
			if ($_SERVER["SERVER_NAME"] == $domain){
				return self::load("/environment/{$place}");
			}
		}
		self::throwError('環境別設定が見つかりませんでした。');
	}

	/**
	 * 設定ファイルのパスを保管します
	 * @param unknown $path
	 * @return string|boolean
	 */
	private static function _moldPath ($path)
	{
		$load_path = app_path().'/config/'.$path;
		if (is_file($load_path)) {
			return $load_path;
		} else {
			$load_path = root_path().'/feke/config/'.$path;
			if (is_file($load_path)) {
				return $load_path;
			}
		}
		return false;
	}
}
