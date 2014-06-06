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

namespace feke\core;

/**
 * オートローダ
 *
 * クラスのオートロードをすべてこのクラスで引き受けます。
 *
 *
 *
 * @package    feke
 * @subpackage core
 */
class ClassLoader
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBaseStatic;

	/**
	 * 名前区間以外で読み込む場合のパス
	 * @var arrray
	 */
	private static $_dirs = array();

	/**
	 * ルートパス
	 * @var string
	 */
	private static $_root = null;


	/**
	 * 名前空間の先頭
	 * @var string
	 */
	private static $_object = null;

	/**
	 * アプリケーション名
	 * @var string
	 */
	private static $_app = null;

	/**
	 * パッケージフラグ
	 * @var string
	 */
	private static $_package = false;


	/**
	 * クラス、トレイトをautoload(オートロード)するメソッドです。
	 *
	 * spl_autoload_register()で登録されているため直接呼び出すことはありません。
	 *
	 * フレームワーク内のクラス読み込みをすべて担当します．
	 * -クラス名最後尾に'C'がある場合→コントローラとして読込み
	 * -クラス名最後尾に'M'がある場合→モジュールとして読込み
	 *
	 * クラス名に'_'アンダーバーがあった場合は，
	 * いかなる場合でもディデクトリ階層として処理
	 *
	 * 上記条件で読み込めなかった場合は，あらかじめセットされている'$dirs'から
	 * pathを生成し，オートロードを試みる
	 *
	 * クラス名は，スラッシュから最後尾までとなります．（'_'はクラス名として残されます）
	 *
	 * たぶん，PSR-0なクラスローダになっているはずです。
	 *
	 * **読み込めなかった場合の処理**
	 * クラスファイルが読み込めなかった場合は、致命的なエラーとして''\feke\error\FekeError''を投げます。
	 * この場合は、フレームワークがキャッチし、スクリプトを停止させます。
	 *
	 * ただし、他のオートローダが''spl_autoload_register()''によって登録されている場合は、違うオートローダーが読み込みを引き継ぐので、例外は投げられません。
	 *
	 *
	 * @param  string $classname 読み込むクラス名
	 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	 * @throws Error
	 * @example   new ClassName();
	 */
	public static function Load ($classname)
	{
		//プラグインの補完
		if (true === self::_loadPlugin($classname)) return;

		//ベンダー読み込み
		if (true === self::_loadFile(self::$_root.'/vendor_'.$classname, $classname)) return;

		//名前空間で読み込み
		if (true === self::_loadFile(self::$_root.'/'.$classname, $classname)) return;

		//命名規則で読み込み
		if (false !== ($class = self::_modName($classname, $classname))) {
			if (true === self::_loadFile(self::$_root.$class, $classname)) return;
		}

		//dirsからpathを生成して読込み込み
		if (self::$_dirs) {
			if (isset(self::$_dirs[$classname])) {
				$file = preg_replace("/\.php$/", '', self::$_root.self::$_dirs[$classname]);
				if (true === self::_loadFile($file, $classname)) return;
			}
		}

		//登録されているオートローダーの確認
		$list = spl_autoload_functions();
		$count = 0;
		foreach ($list as $key => $array) {
			if ((isset($array[0]) and $array[0] === 'feke\core\ClassLoader') or $count > 0) {
				$count++;
			}
		}

		//このオートローダの後に登録されているオートローダーがなければ例外を投げる
		if ($count === 1) {
			//読み込みに失敗した場合
			if (debug_level() >= 2) {
				\feke\core\Debug::setLoadClass("<span style='color:red;'>{$classname}</span>", $classname);
			}

			self::throwError ("クラス「{$classname}」が読み込めませんでした。", true);
		}
	}

	/**
	 * オートローダーが読み込むファイルパスを取得します。
	 *
	 * このメソッドでは、FekePHPのオートローダーが読み込むクラスを事前に確認することができます。
	 * 基本的には、デバック用です。
	 *
	 * @param string $classname
	 * @example  // Fekeクラスのフルパスが取得できます。
	 *           echo \feke\core\ClassLoader::classPlace ('feke');
	 *
	 *           //ルートパス/feke/core/Feke.php
	 */
	public static function classPlace ($classname)
	{

		if (strpos($classname, '\\') === 0) {
			$classname = preg_replace('/^\\\\/' ,'' ,$classname);
		}

		//プラグインの補完
		if (false !== ($file_name = self::_loadPlugin($classname, true))) {
			return realpath(self::_modPath ($file_name).'.php');
		}

		//ベンダー読み込み
		$file_name = realpath(self::$_root.'/vendor_'.$classname);
		if (true === self::_loadFile($file_name, $classname, true)) {
			return realpath(self::_modPath ($file_name).'.php');
		}

		//名前空間で読み込み
		$file_name = self::$_root.'/'.$classname;
		if (true === self::_loadFile($file_name, $classname, true)) {
			return realpath(self::_modPath ($file_name).'.php');
		}

		//命名規則で読み込み
		if (false !== ($class = self::_modName($classname, $classname))) {
			$file_name = elf::$_root.$class;
			if (true === self::_loadFile($file_name, $classname, true)) return realpath(self::_modPath ($file_name).'.php');
		}

		//dirsからpathを生成して読込み込み
		if (self::$_dirs) {
			if (isset(self::$_dirs[$classname])) {
				if (self::$_dirs[$classname]) {
					$file_name = self::$_root.self::$_dirs[$classname].'/'.$classname;
					if (true === self::_loadFile($file_name, $classname, true)) return realpath(self::_modPath ($file_name).'.php');
				}
			}
		}
		return false;
	}

	/**
	 * オートロードを行うパスの追加をします。
	 *
	 * @param string $name クラス名
	 * @param string $dir オートロードを行うパス
	 *
	 * @example \feke\core\ClassLoader::addDir ('Feke', '/feke/core');
	 *          \feke\core\ClassLoader::addDir ('Feke', '/feke/core');
	 *          \feke\core\ClassLoader::addDir ('Feke', '/feke/core');
	 */
	public static function addDir ($name ,$dir)
	{
		self::$_dirs[$name] = $dir;
	}

	/**
	 * クラスローダーが読み込む際にディテクトリの基底となるルートパスをセットします。
	 *
	 * @class hide
	 * @param string $name ルートパス
	 * @example  \feke\core\ClassLoader::root_path (FEKE_ROOT_PATH);
	 */
	public static function setRoot ($name)
	{
		self::$_root = $name;
	}

	/**
	 * オブジェクト名の設定をします。
	 *
	 * @class hide
	 * @param string $name オブジェクト名
	 * @example  \feke\core\ClassLoader::setObject ($object);
	 */
	public static function setObject ($name)
	{
		self::$_object = $name;
	}

	/**
	 * アプリケーション名の設定をします。
	 *
	 * @class hide
	 * @param string $name アプリケーション名
	 * @example  \feke\core\ClassLoader::setObject ($object);
	 */
	public static function setApp ($name)
	{
		self::$_app = $name;
	}

	/**
	 * パッケージが動作しているかを設定します。
	 *
	 * @class hide
	 * @param string $name パッケージフラグの追加
	 * @example  \feke\core\ClassLoader::setPackage ($name);
	 */
	public static function setPackage ($name)
	{
		self::$_package = $name;
	}

	/**
	 * プラグインの読み込み
	 *
	 * 名前空間の最初のパスに\plugin が指定されていた場合にここから読み込みます。
	 *
	 * @param  string $classname 読み込むクラス名
	 * @param  boolen $flag      ファイルの存在確認のみ
	 * @return boolen
	 */
	private static function _loadPlugin ($classname, $flag = null)
	{
		if (0 === strpos($classname, "plugin")) {
			//パーケージが読み込まれている場合のみ
			if (self::$_package) {
				$file_name = self::$_root.'/'.self::$_app.'/package/'.self::$_object.'/'.$classname;
				if (true === (self::_loadFile($file_name, $classname, $flag))) {
					if ($flag === true) return $file_name;
					return true;
				}
			}
			//アプリケーションディレクトリから
			$file_name = self::$_root.'/'.self::$_object.'/'.$classname;
			if (true === (self::_loadFile($file_name, $classname, $flag))) {
				if ($flag === true) return $file_name;
				return true;
			}
			// /vender/feke/pluginから
			$file_name = self::$_root.'/vender/feke/'.$classname;
			if (true === (self::_loadFile($file_name, $classname, $flag))) {
				if ($flag === true) return $file_name;
				return true;
			}
			// /feke/pluginから
			$file_name = self::$_root.'/feke/'.$classname;
			if (true === (self::_loadFile($file_name, $classname, $flag))) {
				if ($flag === true) return $file_name;
				return true;
			}
		}
		return false;
	}


	/**
	 * 命名規則
	 *
	 * Coreconfigで設定されている規則に引っかかった場合、オートロードを行います。
	 * 対象は、コントローラ，モジュール，ビュー，アクティブレコード、ベースクラスです。
	 *
	 * @param  string $classname 読み込むクラス名
	 * @return boolen
	 */
	private static function _modName ($classname)
	{
		//パーケージが読み込まれている場合のみ
		if (self::$_package) {
			$load_name = '/'.self::$_app."/package\\{$classname}";
		} else {
			$load_name = "\\{$classname}";
		}
		//contoller Class
		if (preg_match('/'.self::$_Config->controller.'$/',$classname)) {
			return $load_name;
		}
		//model Class
		elseif (preg_match('/'.self::$_Config->model.'$/',$classname)) {
			return $load_name;
		}
		//ActiveRecord Class
		elseif (preg_match('/'.self::$_Config->ar.'$/',$classname)) {
			return $load_name;
		}
		//view Class
		elseif (preg_match('/'.self::$_Config->view.'$/',$classname)) {
			return $load_name;
		}
		//base Class
		elseif (preg_match('/'.self::$_Config->base.'$/',$classname)) {
			return $load_name;
		}
		return false;
	}

	/**
	 * パスの最適化
	 *
	 * バックスラッシュとアンダーバーをスラッシュヘ変換
	 *
	 * @param  string       $path
	 * @return string|false
	 */
	private static function _modPath ($path)
	{
		$path = preg_replace('/[\\\\_]/','/',$path);
		return $path;
	}

	/**
	 * ファイルの読み込みに挑戦
	 *
	 * @param  string $filename 読み込むファイル名
	 * @param  string $classname 読み込むクラス名
	 * @param  boolen $flag      ファイルの存在確認のみ
	 * @return boolen
	 */
	private static function _loadFile($filename, $classname, $flag = null)
	{
		$filename = self::_modPath($filename).'.php';
		//echo "<p style='1color:white'>{$filename}</p>";
		if (is_readable($filename)) {
			if ($flag !== true) {
				if (debug_level() >= 2) \feke\core\Debug::setLoadClass($filename ,$classname);
				require_once $filename;
			}
			return true;
		}
		return false;
	}

}
