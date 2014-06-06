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
use feke\config\CoreConfig;

/**
 * Session クラスのプラグイン
 *
 * @package    Feke
 * @subpackage plugin
 */
trait Session {

	/**
	 *継承時に使用するメソッド
	 * @var object
	 */
	protected $Session;

	/**
	 * Session classのインスタンス
	 * @var object
	 */
	static $_SessionInstance;

	/**
	 * セッションクラスのインスタンス作成
	 *
	 */
	public function get_SessionInstance ()
	{
		if (!\plugin\Session::$_SessionInstance) {
			$config = \Feke::loadConfig('/util/session', true)->SESSION;

			//セッションの管理
			//セッションをデータベースで管理する場合
			if ($config->USE_DATABASE) {
				$handler = \Feke::load ('session\SessionDB', 'util');

				$DB = \Feke::load('QueryBuilder','util');
				$DB->connect();

				$handler->setConfig ($config, $DB);
				ini_set('session.save_handler', 'user');
				session_set_save_handler(
				array($handler, 'open'),
				array($handler, 'close'),
				array($handler, 'read'),
				array($handler, 'write'),
				array($handler, 'destroy'),
				array($handler, 'gc')
				);
			}
			//セッションのマスククラス
			\plugin\Session::$_SessionInstance = \Feke::load ('Session', 'util');

			//ini
			register_shutdown_function('session_write_close');

			ini_set('session.gc_maxlifetime', $config->EXPIRATION);
			ini_set('session.gc_probability', 1);
			ini_set('session.gc_divisor', $config->UPDATE_PROBABILITY);

			\plugin\Session::$_SessionInstance->open();
		}

		$this->Session = \plugin\Session::$_SessionInstance;

	}
}