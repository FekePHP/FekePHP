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

/**
 * FekePHPの基幹クラスです。
 *
 *
 * @package    Feke
 * @subpackage Core
 */
class Feke
{
	/**
	 * オブジェクト名
	 * @var string
	 */
	private static $_object;

	/**
	 * コントローラ名
	 * @var string
	 */
	private static $_controller;

	/**
	 * アクション名
	 * @var string
	 */
	private static $_action;

	/**
	 * パッケージフラグ
	 * @var string
	 */
	private static $_package = false;

	/**
	 * 階層
	 * @var array
	 */
	private static $_floer;

	/**
	 * コントローラの階層
	 * @var numeric
	 */
	private static $_floer_count = 0;

	/**
	 * URLパラメータ
	 * @var array
	 */
	private static $_url_params = array();

	/**
	 * 設定
	 * @var array
	 */
	private static $_Config;

	/**
	 * コントローラの階層
	 * @var numeric
	 */
	private static $_Instance = array();

	public static function l()
	{

	}

	/**
	 * クラス読み込み用メソッドです。
	 *
	 * FekePHPの命名規則に沿ったクラス名を付けられている場合は、Contrroller,Model,View,Activerecrdeクラスの名前空間が補完されます.
	 * また、第二因数に、util,helper.pluginを指定することで、これらの名前空間を補完することもできます。
	 *
	 * @param string  $class 読み込みたいクラス名
	 * @param string  $type  読み込みたいクラスのカテゴリ
	 * @return object        読み込んだクラスのインスタンス
	 * @example \Feke::load('Auth','util');
	 */
	public static function load ($class, $type = null, $parm = null)
	{
		$class = self::_moldClassname($class, $type);
		return new $class($parm);
	}

	/**
	 * utilityクラス読み込み用メソッドです。
	 *
	 * @param string  $class 読み込みたいクラス名
	 * @return object        読み込んだクラスのインスタンス
	 * @example \Feke::loadUtil ('Auth');
	 */
	public static function loadUtil ($class , $parm = null)
	{
		$class = self::_moldClassname($class, 'util');
		return new $class($parm);
	}

	/**
	 * オートローダへパスを追加
	 *
	 * @param $name クラス名
	 * @param $path パス
	 * @example \Feke::addDir ('smarty','/feke/library/smarty');
	 */
	public static function addDir ($name, $path)
	{
		\feke\core\ClassLoader::addDir ($name, $path);
	}


	/**
	 * 設定ファイルの読み込みを行うメソッドです。
	 * 拡張子は、「ini,yaml,json」の順に、
	 * ディレクトリは、（プラグイン）→アプリケーション→Feke標準の順に
	 * 設定ファイルを検索し、読み込みます。
	 *
	 * @param string $filename  読み込みたい設定ファイルの名前
	 * @param boolen $object_fg trueの場合はオブジェクト,falseまたは指定しない場合は、配列で設定ファイルを読み込みます。
	 * @return object|array
	 * @throws \feke\error\FekeError
	 * @example \Feke::loadConfig ('/core/session', true);
	 */
	public static function loadConfig ($filename, $object_fg = null)
	{
		return \feke\core\ConfigLoader::load ($filename);
	}

	/**
	 * ファイル読み込みメソッドです。
	 *
	 * @param string $filename  読み込みたいファイルの名前
	 * @param boolen $object_fg 読み込んだデータの取得形式の設定
	 * @throws \Error
	 * @return object|array|string
	 * @example \Feke::loadFile ('/core/session.ini', true);
	 */
	public static function loadFile ($filename, $object_fg = null)
	{
		return self::$_Instance['Load']->file($filename, $object_fg);
	}

	/**
	 * プラグインの読み込みを行います。
	 *
	 * @param  string $plugin_name  読み込みたいプラグイン名
	 * @throw
	 * @return object        読み込んだクラスのインスタンス
	 * @example \Feke::loadPlugin ('Validation');
	 *
	 */
	public static function loadPlugin ($plugin_name)
	{
		$class = "\\plugin\\".$plugin_name;
		return new $class;
	}

	/**
	 * ライブラリディレクトリから読み込みます。
	 *
	 * @param  string $library_name  読み込みたいクラス名
	 * @param  string $path          /feke/library以降のパス
	 * @return object                読み込んだクラスのインスタンス
	 * @example \Feke::loadPlugin ('spyc','/spyc');
	 */
	public static function loadLibrary ($library_name, $path)
	{
		self::addDir ($library_name, '/feke/library'.$path);
		return new $library_name;
	}

	/**
	 * Fekeクラス内の静的プロパティを取得,書き換えをします
	 *
	 * @param string $name  Feke classの内のプロパティ名
	 * @param string $value 書き換えたい場合の値
	 * @example //コントローラ名の取得
	 *     \Feke::_ ('controller');
	 */
	public static function _ ($name, $value = null)
	{
		$name = "_{$name}";
		if ($value) {
			self::${$name} = $value;
			return;
		} else {
			return self::${$name};
		}
	}

	/**
	 * 設定を取得します
	 *
	 * @param string $name  Feke classの内のプロパティ名
	 * @param string $value 書き換えたい場合の値
	 * @example //コントローラ名の取得
	 *     \Feke::_ ('controller');
	 */
	public static function config ($name, $value = null)
	{
		if (property_exists(self::$_Config, $name)) {
			return self::$_Config->$name;
		}
		return null;
	}

	/**
	 * 2階層目以降のコントローラ呼び出しに使用します．
	 * @example \Feke::loadController();
	 */
	public static function loadController()
	{
		self::$_floer_count++;

		$Rooter = new \feke\core\rooter(self::$_floer_count);

		$controller = $Rooter->getController();
		$action     = strtolower($Rooter->getAction());

		if (self::$_action == 'Action') self::$_action = 'Index';

		self::$_controller = strtolower(self::$_controller).'\\'.ucfirst(strtolower(self::$_action));
		self::$_action = $action;

		//コントローラ名
		$className  =  "\\".self::$_object."\\controller\\".ucfirst(strtolower(self::$_controller)).self::$_Config->AUTO_LOADER->controller;

		// クラスインスタンス生成
		$Instance = new $className;

		//アクションを実行
		\feke\core\Dispatcher::doAction ('controller', $Instance, self::$_controller, self::$_action);

		//ビュー名
		$className  =  "\\".self::$_object."\\view\\".ucfirst(strtolower(self::$_controller)).self::$_Config->AUTO_LOADER->view;
		if (is_file(FEKE_ROOT_PATH.$className.'.php')) {

			// クラスインスタンス生成
			$Instance = new $className;
			//アクションを実行
			self::doAction ('view', $Instance, self::$_controller, $action);
		}

		/*
		self::$_floer_count++;
		$Rooter = new \feke\core\rooter(self::$_floer_count);

		$object     = $Rooter->getObject();
		$controller = $Rooter->getController();
		$action     = strtolower($Rooter->getAction());

		if (self::$_action == 'Action') self::$_action = 'Index';

		self::$_controller = strtolower(self::$_controller).'\\'.ucfirst(strtolower(self::$_action));
		self::$_action = $action;

		$className = '\\'.self::$_object.'\\controller\\'.self::$_controller.CoreConfig::CONTROLLER_PULS;

		// クラスインスタンス生成
		$controllerInstance = new $className;

		if (self::_allowCall($controllerInstance, 'uses_all') === true) {
			$controllerInstance->uses_all();
		}

		//スタートアクション
		if (self::_allowCall($controllerInstance, 'before') === true) {
			$controllerInstance->before();
		}

		//POSTがあったとき
		if (empty($_POST)) {
			$post_action = $action.'Post';
			if (self::_allowCall($controllerInstance, $post_action) === true) {
				$controllerInstance->$post_action();
			}
			//GETがあったとき
		} elseif (!empty($_GET)) {
			$getAction = $action.'Get';
			if (self::_allowCall($controllerInstance, $getAction) === true) {
				$controllerInstance->$getAction();
			}
		}


		// アクションメソッドを実行
		$action = $action.CoreConfig::ACTION_PULS;
		//アクションが存在するか確認
		if (self::_allowCall($controllerInstance, $action) === true) {
			$controllerInstance->$action();
		} else {
			throw new \feke\error\NotFound ("Controller[".self::$_controller.CoreConfig::CONTROLLER_PULS."] Action[{$action}]は存在しません<br>");
		}

		//最終コントローラ
		if (self::_allowCall($controllerInstance, 'after') === true) {
			$controllerInstance->after();
		}*/

	}

	/**
	 * Fekeクラス内で使用するインスタンスの作成をします。
	 *
	 * @class hide
	 */
	public static function getFekeInstance ()
	{
		//Loadclass読み込み
		self::$_Instance['Load'] = \Feke::load ('Load', 'util');
		self::$_Instance['ConfigLoader'] = new \feke\core\ConfigLoader;
	}

	/**
     * メゾット確認用
     * @param unknown $class_name
     * @param unknown $method_name
     */
    private static function _allowCall ($controllerInstance, $method_name) {
    	if (method_exists($controllerInstance,$method_name)) {
    		if (is_callable(array($controllerInstance, $method_name))) {
    			return true;
    		}
    	}
    	return false;
    }

    /**
     * メゾット確認用
     * @param unknown $class_name
     * @param unknown $method_name
     */
    private static function _moldClassname ($class, $type) {

    	//強制的にクラス名の先頭に'\'をつけます
    	if (!preg_match("/^[\\\\]/u",$class)) $class = "\\".$class;

    	if ($type == 'util') {
    		$class = '\\feke\\util'.$class;
    	}
    	elseif ($type == 'library') {
    		$class = 'library'.$class;
    	}
    	elseif ($type == 'plugin') {
    		$class = '\\plugin'.$class;
    	}


    	//補完
    	//model
    	if (preg_match("/".self::$_Config->AUTO_LOADER->model."$/",$class)) {
    		$class = '\\'.self::$_object.'\\model'.$class;
    	}
    	//ActiveRecord
    	elseif (preg_match("/".self::$_Config->AUTO_LOADER->ar."$/",$class)) {
    		$class = '\\'.self::$_object.'\\activerecord'.$class;
    	}
    	//View
    	elseif (preg_match("/".self::$_Config->AUTO_LOADER->view."$/",$class)) {
    		$class = '\\'.self::$_object.'\\view'.$class;
    	}
    	return $class;
    }
}
