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
 * ファイル読み込みを行うクラスです。
 *
 * ini,csv,xml,YAML,jsonファイルを，配列またはオブジェクトとして変換・読み込みます．
 *
 * なお、Fekeクラスがこのクラスのエイリアス静的メソッドを所持しているので、エラー内容を取得しない場合以外は必要ありません。
 * @package    Feke
 * @subpackage util
 *
 */
class Load extends filer\Filer
{
	/**
	 * クラスベース読み込み
	 * @load
	 */
	use \feke\base\ClassBase;

	/**
	 * ファイルのパス
	 * @var unknown
	 */
	protected $_file_path;

	/**
	 * ファイルの読み込みを行います。
	 *
	 * 読み込むディレクリの優先順位は、以下の順に指定されたファイル名を加えて読み込まれます。
	 * +パッケージディレクリ（/app/package/パケージ名/config）
	 * +アプリケーションディレクリ（/app/config）
	 * +fekePHPディレクリ(/feke/config)
	 *
	 * @param string $file_name 読み込みたい設定ファイル名
	 * @param string $load_type trueの場合はオブジェクト形式、指定がない場合は配列で読み込み
	 * @example //読み込み例(1)
	 *          $Load = \Feke::load ('Load','util');
	 *          $Load->config ('/config/util/session.ini', true);
	 *
	 *          //読み込み例(2)
	 *          \Feke::load_file ('/config/util/session.ini', true);
	 *
	 *          //読み込み例(3)
	 *          //プラグイン使用時
	 *          $this->Load->file ('/config/util/session.ini', true);
	 */
	public function file ($file_name, $mold = null)
	{
		if ($mold === true) $mold = 'object';
		else $mold = 'array';

		$this->_data = null;
		$this->_file_path = null;
		$this->_error_message = null;

		try {
			//ファイルの存在確認
			if (is_readable($file_name)) {
				$file_path = $file_name;
			} elseif (is_readable(FEKE_ROOT_PATH.$file_name)) {
				$file_path = FEKE_ROOT_PATH.$file_name;
			} elseif (is_readable(FEKE_ROOT_PATH.$file_name)) {
				$file_path = FEKE_ROOT_PATH.$file_name;
			} else {
				throw new \Exception('['.$file_name.']は読み込みできないファイルです．');
			}
			//拡張子の確認
			if ('' != ($extension = $this->_checkExtension($file_path))) {
				//差分の吸収
				if ($extension == 'yaml') $extension = 'yml';
				$method_name = "_{$extension}{$mold}";
				if (method_exists($this, $method_name)) {
					$this->_data = $this->{$method_name}($file_path);
				} else {
					return $this->throwError('拡張子['.$extension.'],形式['.$mold.']は読み込みをサーポートしていないファイル形式です．');
				}
			}
			//取得したデータを返す
			$this->_file_path = $file_path;
			return $this->_data;

		} catch (\Exception $e) {
			$this->_error_message = $e->getMessage();
			return false;
		}
	}


	/**
	 * Minetype txt読み込み
	 */
	protected function _txtArray ($file_path)
	{
		//ファイル名の拡張子取得
		$type = self::_getExtensio ($file_path);

		if ($type === 'ini') {
			return $this->_iniArray ($file_path);
		} elseif ($type === 'csv') {
			return $this->_csvArray ($file_path);
		} elseif ($type === 'json') {
			return $this->_jsonArray ($file_path);
		} elseif ($type === 'yml') {
			return $this->_ymlArray ($file_path);
		} elseif ($type === 'yaml') {
			return $this->_ymlArray ($file_path);
		}else {

		}
	}

	/**
	 * Minetype txt読み込み
	 */
	protected function _txtObject ($file_path)
	{
		//ファイル名の拡張子取得
		$type = self::_getExtensio ($file_path);

		if ($type === 'ini') {
			return $this->_iniObject ($file_path);
		} elseif ($type === 'csv') {
			return $this->_csvObject ($file_path);
		} elseif ($type === 'json') {
			return $this->_jsonObject ($file_path);
		} elseif ($type === 'yml') {
			return $this->_ymlObject ($file_path);
		} elseif ($type === 'yaml') {
			return $this->_ymlObject ($file_path);
		}else {

		}
	}

	/**
	 * ini読み込み（配列）
	 */
	protected function _iniArray ($file_path)
	{
		$this->_data = parse_ini_file($file_path, true);
		return $this->_data;
	}

	/**
	 * ini読み込み(オブジェクト)
	 */
	protected function _iniObject ($file_path)
	{
		$this->_data = toObject(parse_ini_file($file_path, true));
		return $this->_data;
	}

	/**
	 * csv読み込み（配列）
	 *
	 * @link http://jp.php.net/manual/ja/function.fopen.php
	 */
	protected function _csvArray ($file_path)
	{
		$handle = fopen($file_path, 'r');
		$this->_data = fgetcsv($handle, 0);
		fclose($handle);
		return $this->_data;
	}

	/**
	 * csv読み込み(オブジェクト)
	 *
	 * @link http://jp.php.net/manual/ja/function.fopen.php
	 */
	protected function _csvObject ($file_path)
	{
		$handle = fopen($file_path, 'r');
		$this->_data = toObject(fgetcsv($handle, 0));
		fclose($handle);
		return $this->_data;
	}

	/**
	 * xml読み込み(オブジェクト)
	 */
	protected function _xmlObject ($file_path)
	{
		$this->_data =  simplexml_loadFile($file_path);
		return $this->_data;
	}

	/**
	 * xml読み込み（配列）
	 */
	protected function _xmlArray ($file_path)
	{
		$this->_data = toArray(simplexml_loadFile($file_path));
		return $this->_data;
	}

	/**
	 * YAML読み込み(オブジェクト)
	 *
	 * 外部ライブラリ使用
	 * @link https://github.com/mustangostang/spyc/
	 */
	protected function _ymlObject ($file_path)
	{
		\feke\core\ClassLoader::registDir('spyc','/feke/library/spyc');
		new \Spyc;

		$this->_data = toObject(\Spyc::YAMLLoad($file_path));

		return $this->_data;
	}

	/**
	 * YAML読み込み（配列）
	 *
	 */
	protected function _ymlArray ($file_path)
	{
		\feke\core\ClassLoader::registDir('spyc','/feke/library/spyc');
		new \Spyc;

		$this->_data = \Spyc::YAMLLoad ($file_path);
		return $this->_data;
	}

	/**
	 * json読み込み(オブジェクト)
	 *
	 * @link http://php.net/manual/ja/function.json-decode.php
	 */
	protected function _jsonObject ($file_path)
	{
		$text = file_get_contents($file_path);
		$this->_data = json_decode($text);
		if (false !== ($error_msg = self::_jsonError ())) {
			return $this->throwError ('JSONファイル読み込みエラー:'.$error_msg);
		}
		return $this->_data;
	}

	/**
	 * json読み込み（配列）
	 */
	protected function _jsonArray ($file_path)
	{
		$text = file_get_contents($file_path);
		$this->_data = json_decode($text, true);
		if (false !== ($error_msg = self::_jsonError ())) {
			return $this->throwError ('JSONファイル読み込みエラー:'.$error_msg);
		}
		return $this->_data;
	}
}