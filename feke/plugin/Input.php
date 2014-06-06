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
 * Input用のプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */

trait Input {
	/**
	 * 継承時に使用するプロパティ
	 * @var object
	 */
	protected $Input;

	/**
	 * Get class のインスタンス
	 * @var object
	 */
	public static $_InputInstance;

	/**
	 * アプリケーション名
	 * @var string
	 */
	protected $Object;

	/**
	 * コントローラ名
	 * @var string
	 */
	protected $Controller;

	/**
	 * アクション名
	 * @var string
	 */
	protected $Action;

	/**
	 * Cookei class のインスタンス取得
	 *
	 * @class hide
	 */
	public function get_InputInstance ()
	{
		if (!\plugin\Input::$_InputInstance) {
			\plugin\Input::$_InputInstance = \Feke::loadUtil ('Input');
		}
		$this->Input = \plugin\Input::$_InputInstance;

		$this->Controller = \Feke::_('controller');
		$this->Action = \Feke::_('action');
		$this->Object = \Feke::_('object');
	}
}