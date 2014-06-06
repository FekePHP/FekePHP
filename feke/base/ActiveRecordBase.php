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

namespace feke\base;
/**
 * Active Record を実現するための基底クラスです。
 *
 * 基本的な使用方法は、QueryBuilderと同じですが、一部追加機能があるのでその説明をこのページで行います。
 * また、設定によっては更新・挿入値の検証も行うことができます。
 *
 * **他のクラスからの呼び出し方**
 * {{{php|
 * //Fekeクラスから
 * $Item = \Feke::load('ItemAR');
 *
 * //直接
 * $Item = new \app\activerecord\ItemAR();
 * }}}
 *
 * **最初のレコードを取得するとき**
 * 下記のいずれも1レコード分の情報を取得、出力します。
 * {{{php|
 * //テーブル内の主キーが10なレコードを取得
 * $Item->find(1);
 *
 * //商品の情報を表示
 * echo "<dt>{$Item->title}</dt>";
 * echo "<dd>{$Item->introduction}</dd>";
 *
 * ////テーブル内の全レコードを取得
 * $Item->all();
 *
 * //商品の情報を表示
 * echo "<dt>{$Item->title}</dt>";
 * echo "<dd>{$Item->introduction}</dd>";
 * }}}
 *
 * **複数レコード取得し、使用するとき**
 * {{{php|
 * //テーブル内の全レコードを取得
 * $Item->all();
 *
 * //(1) foreachとresult()を使って取得
 * foreach ($Item->result() as $row) {
 *     //商品の情報を表示
 *     echo "<dt>{$row->title}</dt>";
 *     echo "<dd>{$row->introduction}</dd>";
 * }
 *
 * //(2) whileとeach()を使って取得
 * while ($Item->each()) {
 *     //商品の情報を表示
 *     echo "<dt>{$Item->title}</dt>";
 *     echo "<dd>{$Item->introduction}</dd>";
 * }
 * }}}
 * >>(2)の取得方法のほうが見た目はシンプルですが、オーバーヘッドが大きいので大量のレコードを処理する場合は、(1)が推奨です。
 *
 * **新しいレコードを挿入**
 * {{{php|
 * $Item->name = 'スポーツカー';
 * $Item->price = 10000000;
 * $Item->save();
 *
 * //前の段階で、取得メソッドを使用していた場合は、取得値をリセットする必要があります。
 * $Item->find();
 * $Item->name = 'スポーツカー';
 * $Item->price = 10000000;
 * $Item->save();
 * }}}
 *
 * **レコードが存在すれば更新、しなければ挿入**
 * {{{php|
 * $Item->find(10);
 * $Item->name = 'スポーツカー';
 * $Item->price = 10000000;
 * $Item->save();
 * }}}
 *
 * **複数のレコードを更新**
 * {{{php|
 * $Item->all();
 * while ($Item->next()) {
 *     //すべての商品を半額にする
 *     $Item->price = $row->price / 2;
 *     $Item->save();
 * }
 * }}}
 *
 * @package    Feke
 * @subpackage base
 */

class ActiveRecordBase extends \feke\util\QueryBuilder
{
	/**
	 * テーブルのカラム情報
	 */
	private $_columns = array();


	/**
	 * 型の確認を行う
	 */
	//protected $_check_type = true;

	/**
	 * saveするデータを保存
	 */
	protected $_new_data = array();

	/**
	 * find_key
	 */
	private $_find_key = null;


	/**
	 * コンストラクタ
	 * @class hide
	 */
	public function __construct ()
	{
		$this->mode('object');
		if ($this->_auto_setting === true) {
			$this->_columns = $this->getColsData();
		} else {
			foreach ($this->_col_rule as $array) {
				$this->_columns[$array['name']] = $array;
			}
		}
	}

	/**
	 * 値を挿入します。
	 * @param unknown $filed
	 * @param unknown $value
	 * @class hide
	 */
	public function __set($filed, $value)
	{
		if (strpos($filed, '_') === 0) $this->throwError ('カラム名の最初がアンダーバーの場合は挿入できません。');

		//カラムが存在しなかったら
		$this->_check_col ($filed);
		if ($filed == $this->_primary_key) {
			return $this->throwError ("主キーの値は変更できません。");
		}
		$this->_new_data[$filed] = $value;

	}

	/**
	 * 値の取得
	 * @param string $filed
	 * @class hide
	 */
	public function __get($filed)
	{
		if ($this->_row_count === -1) $this->_row_count = 0;
		if (!is_numeric($this->_row_count)) $this->_row_count = 0;

		if (strpos($filed, '_') === 0) $this->throwError ('カラム名の最初がアンダーバーの場合は取得できません。');

		if (isset($this->_data[$this->_row_count]->{$filed})) {
			return $this->_data[$this->_row_count]->{$filed};
		} else {
			//カラムが存在しなかったら
			$this->_check_col ($filed);
			return null;
		}
	}



	/**
	 * 更新・挿入を行います。
	 *
	 * MySQLとその他のBDでは内部の動作が異なります。
	 *
	 * @return 更新が成功した場合はtrueを返します。
	 * @example //新しいレコードを挿入
	 *           $Item->name = 'スポーツカー';
	 *           $Item->price = 10000000;
	 *           $Item->save();
	 *
	 *           //レコードを更新、又は新規作成
	 *           $Item->find(1);
	 *           $Item->name = 'スポーツカー';
	 *           $Item->price = 10000000;
	 *           $Item->save();
	 */
	public function save ()
	{
		//現在のfindのキーを取得する
		$this->_find_key = false;
		if ($this->_row_count === -1) $this->_row_count = 0;

		if (isset($this->_data[$this->_row_count]->{$this->_primary_key})) {
			$this->_find_key = $this->_data[$this->_row_count]->{$this->_primary_key};
		}

		//挿入されるデータを検証
		if ($this->_check_input === true) {
			$this->_check_type ();
		}

		//トランザクション中でなければトランザクションを実行
		if (!$this->_db->inTransaction()) {
			$this->transaction ();
			$transaction = true;
		} else {
			$transaction = false;
		}

		//更新または挿入
		if ($this->_find_key !== false) {
			//MySQL用
			if ($this->_db_name == 'mysql') {
				$flag = $this->_save_mysql ();
			//その他
			} else {
				$flag = $this->_save_any ();
			}
		//挿入
		} else {
			//テーブルがオートインコメントでない場合
			if($this->_auto_increment === false) {
				//IDの最大値
				$res = $this->_db->query("SELECT MAX(\"{$this->_primary_key}\") AS max FROM \"{$this->_main_table}\"");
				//SQLを実行
				$max_id = $res->fetchAll()[0]->max + 1;
				$this->values($this->_primary_key, $max_id);
			}

			$flag = $this->insert($this->_new_data);
		}

		$this->_new_data = array();

		//このメソッド内でトランザクションを実行した場合は
		if ($transaction === true and $flag !== false) {
			$this->commit();
			return true;
		} elseif ($flag !== false) {
			return true;
		}

		//ここまではこないはず
		$this->rollBack();
		return false;
	}

	/**
	 * MySQの更新・挿入メソッドです。
	 */
	protected function _save_mysql ()
	{
		$this->_sql = "INSERT INTO \"{$this->_part['table']}\"";

		//主キー用
		$bind_name = $this->bindCheck($this->_primary_key);
		$calams = "\"{$this->_primary_key}\"";
		$values = " :{$bind_name} ";
		$this->setBind($bind_name, $this->_find_key);
			//挿入するデータの用意
		foreach ($this->_new_data as $field => $value) {
			$bind_name = $this->bindCheck($field);
			$calams .= ", \"{$field}\"";
			$values .= ", :{$bind_name} ";
			$this->setBind($bind_name, $value);
		}

		$this->_sql .= " ({$calams}) ";
		$this->_sql .= " VALUES ({$values}) ";

		$this->_sql .= " ON DUPLICATE KEY UPDATE ";

		$sqltext = null;
		foreach ($this->_new_data as $field => $value) {
			if ($sqltext) $sqltext .= ',';

			//vindvalueのセット
			$bind_name = $this->bindCheck($field);
			$sqltext .= "\"{$field}\" = :{$bind_name} ";
			$this->setBind ($bind_name, $value);
		}
		$this->_sql  .= $sqltext;
		//実行
		return $this->run('insert');
	}

	/**
	 * MySQ以外の更新・挿入メソッドです。
	 */
	protected function _save_any ()
	{
		$this->_primary_key = $this->_e($this->_primary_key);
		$result = $this->query ("SELECT \"{$this->_primary_key}\" FROM \"{$this->_part['table']}\" WHERE \"{$this->_primary_key}\" = :id", ['id' => $this->_find_key]);

		//データが存在すれば更新処理
		if(isset($result[0]) and $result[0]->{$this->_primary_key} === $this->_find_key) {
			//更新用のwhere
			$this->where($this->_primary_key, $key);
			//更新
			$flag = $this->update ($this->_new_data);
		} else {
			if($this->_auto_increment === false) {
				//IDの最大値
				$res = $this->_db->query("SELECT MAX(\"{$this->_primary_key}\") AS max FROM \"{$this->_main_table}\"");
				//SQLを実行
				$max_id = $res->fetchAll()[0]->max + 1;
				$this->values($this->_primary_key, $max_id);
			}

			$flag = $this->insert($this->_new_data);
		}
		return $flag;
	}

	/**
	 * 次のレコードへ進めます。
	 *
	 * @return 次のレコードがあった場合はtrue、なかった場合はfalseを返します。
	 */
	/*
	public function next ()
	{
		++$this->_row_count;
		if (isset($this->_data[$this->_row_count])) {
			return true;
		}
		return false;
	}
	*/
	/**
	 * 次のレコードへ進めます。
	 *
	 * @return 次のレコードがあった場合はtrue、なかった場合はfalseを返します。
	 * @example //複数のレコードを更新
	 *          //すべての商品を半額にする
	 *          $Item->all();
	 *          while ($this->each()) {
	 *               $Item->price = $Item->price / 2;
	 *               $Item->save();
	 *           }
	 */
	public function each ()
	{
		if ($this->_each_flag === false) {
			$this->reset_count();
		}
		++$this->_row_count;
		if ($this->_row_count < $this->_get_count) {
			$this->_each_flag = true;
			return true;
		}
		$this->_each_flag = false;
		return false;
	}

	/**
	 * 取得したレコードの行数をはじめの一行目、又は指定した行数に変更します。
	 *
	 * @param numeric $row_num
	 *
	 * @return $this
	 * @example $Item->all();
	 *          //5行目のレコートを操作する
	 *          $Item->reset_count(5);
	 */
	public function reset_count ($row_num = null)
	{
		if ($row_num) $this->_row_count = $row_num;
		else $this->_row_count = -1;

		return $this;
	}

	/**
	 * カラムの存在確認します。
	 */
	private function _check_col ($filed)
	{
		//カラムの存在確認
		if ($this->_auto_setting === true and !isset($this->_columns[$filed])) {
			return $this->throwError ("Table「{$this->_main_table}」にはカラム名「{$filed}」は存在しません。");
		}
		if ($this->_auto_setting === false and !isset($this->_columns[$filed])) {
			return $this->throwError ("Table「{$this->_main_table}」にはカラム名「{$filed}」は存在しません。");
		}
	}

	/**
	 * カラムの型と必須値の確認します。
	 */
	private function _check_type ()
	{
		if ($this->_auto_setting === true) {
			foreach ($this->_new_data as $filed => $value) {
				$type = strtolower($this->_columns[$filed]->Type);

				//Nullを許可するか確認
				if ($this->_columns[$filed]->Null == 'NO' and '' === strval($value)) {
					return $this->throwError ("Table「{$this->_main_table}」カラム「{$filed}」には、NULLは許可されていません。");
				}

				//型の確認
				if (strpos($type, 'int') !== false) {
					preg_match("/[0-9]+/",$type,$match);
					$limit = $match[0];
					if (true === ($msg = check($value, "numeric|maxlength:{$limit}"))) {
						return true;
					} else {
						return $this->throwError ("Table「{$this->_main_table}」カラム「{$filed}」は、 {$msg}");
					}
				} elseif (strpos($type, 'varchar') === 0) {
					preg_match("/[0-9]+/",$type,$match);
					$limit = $match[0];

					if (true === ($msg = check($value, "maxlength:{$limit}"))) {
						return true;
					} else {
						return $this->throwError ("Table「{$this->_main_table}」カラム「{$filed}」 は、{$msg}");
					}
				}
			}
		} else {
			$val = \Feke::load('Validation', 'util');
			$val->setValue($this->_new_data);
			if (!$val->run($this->_col_rule)) {
				$error_msg = null;
				foreach ($val->getError() as $col => $msg) {
					$error_msg .= "Table「{$this->_main_table}」カラム「{$col}」 は、{$msg}<br>";
				}
				return $this->throwError ($error_msg);
			}
		}

		return true;
	}

}
