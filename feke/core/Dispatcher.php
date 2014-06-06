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

use \feke\config\CoreConfig;
/**
 * コントローラとビューのアクション振り分けと、設定クラスの読み込みを行います。
 *
 * Dispatcherは唯一、index.phpから呼び出されるクラスとなります。
 *
 * このクラスないでエラーが起こった場合は、500エラーとして処理されます。
 *
 * CoreConfig読み込み部より前でエラーが起こった場合の動作は想定されていません。
 *
 * 途中でcatchされなかった例外は、このクラスでcatchされます．
 *
 * @package     feke
 * @subpackage  core
 */

class Dispatcher
{
	/**
	 * 設定ファイルを保持
	 */
	protected static $_CONFIG;

	/**
	 * コアクラス、設定、コントローラの読み込みを行います。
	 *
	 * ユーザーが使用することはありません。
	 *
	 * @throws
	 */
	public function dispatch ()
    {
		try{
			//簡易ファイル読み込み関数
			$load_class = function ($file_name) {
				if (is_file(FEKE_ROOT_PATH.'/vender'.$file_name)) require_once (FEKE_ROOT_PATH.'/vender'.$file_name);
				elseif (is_file(FEKE_ROOT_PATH.$file_name)) require_once (FEKE_ROOT_PATH.$file_name);
				else throw new \Exception ($file_name. 'is not found.');
			};

			//エラーレベル
			//全部対象
			error_reporting(-1);

			//エラーハンドラーの最後の砦
			$load_class('/feke/error/ErrorBase.php');

			//エラーハンドラーを登録
			set_error_handler(array('\feke\error\ErrorBase','error_handler'));

			//例外クラス
			$load_class('/feke/error/Error.php');
			$load_class('/feke/error/FekeError.php');

			//グローバル関数の読み込み
			$load_class('/feke/core/Functions.php');

			//デバッククラス読み込み
			$load_class('/feke/core/Debug.php');

			//クラスベース読み込み
			$load_class('/feke/base/ClassBase.php');
			$load_class('/feke/base/ClassBaseStatic.php');

			//コンフィグ読み込み用クラス読み込み
			$load_class('/feke/core/ConfigLoader.php');
			$ConfigLoader = new \feke\core\ConfigLoader;

			//ルータ読み込み ＆ 実行
			$load_class('/feke/core/Rooter.php');
			$Rooter = new \feke\core\Rooter();
			$Rooter->rooting();

			//ルーターの結果をセット
			$object     = $Rooter->getObject();
			$controller = $Rooter->getController();
			$action     = strtolower($Rooter->getAction());
			$package	= $Rooter->getPackage();
			$floer		= $Rooter->getFloer();
			$url_params = $Rooter->getUrlparams();

			//FekePHPの設定を読み込み
			self::$_CONFIG = $ConfigLoader->load ('coreConfig');

			//環境別コンフィグの読みこみ
			//coreConfigと結合
			$new_Config = $ConfigLoader->environment(self::$_CONFIG->DOMAIN);
			foreach ($new_Config as $key => $value) self::$_CONFIG->{$key} = $value;

			//デバックレベルをセット
			\feke\core\Debug::setDebugLevel(self::$_CONFIG->DEBUG->level);

			//エラーをすべて非表示
			if (self::$_CONFIG->DEBUG->level === 0) {
				ini_set( 'display_errors', 0 );
			}

			//オートローダクラス読み込み
			$load_class('/feke/core/ClassLoader.php');
			//オートローダの登録
			spl_autoload_register(array('\Feke\core\ClassLoader', 'Load'));
			//ルートパスをセット
			\feke\core\ClassLoader::setRoot (FEKE_ROOT_PATH);
			//オブジェクト名をセット
			\feke\core\ClassLoader::setObject ($object);
			//パッケージフラグをセット
			\feke\core\ClassLoader::setPackage ($package);
			//アプリケーション名をセット
			\feke\core\ClassLoader::setApp (FEKE_APP_NAME);
			//設定をセット
			\feke\core\ClassLoader::setConfig (self::$_CONFIG->AUTO_LOADER);

			//Feke class
			$load_class('/feke/core/Feke.php');

			//Fekeクラスへルーチング結果を挿入
			\Feke::_('object', $object);
			\Feke::_('controller', $controller);
			\Feke::_('action', $action);
			\Feke::_('package', $package);
			\Feke::_('floer', $floer);
			\Feke::_('url_params', $url_params);
			\Feke::_('Config', self::$_CONFIG);

			//Fekeクラス内のインスタンス作成
			\Feke::getFekeInstance();

			//エラーログの動作はここから

			//コントローラ名
			$className  =  "\\{$object}\\controller\\".ucfirst(strtolower($controller)).self::$_CONFIG->AUTO_LOADER->controller;
			// クラスインスタンス生成
			try {
				$Instance = new $className;
			} catch (\feke\error\FekeError $e){
				throw new \feke\error\NotFound($e->getMessage());
			}
			//アクションを実行
			self::doAction ('controller', $Instance, $controller, $action);

			//ビュー名
			$className  =  "\\{$object}\\view\\".ucfirst(strtolower($controller)).self::$_CONFIG->AUTO_LOADER->action;
			if (is_file(FEKE_ROOT_PATH.$className.'.php')) {
				// クラスインスタンス生成
				$Instance = new $className;
				//アクションを実行
				self::doAction ('view', $Instance, $controller, $action);
			}

			//displayの実行
			if (self::_allowCall($Instance, 'display') === true) {
				$retrun_display = $Instance->display();
			}
		}
		//404 not foundのチャッチ
		catch (\feke\error\NotFound $e) {
			\feke\error\ErrorBase::NotFound($e);
		}
		//PDOの例外をチャッチ
		 catch (\PDOException $e) {
		 	\feke\error\ErrorBase::PDOException($e);
		}
		//その他全般
		catch (\feke\error\FekeError $e) {
			\feke\error\ErrorBase::Exception($e, '\feke\error\FekeError');
		}
		catch (\Error $e) {
			\feke\error\ErrorBase::Exception($e, '\Error');
		}

		//デバックバーの表示
		if ($retrun_display !== 'rest') \feke\core\Debug::debugBar();
    }

    /**
	 * アクション実行メソッド
	 *
	 * コントローラ、ビュー内の
	 * ・before
	 * ・アクション名POST
	 * ・アクション名GET
	 * ・アクション名Action
	 * ・※2 rooter
	 * ・※2 not_found
	 * ・after
	 *
	 * の順にアクションが呼び出されます。
	 *
	 *
	 * ※2 該当するアクションがなかった場合のみ呼び出されます。
	 *
	 * @param string $type       controller or view
	 * @param string $instance   インスタンス
	 * @param string $controller コントローラ名
	 * @param string $action     アクション名
     * @throws \feke\error\NotFound
     */
	public static function doAction ($type, $instance, $controller, $action)
    {
    	//スタートアクション
    	if (self::_allowCall($instance, 'before') === true) {
    		$instance->before();
    	}

    	//POSTがあったとき
    	if ($_SERVER["REQUEST_METHOD"]) {
    		$http_action = $action.ucwords($_SERVER["REQUEST_METHOD"]);
    		if (self::_allowCall($instance, $http_action) === true) {
    			$instance->{$http_action}();
    		}
    	}

    	// アクションメソッドを実行
    	$action = $action.self::$_CONFIG->AUTO_LOADER->action;
    	//アクションが存在するか確認
    	if (self::_allowCall($instance, $action) === true) {
    		$instance->$action();
    	} elseif ($type === 'controller') {
    		//ルーター
    		if (self::_allowCall($instance, 'rooter') === true) {
    			$instance->rooter();
    		} else {
	    		//Actionがないときの処理
	    		throw new \feke\error\NotFound ("Controller[{$controller}] Action[{$action}]は存在しません<br>");
    		}
    	}

    	//最終コントローラ
    	if (self::_allowCall($instance, 'after') === true) {
    		$instance->after();
    	}
    }


    /**
     * メゾット確認用
     *
     * メソッドが存在かつ、呼び出せる場合のみtrueを返します。
     *
     * @param string|instance $class_name
     * @param string $method_name
     */
    protected static function _allowCall ($controllerInstance, $method_name) {
    	if (method_exists($controllerInstance, $method_name)) {
    		if (is_callable(array($controllerInstance, $method_name))) {
    			return true;
    		}
    	}
    	return false;
    }
}
