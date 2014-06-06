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

namespace plugin;

/**
 * Thema クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */
trait Thema {

	/**
	 *
	 */
	public static $tmp = array();

	/**
	 * 各オブジェクトのテンプレートを管理
	 *
	 * $nameはテーマ名，$objectはオブジェクト名をさします．
	 *
	 * @param string $name テーマ名
	 */
	public function tmp_path($name = null) {
		$object = \Feke::_('object');

		if ($object) $object = '/'.$object;
		if ($name and ThemaConfig::THEMA_USE === true) {
			define('TMP_PATH', FEKE_ROOT_PATH. "/{$object}/template/{$name}");
		} else {
			define('TMP_PATH', FEKE_ROOT_PATH. "/{$object}/template");
		}
	}

	/**
	 * テんプレート内のデフォルトmain.tplの指定
	 *
	 * テンプレートのメインコンテンツを差し込みます
	 *
	 * 使用するテンプレートは，
	 * /app/template/object名/Controller名/Controler名_Action名.tpl
	 * となります．
	 */
	public function main_tmp_path()
	{
		//デフォルトの表示テンプレート読み込み
		$controller = \Feke::_('controller');
		$action = \Feke::_('action');
		if ($controller) {
			$path = "/".$controller;
		}
		//コントローラー名にファイルパスがあったら削除
		//2階層以降のコントローラの場合のみパスが付属します
		$controller = preg_replace("/^(.*)[\\\\]/", '', $controller);

		$path .= "/".strtolower($action).".tpl";

		$this->set('main',$path);
	}

	/**
	 * メインテンプレートの指定
	 *
	 * デフォルトのテンプレート以外を指定したい場合は，
	 * このメソッドを使用してください．
	 */
	public function set_main_tmp_path($path)
	{
		$this->set('main',$path);
	}



	/**
	 * *テンプレートエンジンの変数にセット
	 *
	 * set_plece('挿入先')→指定した設置場所に変数をセット
	 *
	 * @param string $plece
	 */
	public function set_plece($plece)
	{
		if (isset(\plugin\Thema::$tmp[$plece])) $this->set($plece, \plugin\Thema::$tmp[$plece]);
	}

	/**
	 * HTMLにスクリプト埋め込み
	 *
	 * 指定したスクリプトをHTMLのヘッダなどに埋め込み
	 *
	 * 使用方法
	 * ・デフォルトでは，テンプレート内の{$header}へ埋め込まれます．
	 * ・set_scripts ('埋め込みたいスクリプト名')
	 * ・set_scripts ( array ( 'name' => 'スクリプト名' , 'plece' => '設置場所 header/footer')
	 *
	 * @param string $script
	 */
	public function set_scripts ($script)
	{
		//配列・変数振り分け
		if (is_array($script)){
			//スクリプト名
			$script_name = $script['name'];
			//挿入場所
			$add_plece = $script['plece'];
		} else {
			//スクリプト名
			$script_name = $script;
		}
		//挿入場所調整
		if (!$add_plece) $add_plece = header;

		$urlclass = Feke::_('url');
		$url = $urlclass->obj();
		//郵便番号→住所自動入力スクリプト
		//http://code.google.com/p/ajaxzip3/
		//使用方法は上記URL参照
		if ($script_name == 'ajaxzip3') {
			\plugin\Thema::$tmp[$add_plece] .= "\n<script src='http://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/ajaxzip3.js' charset='UTF-8'></script>";
		}

		if ($script_name == 'sitemapstyler') {
			\plugin\Thema::$tmp[$add_plece] .= "\n<script src='{$url}/js/sitemapstyler.js' charset='UTF-8'></script>";
		}

		if ($script_name == 'lightbox_me') {
			\plugin\Thema::$tmp[$add_plece] .= "\n<script src='{$url}/js/jquery.lightbox_me.js' type='text/javascript' ></script>";
		}

		//テンプレートヘ挿入
		$this->set_plece($add_plece);
	}

	/**
	 * 会員用テンプレート差し替えメソッド
	 *
	 * 登録ユーザ向けのテンプレートパーツを表示します．
	 *
	 * @param unknown $flag
	 */
	public function set_login ($flag)
	{
		if ($flag == 1) {
			\plugin\Thema::$tmp['navi'] = '/tmp/navi_login.tpl';
		}

		if ($flag == -1) {
			\plugin\Thema::$tmp['navi'] = '/tmp/navi.tpl';
		}
		//テンプレートヘ挿入
		$this->set_plece('navi');
	}
}