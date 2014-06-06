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

namespace feke\error;

/**
 * 404エラークラス
 *
 * ただ，404エラー処理するだけのクラスです．
 * Disbapatcherで標準で呼び出され，主にルーティング周りとオートローダの例外をキャッチします．
 *
 * @package    Feke
 * @subpackage error
 *
 */
class NotFound extends \Exception
{
	/**
	 * エラー内容格納配列
	 */
	protected $_error_data;

	/**
	 * 例外を再定義し、メッセージをオプションではなくする
	 * @param unknown $message
	 * @param number $code
	 * @param Exception $previous
	 */
	public function __construct($error_data = null) {
		// 全てを正しく確実に代入する
		parent::__construct(null);

		$this->_error_data = $error_data;

		//メッセージの挿入
		$this->message = $error_data;
	}

	/**
	 * エラーデータを取得する
	 *
	 * @return エラーのデータ
	 */
	public function get()
	{
		return $this->_error_data;
	}

	/**
	 * エラーメッセージを取得します。
	 *
	 * @return エラーメセージ
	 */
	public function msg()
	{
		if (isset($this->_error_data[0]->message)) {
			return $this->_error_data[0]->message;
		}
	}

	/**
	 * エラー内容の詳細を表示する。
	 */
	public function show ()
	{
		\feke\error\ErrorBase::show_debug($this->_error_data);
	}
}