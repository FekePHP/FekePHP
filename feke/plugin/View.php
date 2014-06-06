<?php
/**
 * Part of the Feke framework.
 *
 * @package Feke
 * @author Shohei Miyazawa
 * @since PHP 5.3
 */

namespace plugin;

/**
 * アクティブレコードのインスタンス取得
 * @param  string|arra$name
 * @return string|array
 */

trait View
{
	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $View;

	/**
	 * Read class のインスタンス
	 * @var object
	 */
	public static $_ViewInstance;

	/**
	 * メインテンプレートのパス
	 */
	protected $main_template;

	/**
	 * REST出力の形式
	 */
	protected $rest_type;

	/**
	 * アクティブレコードのインスタンス取得
	 * @param  string|arra$name
	 * @return string|array
	 */
	public function get_ViewInstance ()
	{
		//メインテンプレートパス
		$this->main_template = '/'.\Feke::_('controller').'/'.\Feke::_('action');

		if ( ! \plugin\view::$_ViewInstance) {
			$config = \Feke::loadConfig('/plugin/view',true)->CONFIG;

			//使用するのインスタンス
			if ($config->class_name == 'FekeParser') {

				//FekeParserを使用する場合
				\plugin\view::$_ViewInstance = \Feke::load ($config->class_name, 'util');

				//テンプレートのディレクトリ
				\plugin\view::$_ViewInstance->rootDir(app_path().'/template');

				//コンパイルファイルのディレクトリ
				\plugin\view::$_ViewInstance->compileDir (tmp_path().'/parser_compile');

				//キャッシュファイルのディレクトリ
				\plugin\view::$_ViewInstance->cacheDir (tmp_path().'/parser_page');

				//ベーステンプレート
				\plugin\view::$_ViewInstance->baseTemplate ('/base/default.tpl');

				//メインテンプレート

				\plugin\view::$_ViewInstance->mainTemplate ($this->main_template.'.tpl');

				//\plugin\view::$_ViewInstance->forceCompile(true);

			} elseif ($config->class_name) {
				if ($config->class_name) \Feke::addDir ($config->class_name, $config->class_path);
				\plugin\view::$_ViewInstance = \Feke::load ($config->class_name);
			}
		}
		$this->View = \plugin\view::$_ViewInstance;
	}

	/**
	 *
	 */
	public function set($a,$b)
	{
		$this->View->set($a,$b);
	}

	/**
	 * ディスプレイメソッド
	 */
	public function display()
	{
		$this->View->display();
	}
}