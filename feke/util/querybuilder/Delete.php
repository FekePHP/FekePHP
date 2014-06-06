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
 * QueryBuilderのレコード挿入用メソッドが集まっているトレイトです。
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */
trait Delete
{
	/**
	 * DELETE実行メソッド
	 *
	 * ※テーブルが空にならないよう，
	 * where句がセットされていない場合は，動作しません．
	 * テーブルを空にしたい場合は．TRUNCATEを使用してください．
	 *
	 *
	 *
	 * @param string|array $pkey_value 削除したい主キーの値又は配列
	 *
	 * @example //「user」テーブル、主キーが「1」な レコードを削除する。
	 *          $QB->from ('user');
	 *          $QB->delete (1);
	 *
	 *          //findのようにwhereなども使用できます。
	 *          //以下の例の場合は、'category_id'が10以上のものは全て削除
	 *          $QB->from ('user');
	 *              ->where('category_id', '>=', '10')
	 *              ->delete ();
	 */
	public function delete ($pkey_value = null)
	{

		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$this->_sql = "DELETE FROM \"{$this->_part['table']}\"";
		} else {
			$this->_sql .= " \"{$this->_part['table']}\"";
		}

		//主キーの指定があった場合
		if (isset($pkey_value)) {
			if (is_array($pkey_value)) $this->whereIn ($this->_primary_key, $pkey_value);
			else $this->where ($this->_primary_key, $pkey_value);
		}

		//SQLの生成
		//whereの指定がない場合，実行不可
		if (!$this->_create_where ()) {
			return $this->throwError ('delete()には必ずwhere句を指定する必要があります。');
		}

		//limit句を追加
		$this->_create_limit();

		//実行
		return $this->run('delete');
	}
}

