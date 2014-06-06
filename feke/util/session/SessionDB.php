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

namespace feke\util\session;

use feke\config\coreConfig as C;

/**
 * Session on database class
 *
 * Sessionのセータをデータベースで管理するクラスです．
 *
 * データベースのコネクションは，Fekeclassが所持しているものを利用します．
 * ※別のコネクションを利用したい場合は，クラスのインスタンス作成時に指定してください．
 * (/feke/core/feke.php内を編集)
 *
 * セッションの操作自体は，通常のphp提供の機能か，
 * Fekeのセッションマスククラス(/feke/core/Session.php)
 * を利用して操作します．
 *
 *
 * なお使用したい場合は，
 *     /app/config/config.php
 *     SESSTION_USE_DATABASE = true
 * を設定してください．
 *
 *
 * ******************************************************
 *
 * セッションの管理二使用するテーブルは，以下のSQLにて作成してください．
 * （テーブル名はconfig.phpに合わせて指定）
 *
 * CREATE TABLE IF NOT EXISTS `feke_sessions` (
 *     session_id varchar(40) DEFAULT '0' NOT NULL,
 *     ip_address varchar(16) DEFAULT '0' NOT NULL,
 *     user_agent varchar(120) NOT NULL,
 *     update int(10) unsigned DEFAULT 0 NOT NULL,
 *     data text NOT NULL,
 *     PRIMARY KEY (session_id),
 *     KEY `update_idx` (`update`)
 * );
 *
 *
 * @package    Feke
 * @subpackage util
 */

class SessionDB
{
	/**
	 * データベースのコネクション
	 * @var object
	 */
	private $_db;

	/**
	 * コンフィグの保存
	 */
	private $_Config;

	/**
	 * 設定値のセット
	 * @param string $db データベースのコネクション
	 */
	public function setConfig ($config,$db)
	{
		$this->_Config = $config;
		$this->_db = $db;
		$this->_db->setMainTable ($this->_Config->TABLE_MAME);
	}


	/**
	 * セッションの開始
	 * @param string $name
	 */
	public function open ($name = null)
	{
		return true;
	}

	/**
	 * セッションを終了する
	 */
	public function close ()
	{
		return true;
	}

	/**
	 * セッションを取得
	 *
	 * @param string $name
	 * @return boolean|unknown
	 */
	public function read ($session_id)
	{
		$data = $this->_db->select('data')->findBy('session_id',$session_id);
		if (isset($data->data)) return $data->data;
	}

	/**
	 * 指定したセッションをセット
	 * @param string       名前
	 * @param string|array 書き込む値
	 */
	public function write ($session_id, $data)
	{
		$data = $this->_db->values(['session_id' => $session_id, 'data' => $data, 'update' => date('Y-m-d H:i:s')])->replace();
		return true;
	}


	/**
	 * セッションの破棄
	 *
	 * 参考
	 * ・http://www.php.net/manual/ja/function.session-destroy.php
	 *
	 * @parm string session_id
	 */
	public function destroy ($session_id)
	{
		$data = $this->_db->where('session_id',$session_id)->delete();
		return true;
	}

	/**
	 * セッションの時間管理用
	 */
	public function gc ($maxlifetime)
	{
		$time = date("Y-m-d H:i:s",strtotime("-$maxlifetime second" ,strtotime('now')));

		$data = $this->_db->where('update', '<', $time)->delete();
		return true;
	}


}
