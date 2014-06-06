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

namespace feke\util\querybuilder;

/**
 * トランザクション関連のトレイトです。
 *
 * トランザクションは、クエリが1つでも正常に発行されなかった場合はすべてロールバックして、取り消すことができます。
 * クエリを複数発行しすべてのクエリが成功した場合のみデータベースを更新したいときなどに大変有効です。
 *
 * >>MySQLなど、一部のデータベースでは、DROP TABLE や CREATE TABLEのようなクエリを発行すると、自動でコミットされます。
 *
 * >>トランザクションに対応していないデータベースに対してトランザクションを実行しようとした場合は例外がスローされますが、MySQLのMyISAMではエンジンが対応していないため、トランザクションは使用できませんが、例外はスローされません。
 * **ロールバックについて**
 * クエリに不正があった段階、又はスクリプトが停止した段階でフレームワークが自動でロールバックを行います。
 * したがって、ユーザーは明示的にロールバックを行いたい場合を除き、一応はロールバックをしなくても問題はありません。
 *
 * **使用例1**
 * 明示的にロールバックを行う場合
 * {{{php|
 * try {
 *     //トランザクションを開始
 *     $QB->transaction ();
 *
 *     //ここから発行したいクエリ
 *
 *
 *     //最後までくれば成功
 *     $QB->commit ();
 *
 * } catch (\Error $e)
 *     //クエリの発行に一つでも失敗した場合
 *     $QB->rollBack ();
 * }
 * }}}
 *
 * **使用例2**
 * フレームワークにロールバックを任せる場合
 * {{{php|
 * //トランザクションを開始
 * $QB->transaction ();
 *
 * //ここから発行したいクエリ
 *
 *
 * //ここまでくれば成功
 * $QB->commit ();
 *
 * }}}
 * @package    Feke
 * @subpackage util.querybuilder
 */

trait Transaction
{
	/**
	 *  トランザクションを開始します。
	 *
	 *  @return 成功した場合は、trueを返す。
	 *  @example $QB->transaction ();
	 */
	public function transaction ()
	{
		if (true === $this->_db->beginTransaction()) {
			$this->_transaction_fg = true;
			return true;
		} else {
			return $this->throwError('トランザクションの開始に失敗しました。');
		}
	}

	/**
	 *  トランザクションをロールバックします。
	 *
	 *  @return 成功した場合は、trueを返す。
	 *  @example $QB->rollBack ();
	 */
	public function rollBack ()
	{
		if ($this->_db->inTransaction()) {
			if (true === $this->_db->rollBack()) {
				$this->_transaction_fg = false;
				return true;
			} else {
				return $this->throwError('ロールバックに失敗しました。');
			}
		}
	}

	/**
	 *  トランザクションをコミットします。
	 *
	 *  @return 成功した場合は、trueを返す。
	 *  @example $QB->commit ();
	 */
	public function commit ()
	{
		if ($this->_db->inTransaction()) {
			if (true === $this->_db->commit()) {
				$this->_transaction_fg = false;
				return true;
			} else {
				return $this->throwError('コミットに失敗しました。');
			}
		}
	}
}

