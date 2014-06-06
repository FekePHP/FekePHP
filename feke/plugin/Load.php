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

namespace plugin;

/**
 * 読み込み関係のプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */

trait Load {
	private static $_Load;

	public $Load;
	/**
	 * クラスローダ
	 * @param  string|arra$name
	 * @return string|array
	 */
	public function load ($class, $type = null, $que = null)
	{
		return \Feke::load($class, $type, $que);
	}

	/**
	 * クラスローダ
	 * @param  string|arra$name
	 * @return string|array
	 */
	public function loadUtil ($class, $type = null, $que = null)
	{
		return \Feke::loadUtil($class);
	}

	/**
	 * オートロードのパスを追加
	 */
	public function addDir ($name, $path)
	{
		return \Feke::addDir ($name,$dir);
	}

	/**
	 * ファイルの読み込み
	 *
	 * @param unknown $file_name  読み込みたいファイルパス
	 * @param string  $object_fg  trueを指定した場合は、オブジェクト形式で読み込んだ内容を返します
	 */
	public function loadFile ($file_name, $object_fg = null)
	{
		return \Feke::loadFile ($file_name,$object_fg);
	}

	/**
	 * 設定ファイルの読み込み
	 *
	 * @param string $file_name  読み込みたい設定ファイルのパス
	 *                            拡張を指定しなかった場合は、ini/yaml/jsonのいずれかの形式で読み込みます。
	 * @param string  $object_fg  trueを指定した場合は、オブジェクト形式で読み込んだ内容を返します
	 */
	public function loadConfig ($file_name, $object_fg = null)
	{
		return \Feke::loadConfig ($file_name, $object_fg);
	}

	/**
	 * プラグインの読み込み
	 *
	 * @param unknown $file_name  読み込みたいファイルパス
	 * @param string  $object_fg  trueを指定した場合は、オブジェクト形式で読み込んだ内容を返します
	 */
	public function loadPlugin ($name)
	{
		return \Feke::loadPlugin ($name);
	}

	/**
	 * プラグインの読み込み
	 *
	 * @param string $file_name  読み込みたいファイルパス
	 * @param string  $object_fg  trueを指定した場合は、オブジェクト形式で読み込んだ内容を返します
	 */
	public function loadLibrary ($name, $path)
	{
		return \Feke::loadLibrary ($name, $path);
	}
	
	/**
	 * 2階層目以降のコントローラ呼び出しに使用します．
	 *
	 * @example \Feke::loadController();
	 */
	public static function loadController()
	{
		return \Feke::loadController();
	}

}