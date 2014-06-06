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
 * QueryBuilder クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */

trait QueryBuilder {
	/**
	 * 継承時に使用するプロパティ
	 * @var unknown
	 */
	protected $QueryBuilder;

	/**
	 * protected $QueryBuilder のエイリアス
	 * @var unknown
	 */
	protected $QB;

	/**
	 * Bilder class のインスタンス
	 * @var unknown
	 */
	public static $_QueryBuilderInstance;

	/**
	 * アクティブレコードのインスタンス取得
	 * @param  string|arra$name
	 * @return string|array
	 */
	public function get_QueryBuilderInstance ()
	{
		if (!\plugin\QueryBuilder::$_QueryBuilderInstance) {
			\plugin\QueryBuilder::$_QueryBuilderInstance = \Feke::load ('QueryBuilder', 'util');
			\plugin\QueryBuilder::$_QueryBuilderInstance->connect();
		}
		$this->QueryBuilder = \plugin\QueryBuilder::$_QueryBuilderInstance;
		$this->QB = $this->QueryBuilder;
	}

}