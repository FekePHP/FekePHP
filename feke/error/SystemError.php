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
class SystemError extends \Exception
{
	/**
	 * 例外を再定義し、メッセージをオプションではなくする
	 * @param unknown $message
	 * @param number $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		// 全てを正しく確実に代入する
		parent::__construct($message, $code, $previous);
	}

	/**
	 * オブジェクトの文字列表現を独自に定義する
	 * @see Exception::__toString()
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

	/**
	 * デバック用
	 */
	public function getError ()
	{
		$error_ms = 'Error on line '.$this->getLine().' in '.$this->getFile().' '.$this->getMessage();

		return $error_ms;
	}
}