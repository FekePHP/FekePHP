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
use \feke\config\RootConfig;

/**
 * URLを解析して、コントローラ名とアクション名を取得するクラスです。
 *
 * @package     Feke
 * @subpackage  Core
 */
class Rooter
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * オブジェクト名
	 * @var string
	 */
	protected static $_object;

	/**
	 * コントローラ名
	 * @var string
	 */
	protected static $_controller;

	/**
	 * アクション名
	 * @var string
	 */
	protected static $_action;

	/**
	 * パッケージフラグ
	 * @var string
	 */
	protected static $_package = false;

	/**
	 * オブジェクトのリスト
	 * @var array
	 */
	protected static $_object_list;

	/**
	 * index.phpのドメイン上での階層数
	 * @var numeric
	 */
	protected static $_floer;

	/**
	 * URLパラメータ
	 * @var array
	 */
	protected static $_url_params = array();

	/**
	 * コンストラクタ
	 *
	 * URLから、コントローラ名、アクション名などを整形・保存するメソッドです。
	 *
	 * @class hide
	 * @param numeric $flore コントローラの階層
	 */
	public function rooting ($flore = null)
	{
		/*
		//一度取得していた場合は終了
		if (self::$_controller and is_null($flore)) return;
		*/

		if (!isset($this->_Config)) $this->_Config = \feke\core\ConfigLoader::load('rootConfig');

		//index.phpの設置場所を求める！
		$droot_params = explode('/',  $_SERVER['DOCUMENT_ROOT']);
		if (count($droot_params) <= 1) $droot_params = explode('\\',  $_SERVER['DOCUMENT_ROOT']);
		$index_params = explode('/',  FEKE_INDEX_PATH);
		if (count($index_params) <= 1) $index_params = explode('\\',  FEKE_INDEX_PATH);

		//ドメイン上の階層数？
		self::$_floer = count($index_params) - count($droot_params) + $flore;

		// ルート用urlを作成
		$target_url = trim(str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '/');

		//拡張子を削除
		if (strpos($target_url, '.') !== false) $target_url = preg_replace('/\.[a-z0-9]+$/i', '', $target_url);

		//スラッシュごとに分割
		$params = explode('/',$target_url);

		//階層と比較して配列を詰める
		for ($i = 0; $i < self::$_floer; $i++) array_shift($params);

		//もう一度もどす
		$target_url = implode('/', $params);

		//ルーティングの確認をする
		if (is_array($text = $this->_checkRoot($target_url))) {
			$target_url = preg_replace("/^{$text[0]}/","{$text[1]}/",$target_url);
		} else {
			$target_url = $text;
		}

		//スラッシュごとに分割
		$params = explode('/',$target_url);

		//パケージ名が存在するか確認
		if (isset($params[0]) and false !== ($package_name = $this->_checkPackage($params[0]))) {
			$object = $params[0];
			array_shift($params);
		} else {
			$object = FEKE_APP_NAME;
		}

		//コントローラ
		if (isset($params[0]) and $params[0]) {
			$controller = $params[0];
		} else {
			$controller = 'index';
		}
		array_shift($params);

		//アクション
		if (isset($params[0]) and $params[0]) {
			$action = $params[0];
		} else {
			$action = 'index';
		}
		array_shift($params);

		//余った部分をぱらめーたとして入れとく
		if (count($params) > 0) {
			foreach ($params as $que) {
				if (strpos($que ,'_') !== false) {
					$field = explode('_', $que)[0];
					$params[$field] = urldecode(str_replace("{$field}_" , '', $que));
				}
			}
			self::$_url_params = array_merge($params, self::$_url_params);
		}

		//共通用
		self::$_object = $object;
		self::$_controller = $controller;
		self::$_action = $action;
	}

	/**
	 * パッケージの有無の確認
	 *
	 * @param  string $name
	 * @return string|false
	 */
	private function _checkRoot ($name)
	{
		$dust_name = strstr($name, '/');
		if (strpos ($name,'/') !== false) $cut_name = strstr($name, '/', true);
		else $cut_name = $name;

		foreach($this->_Config->ROOTING as $key => $value) {
			//パラメータの指定がない場合
			if (strpos($key, ':') === false) {
				if ($key === $cut_name) return $value.$dust_name;
			} else {
				preg_match_all('/:([a-z0-9_]+)/i', $key, $match);
				$key = preg_replace('/:[a-z0-9_]+/i','(.*?)' , $key);
				$key = str_replace('/','\/' , $key);

				//条件と一致した場合
				if (preg_match("/^{$key}$/i", $name, $match2)) {
					foreach ($match[1] as $key2 => $param_key) {
						self::$_url_params[$param_key] = $match2[$key2 + 1];
					}
					self::$_url_params[$param_key] = preg_replace('/\/.*$/','',self::$_url_params[$param_key]);
					return [$key,$value];
				}
			}
		}
		return $name;
	}

	/**
	 * パッケージの有無の確認
	 *
	 * @param  string $name
	 * @return string|false
	 */
	private function _checkPackage ($name)
	{
		if (is_object($this->_Config->PACKAGE)) {
			foreach ($this->_Config->PACKAGE as $key => $value){
				if ($key == $name) {
					self::$_package = true;
					return $value;
				}
			}
		}
		return false;
	}

	/**
	 * オブジェクト名の取得をします。
	 * @return string オブジェクト名
	 */
	public function getObject ()
	{
		return self::$_object;
	}

	/**
	 * コントローラ名の取得をします。
	 * @return string コントローラ名
	 */
	public function getController ()
	{
		return self::$_controller;
	}

	/**
	 * アクション名の取得をします。
	 * @return string アクション名
	 */
	public function getAction ()
	{
		return self::$_action;
	}

	/**
	 * パッケージフラグの取得をします。
	 * @return string パッケージの使用の有無
	 */
	public function getPackage ()
	{
		return self::$_package;
	}

	/**
	 * ドメイン上での階層数の取得をします。
	 * @return numeric ドメイン上での階層数
	 */
	public function getFloer ()
	{
		return self::$_floer;
	}

	/**
	 * ドメイン上での階層数の取得をします。
	 * @return numeric ドメイン上での階層数
	 */
	public function getUrlparams ()
	{
		return self::$_url_params;
	}
}
