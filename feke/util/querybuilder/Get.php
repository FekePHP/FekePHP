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

namespace feke\util\querybuilder;

/**
 * SELECT文実行メソッドと、実行したクエリの取得を行うメソッドのトレイトです。
 *
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */
trait Get
{

	/**
	 * SELRCT文を実行します。
	 * @param string $table_name テーブル名
	 * @param boolen $findOnly  （システム用）trueの場合は、データプロパティの書き換えを行いません
	 * @example //itemテーブルから取得
	 *          $QB->where('id','=','100')->get('item');
	 */
	public function get ($table_name = null, $findOnly = null)
	{
		if ($this->_each_flag === true) return $this->throwError('each()実行中は、同じインスタンスで新たなSELECT系メソッドは実行できません。<br>インスタンスを複数作成して実行してください。');

		//$table_nameが渡された場合は，上書き
		if ($table_name) $this->from($table_name);

		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$select_text = $this->_create_select();
			$this->_sql = "SELECT {$select_text} FROM \"{$this->_part['table']}\"";
		}

		//JOINの追加
		$this->_create_join ();

		//GROUP BYの追加
		$this->_create_group_by ();

		//HAVING句を追加
		$this->_create_having ();

		//where句を追加
		$this->_create_where ();

		//order句を追加
		$this->_create_order ();

		//limit句を追加
		$this->_create_limit();

		//実行
		return $this->run('get', $findOnly);
	}

	/**
	 * SELRCT文を実行します。
	 *
	 * get()メソッドとの違いは、返り値に予め「[0]」が付けられて返されるところのみです。
	 *
	 * @param string $table_name テーブル名
	 * @example //1行目と2行目の結果は等価です。
	 *          $data1 = $QB->where('id','=','100')->getOne('item');
	 *          $data2 = $QB->where('id','=','100')->get('item')[0];
	 */
	public function getOne ($table_name = null)
	{
		$this->get ($table_name = null);

		if (isset($this->_data[0])) return $this->_data[0];
		return false;
	}

	/**
	 * 直前のクエリに影響したレコード数を返します。
	 *
	 * このメソッドの返り値は、選択・更新・削除したカラム数ではないので注意してください。
	 *
	 * @return レコード数
	 * @example $QB->getRowCount();
	 */
	public function getRowCount()
	{
		return $this->_count;
	}


	/**
	 * 生成したサブクエリ用SQL文を取得します。
	 *
	 * 因数に何も指定しなかった場合は、直前に実行したSQL文を取得し、
	 * 引数を指定した場合は，指定したサブクエリを取得します。
	 *
	 * @param string $name サブクエリ名
	 * @example //直前に実行したSQL文を取得
	 *           $QB->getSql();
	 */
	public function getSql ($name = null, $comp = null)
	{
		if (!$comp) {
			if ($name) {
				return $this->get_sub($name);
			}
		} else {
			$sql = $this->get_sub($name);
			if (preg_match_all("/:([A-Za-z0-9_]+)/s", $sql, $m)) {
				foreach ($m[1] as $value) {
					$bind = $this->getBind($value);
					$sql = str_replace (":{$value}", $bind ,$sql);
				}
			}
			return $sql;
		}
		return self::$_do_sql;
	}


	/**
	 * バインディングの値を取得します。
	 *
	 * 引数を指定した場合は，指定したバインドの値を取得します。
	 * 指定しなかった場合は、すべてのバインド値の配列を返します。
	 *
	 * @param string $name バインド名
	 * @example //バインド名'item'の値を取得
	 *          $QB->getBind('item');
	 */
	public function getBind ($name = null)
	{
		if ($name) {
			if (isset($this->_bind[$name])) {
				return $this->_bind[$name];
			}
			return null;
		}
		return $this->_bind;
	}
}

