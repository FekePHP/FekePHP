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
 * QueryBuilderのレコード更新用メソッドが集まっているトレイトです。
 *
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */
trait Update
{
	/**
	 * UPDATE文を実行します。
	 *
	 * $this->_part['fields']配列をセットした場合は，
	 * セットされたフィールド名のみがテーブルへ挿入されます．
	 *
	 * ***$data引数の例***
	 * insert文の引数$dataと同じ指定方法です。
	 *
	 * @param array  $data 挿入するデータの配列
	 * @param string $pkey_value 更新したい主キーの値又は配列
	 * @param string $search_col_name 更新したいカラム名。指定がない場合は、主キーを使用します。
	 *
	 * @example //「item」テーブル、主キーが「1」な レコードを更新する。
	 *          $QB->from ('item');
	 *          $QB->update ($data, 1);
	 *
	 *          //各専用メソッドを使用して挿入する
	 *          $QB->from ('item')
	 *             ->setValue ($data)
	 *             ->set_filed (['name','price'])//対象カラムを絞りたい時だけ使用
	 *             ->where('id', 1)
	 *             ->Update();
	 *
	 *         //「item」テーブル、主キーが「1,10,100」な レコードを更新する。
	 *         $QB->from ('item');
	 *         $QB->update ($data, [1,10,100]);
	 */
	public function update ($data = null, $pkey_value = null, $search_col_name = null)
	{
		//テーブル名の指定があった場合
		//if ($table_name) $this->from($table_name);

		//データの指定があった場合
		if (isset($data)) {
			if (isset($data[0]) and is_string($data[0])) {
				$this->_part['values'] = [$data[0] => $data[1]];
			} elseif (is_array($data)) {
				$this->_part['values'] = $data;
			} elseif (is_object($data)) {
				$this->_part['values'] = to_array($data);
			}
		}
		
		//更新の際にセットするカラム名
		if (!is_value($search_col_name)) {
			$search_col_name = $this->_primary_key;
		}

		//主キーの指定があった場合
		if (isset($pkey_value)) {
			if (is_array($pkey_value)) $this->whereIn ($search_col_name, $pkey_value);
			else $this->where ($search_col_name, $pkey_value);
		}


		//sqlが存在しない場合のみパーツより生成
		if (!$this->_sql) {
			$this->_sql = "UPDATE \"{$this->_part['table']}\" ";
		}

		//SQLの生成
		$sqltext = null;

		//フィールドが指定されていた場合
		if (isset($this->_part['fields'])) {
			foreach ($this->_part['fields'] as $field) {
				if ($sqltext) $sqltext .= ',';

				//vindvalueのセット
				$bind_name = $this->bindCheck($field);
				$sqltext .= "\"{$field}\" = :{$bind_name} ";
				if (!isset($this->_part['values'][$field])) $this->_part['values'][$field] = null;
				$this->setBind ($bind_name, $this->_part['values'][$field]);
			}

		//フィールドの指定なし
		} else {
			foreach ($this->_part['values'] as $field => $value) {
				if ($sqltext) $sqltext .= ',';

				//vindvalueのセット
				$bind_name = $this->bindCheck($field);
				$sqltext .= "\"{$field}\" = :{$bind_name} ";
				$this->setBind ($bind_name, $value);
			}
		}

		//sql文に追加
		$this->_sql .= "SET {$sqltext} ";
		
		//where句を追加
		$this->_create_where ();

		//limit句を追加
		$this->_create_limit();
		dump($this->_sql);
		//実行
		return $this->run('update');
	}
	
	/**
	 * レコードを複数件更新します。
	 *
	 * ***$data引数の例***
	 * {{{php|
	 * //配列の例
	 * $data = array(
	 * 		//'キーの値' => ['カラム名' => '挿入値',...],
	 * 		'100' => ['name' => 'car','price' => '1000000000','into' => 'this is a pen!'],
	 * 		'150' => ['name' => 'cup','price' => '500','into' => ''],
	 * 		'151' =>	['name' => 'smartphone','price' => '19800','into' => 'かっこいい！',],
	 * );
	 * }}}
	 *
	 * >>SQLiteは非対応です。
	 *
	 * @param array  $data 挿入するデータの配列
	 * @param string $search_col_name 更新したいカラム名。指定がない場合は、主キーを使用します。
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
	public function updateAll ($data, $search_col_name = null)
	{
		$data = to_array($data);
		foreach ($data as $key => $value) {
			$this->update($value, $key, $search_col_name);
		}
	}

	/**
	 * 指定したカラムのみを更新します。
	 *
	 *
	 * @param string  $col_name 更新したいカラム名
	 * @param string  $value    値
	 * @param string|array $pkey_value 更新したい主キーの値又は配列
	 * @example //「user」テーブル、主キーが「1」な レコードの「name」カラムをmikuに更新する。
	 *          $QB->from ('user');
	 *          $QB->update_col ('name', 'miku', 1);
	 *
	 *          //findのようにwhereなども使用できます。
	 *          $QB->from ('user');
	 *              ->where('category_id', '>', '10')
	 *              ->update_col ('name', 'miku', 1);
	 */
	public function updateCol ($col_name, $value, $pkey_value = null)
	{
		$array = [[$col_name => $value]];

		$this->update ($array, $pkey_value);
	}

	/**
	 * 指定したカラムの値を任意値分プラスします。
	 *
	 * >>レコードの値がnullの場合は加算されません。
	 *
	 * @param string  $col_name 更新したいカラム名
	 * @param string  $value    加算したい値
	 * @param string|array $pkey_value 更新したい主キーの値又は配列
	 * @example //「user」テーブル、主キーが「10」な レコードの「visited」カラムに1を加算する。
	 *          $QB->from ('user');
	 *          $QB->plus ('visited', 1, 10);

	 */
	public function plus ($col_name, $plus = 1, $pkey_value = null)
	{
		$table = $this->_part['table'];
		$col_name = $this->_e($col_name);

		//数値か確認
		if (!is_numeric($plus)) {
			$this->throwError("[numeric:plus:{$plus}]");
		}

		//SQL分
		$this->_sql = "UPDATE \"{$table}\" SET \"{$table}\".\"{$col_name}\" = (\"{$table}\".\"{$col_name}\" + {$plus})";
		echo $this->_sql;
		//主キーの指定があった場合
		if ($pkey_value) $this->where ($this->_primary_key, $pkey_value);

		//where句を追加
		$this->_create_where ($this->_part['table']);

		//limit句を追加
		$this->_create_limit();

		//実行
		return $this->run('update');
	}
}

