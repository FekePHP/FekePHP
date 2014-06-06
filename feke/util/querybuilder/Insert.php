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
trait Insert
{
	/**
	 * テーブルにレコードを挿入します。
	 *
	 * INSERTクエリをバインドを利用して実行します．
	 *
	 * $this->_part['fields']配列をセットした場合は，
	 * セットされたフィールド名のみがテーブルへ挿入されます．
	 *
	 * ***$data引数の例***
	 * {{{php|
	 * //配列の例
	 * $data = array(
	 *     //'カラム名' => '挿入値',
	 *     'name' => 'car',
	 *     'price' => '1000000000',
	 *     'into' => 'this is a pen!',
	 * );
	 *
	 * //オブジェクト形式の例
	 * $data = new \stdClass;
	 * $data->name = 'car';
	 * $data->price = '1000000000';
	 * $data->into = 'this is a pen!';
	 * }}}
	 *
	 * @param array  $data 挿入するデータの配列
	 * @example //「item」テーブルに上記配列を挿入する。
	 *          $QB->from ('item')->insert ($data);
	 *
	 *          //各専用メソッドを使用して挿入する
	 *          $QB->from ('item')
	 *             ->setValue ($data)
	 *             ->set_filed (['name','price'])
	 *             ->insert();
	 */
	public function insert ($data = null)
	{
		$calams = '';
		$values = '';
		$replace_fg = false;

		if (is_array($data)) {
			$this->_part['values'] = $data;
		}elseif (is_object($data)) {
			$this->_part['values'] = to_array($data);
		}

		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$this->_sql = "INSERT INTO \"{$this->_part['table']}\"";
		} else {
			$replace_fg = true;
			$this->_sql .= " \"{$this->_part['table']}\"";
		}

		//SQLの生成
		$fileds = array();
		//フィールドが指定されていた場合
		if (isset($this->_part['fields'])) {
			foreach ($this->_part['fields'] as $field) {
				if ($calams) $calams .= ',';
				$calams .= "\"{$field}\"";

				if ($values) $values .= ',';

				//vindvalueのセット
				$bind_name = $this->bindCheck($field);
				$values .= ":{$bind_name} ";
				if (isset($this->_part['values'][$field])) $this->bind ($bind_name, $this->_part['values'][$field]);
				else $this->setBind ($bind_name, '');
			}
			$this->_sql .= " ({$calams}) ";
			$this->_sql .= " VALUES ({$values}) ";
		//フィールドの指定なし
		} else {
			if (is_array($this->_part['values'])) {
				foreach ($this->_part['values'] as $field => $value) {
					if ($calams) $calams .= ',';
					$calams .= "\"{$field}\"";
	
					if ($values) $values .= ',';
	
					$bind_name = $this->bindCheck($field);
					$values .= " :{$bind_name} ";
					$this->setBind($bind_name, $value);
				}
			}
			
			$this->_sql .= " ({$calams}) ";
			$this->_sql .= " VALUES ({$values}) ";
			
		}

		//REPLACE INTO 用
		if ($replace_fg) {
			//where句を追加
			$this->_create_where ($this->_part['table']);
		}

		//実行
		return $this->run('insert');
	}

	/**
	 * テーブルにレコードを複数同時に挿入します。
	 *
	 * $this->_part['fields']配列をセットした場合は，
	 * セットされたフィールド名のみがテーブルへ挿入されます．
	 *
	 * ***$data引数の例***
	 * {{{php|
	 * //配列の例
	 * $data = array(
	 * [
	 *     //'カラム名' => '挿入値',
	 *     'name' => 'car',
	 *     'price' => '1000000000',
	 *     'into' => 'this is a pen!',
	 * ],
	 * [
	 *     'name' => 'cup',
	 *     'price' => '500',
	 *     'into' => '',
	 * ],
	 * [
	 *     'name' => 'smartphone',
	 *     'price' => '19800',
	 *     'into' => 'かっこいい！',
	 * ],
	 * );
	 * }}}
	 *
	 * >>SQLiteは非対応です。
	 *
	 * @param array  $data 挿入するデータの配列
	 * @example //「item」テーブルに上記配列を挿入する。
	 *          $QB->from ('item')->insert ($data);
	 *          //INSERT INTO "item" ("name","price","into")
	 *          //VALUES ( :name , :price , :into ),
	 *          //       ( :name_1 , :price_1 , :into_1 ),
	 *          //       ( :name_2 , :price_2 , :into_2 )
	 *
	 *          //カラム名を指定して挿入する
	 *          $QB->from ('item')
	 *             ->set_filed (['name','price'])
	 *             ->insert();
	 *          //INSERT INTO "item" ("name","price")
	 *          //VALUES ( :name , :price ),
	 *          //       ( :name_1 , :price_1 ),
	 *          //       ( :name_2 , :price_2 )
	 */
	public function insertAll ($data = null)
	{
		$calams = null;
		$values = null;
		$values_text= null;

		if (is_array($data)) {
			$this->_part['values'] = $data;
		}elseif (is_object($data)) {
			$this->_part['values'] = to_array($data);
		}

		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$this->_sql = "INSERT INTO \"{$this->_part['table']}\"";
		} else {
			$this->_sql .= " \"{$this->_part['table']}\"";
		}

		//SQLの生成
		$fileds = array();
		//フィールドが指定されていた場合
		if (isset($this->_part['fields'])) {
			foreach ($this->_part['fields'] as $field) {
				$values = null;
				if ($key === 0) {
					if ($calams) $calams .= ',';
					$calams .= "\"{$field}\"";
				}
				if ($values) $values .= ',';

				$bind_name = $this->bindCheck($field);
				$values .= " :{$field} ";

				if (isset($this->_part['values'][$field])) {
					$this->bind ($bind_name, $this->_part['values'][$field]);
				}
				else $this->setBind ($bind_name, '');

			}
			if ($values_text) $values_text .= ",";
			$values_text .= "($values)";

			//フィールドの指定なし
		} else {
			foreach ($this->_part['values'] as $key => $array) {
				if (empty($array)) break;

				$values = null;
				foreach ($array as $field => $value) {
					if ($key === 0) {
						if ($calams) $calams .= ',';
						$calams .= "\"{$field}\"";
					}

					if ($values) $values .= ',';

					$bind_name = $this->bindCheck($field);
					$values .= " :{$bind_name} ";
					$this->setBind($bind_name, $value);
				}

				if ($values_text) $values_text .= ",";
				$values_text .= "($values)";
			}
			$this->_sql .= " ({$calams}) ";
			$this->_sql .= " VALUES {$values_text} ";
		}

		//実行
		return $this->run('insert');
	}

	/**
	 * カラムごとにSQLを指定してテーブルにレコードを挿入します。
	 *
	 * INSERTのVALUE内にサブクエリを使用して実行します。
	 * サブクエリを指定したい場合は、配列で指定する必要があります。
	 *
	 * ***$data引数の例***
	 * {{{php|
	 * //使用するサブクエリの用意
	 * $QB->sub ('sub1')->from('category')->select->('id')->findBy('id', '10');
	 *
	 * //配列の例
	 * $data = array(
	 *     //'カラム名' => 'SQL文または値',
	 *     //値を挿入
	 *     'name' => 'car'
	 *     //SQL文を挿入,
	 *     'price' => ['SELECT price from price_table where id = 1'],
	 *     //保存したサブクエリを挿入
	 *     'category_id' => [$QB->getSql('sub1')],
	 * );
	 * }}}
	 *
	 * @param array  $data 挿入するデータの配列
	 * @example //「item」テーブルに上記配列を挿入する。
	 *          $QB->from ('item')->insertSql ();
	 *          //
	 */
	public function insertSql ($data)
	{
		$calams = '';
		$values = '';

		if (is_array($data)) {
			$this->_part['values'] = $data;
		}elseif (is_object($data)) {
			$this->_part['values'] = to_array($data);
		}

		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$this->_sql = "INSERT INTO \"{$this->_part['table']}\"";
		} else {
			$this->_sql .= " \"{$this->_part['table']}\"";
		}

		//SQLの生成
		$fileds = array();
		//フィールドが指定されていた場合
		if (isset($this->_part['fields'])) {
			foreach ($this->_part['fields'] as $field) {
				if ($calams) $calams .= ',';
				$calams .= "\"{$field}\"";

				if ($values) $values .= ',';

				if (is_array($value)) {
					$values .= "({$value})";
				} else {
					$bind_name = $this->bindCheck($field);
					$values .= " :{$bind_name} ";
					$this->setBind($bind_name, $value);
				}
			}
			$this->_sql .= " ({$calams}) ";
			$this->_sql .= " VALUES ({$values}) ";
			//フィールドの指定なし
		} else {
			foreach ($this->_part['values'] as $field => $value) {
				if ($calams) $calams .= ',';
				$calams .= "\"{$field}\"";

				if ($values) $values .= ',';

				if (is_array($value)) {
					$values .= "({$value[0]})";//dump($value);
				} else {
					$bind_name = $this->bindCheck($field);
					$values .= " :{$bind_name} ";
					$this->setBind($bind_name, $value);
				}
			}

			$this->_sql .= " ({$calams}) ";
			$this->_sql .= " VALUES ({$values}) ";
		}

		//実行
		return $this->run('insert');
	}

	/**
	 * SELECT文を使用して、レコードを挿入します。
	 *
	 * @param array  $data 挿入するカラムの配列
	 * @param string $sql  登録したクエリの名前、またはSELECT文
	 * @example //「item」テーブルに上記配列を挿入する。
	 *          $QB->sub ('sub1')->select->(['name','price','cate_id'])->findBy('id', '10');
	 *          $QB->from ('item')->insertSelect (['name','price','cate_id'],'sub1');
	 */
	public function insertSelect ($data, $sql)
	{
		$calams = '';
		$values = '';

		if (is_array($data)) {
			$this->_part['values'] = $data;
		}elseif (is_object($data)) {
			$this->_part['values'] = to_array($data);
		}

		$select = $this->get_subquery($sql);
		if (!$select) $select = $sql;

		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$this->_sql = "INSERT INTO \"{$this->_part['table']}\"";
		} else {
			$this->_sql .= " \"{$this->_part['table']}\"";
		}

		//SQLの生成
		$fileds = array();
		foreach ($this->_part['values'] as $field) {
			if ($calams) $calams .= ',';
			$calams .= "\"{$field}\"";
		}
		$this->_sql .= " ({$calams}) ".$select;

		//実行
		return $this->run('insert');
	}

	/**
	 * REPLACE実行メソッド
	 *
	 * @param array  $data 挿入するデータの配列
	 * @param string $pkey_value 更新したい主キーの値又は配列
	 * @example //「item」テーブル、主キーが「1」な レコードを再作成、新規作成する。
	 *          $QB->from ('item');
	 *          $QB->replace ($data, 1);
	 *
	 *          //各専用メソッドを使用してレコードを再作成、新規作成する。
	 *          $QB->from ('item')
	 *             ->setValue ($data)
	 *             ->set_filed (['name','price'])//対象カラムを絞りたい時だけ使用
	 *             ->where('id', 1)
	 *             ->replace();
	 */
	public function replace ($data = null, $pkey_value = null)
	{
		$this->_sql = "REPLACE INTO ";

		//主キーの指定があった場合
		if (isset($pkey_value)) {
			if (is_array($pkey_value)) $this->whereIn ($this->_primary_key, $pkey_value);
			else $this->where ($this->_primary_key, $pkey_value);
		}

		$this->insert ($data);

	}
}

