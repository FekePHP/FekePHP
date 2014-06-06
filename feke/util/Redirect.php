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

namespace feke\util;

/**
 * リダイレクト実行クラスです。
 *
 * **プラグインの読み込み**
 * プラグインとして読み込む場合は、インポートが必要です。
 * ※ControllerBaseに読み込まれています。
 * {{{php|
 * use \plugin\Redirect;
 * }}}
 *
 * @package    feke
 * @subpackage util
 * @plugin plugin\Redirect
 *
 */
class Redirect
{
	/**
	 * pluginの読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * URL読み込み用
	 */
	use \plugin\Url;

	/**
	 * ルータ用
	 */
	use \plugin\Input;

	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public function __construct()
	{
	}

	/**
	 * ***パラメータを指定しなかった場合***
	 *     現在のページをリロードします．
	 *
	 * ***パラメータに文字列を指定した場合***
	 *     動作中のリクエストと同じコントローラ内の指定アクション名にリダイレクトします．
	 *
	 * ***パラメータに配列を指定した場合***
	 *     ''controller''の指定がある場合は，コントローラ名（未指定の場合は動作中のコントローラ名が指定されます）
	 *     ''Action''が指定されている場合は，アクション名（未指定の場合は'Action'が指定されます）
	 *     ''key''が指定されている場合は，URLパラメータを各自設定します．
	 *     ''https''がtrueの場合は、リダイレクト先URLをhttps://に書き変えます。
	 *     ''http''がtrueの場合は、リダイレクト先URLをhttp://に書き変えます。
	 *
	 * {{{php|
	 * $que = array(
	 *     'controller' => 'exasmple',
	 *     'action'     => 'actionName',
	 *     'key'        =>
	 *     array(
	 *         'ms'         => 'example message',
	 *         'page'       => '1',
	 *     )
	 * );
	 *
	 * //Redirectプラグインを読み込んでいる場合
	 * $this->redirect($que);
	 * //または
	 * $this->jump($que);
	 *
	 *
	 * //インスタンス作成
	 * $Redirect = \Feke::load('Redirect,'util');
	 * $Redirect->redirect($que);
	 * }}}
	 *
	 *
	 * @param mixed $que
	 */
    public function redirect ($que = null)
    {
    	$this->usePlugin(__CLASS__);

		$url = $this->Url->obj();
		$url = preg_replace('/\/$/', '', $url);
		$http_protcall = null;
		
    	//配列にしたがって
    	if (is_array($que)) {
	    	if (isset($que['controller'])) {
	    		$url .= "/{$que['controller']}";
	    	}else {
	    		$url .= "/{$this->Controller}";
	    	}

	    	if (isset($que['action'])) {
	    		$url .= "/{$que['action']}";
	    	} else {
	    		$url .= "/Index";
	    	}
	    	
	    	
    		if (isset($que['http']) and is_true($que['http'])) {
	    		$url = preg_replace ('/^https:/is','http:',$url);
	    	}
	    	
    		if (isset($que['https']) and is_true($que['https'])) {
	    		$url = preg_replace ('/^http:/is','https:',$url);
	    	}

	    	//パラメータ生成
	    	if (is_array($que['key'])) {
	    		foreach ($que['key'] as $key => $value) {
	    			if (is_value($key) and is_scalar($value)) $url .= "/{$key}_".urlencode($value);
	    			else {

	    			}
	    		}
	    	}
	    	$url .= "/";

	    } elseif (is_string($que)) {
	    	$url .= "/{$this->Controller}";
	    	if ($que != 'action')$url .= "/{$que}";
	    	$url .= "/";
	    }
	    //再読み込み
	    else {
	    	$url = $this->Url->now();
	    }

    	header("Location:{$url}");
		exit;
	}
}