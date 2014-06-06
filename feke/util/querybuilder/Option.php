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
 * QueryBuilderのオプション指定用メソッドが集まっているトレイトです。
 *
 * ここに記載されているメソッドは、QueryBuilderをメソッドチェーンとして使用するとき、''間に指定することのできるオプション用メソッド''となります。
 *
 * これらのオプションメソッドは、基本的に順不同で問題なく動作しますが、''from()''メソッドのみは、オプションメソッドの先頭にしか使用できません。(subを除く)
 *
 * 記載方法の順序のおすすめは、以下の順番です。
 *
 * >sub > from > select = group > where > order > limit
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */

trait Option
{
	/**
	 * クエリを保存します。
	 *
	 * @param string $name クエリ名
	 * @return $this
	 * @example //サブクエリを保存
	 *          $QB->from('cat')->sub('one')->all();
	 *
	 *          //指定した名前を使用して、保存したクエリを呼び出せます。
	 *          echo $QB->getSql('cat');
	 *
	 */
	public function setScope ($name)
	{
		$this->_sub_query = $name;
		return $this;
	}

	/**
	 * 保存したスコープを呼び出します。
	 *
	 * 引数を指定した場合は，指定したサブクエリを取得します。
	 * 指定しなかった場合は、生成したサブクエリの配列を返します。
	 *
	 * @param string $name サブクエリ名
	 * @example //サブクエリ名'item'なSQL文を取得
	 *           $QB->scope('item');
	 */
	public function scope ($name)
	{
		if ($name) {
			if (isset($this->_sub_sql[$name])) {
				return $this->_sub_sql[$name];
			}
			return null;
		}
		return $this->_sub_sql;
	}


	/**
	 * レコードで参照されるテーブルを指定します。
	 *
	 * メインテーブルを設定されている場合は、クエリが実行されるたびに、メインテーブルに書き換えられます。
	 *
	 * @param string $name テーブル名
	 * @return $this
	 * @example //メインテーブルを'flute'に設定する
	 *          $QB->from('flute');
	 */
	public function from ($table)
	{
		$this->_part['table'] = $this->_e($table);
		return $this;
	}

	/**
	 * 次のクエリで使用するfrom以降をSQLで指定します。
	 *
	 * メインテーブルを設定されている場合は、クエリが実行されるたびに、メインテーブルに書き換えられます。
	 *
	 * @param string $name from国指定したいSQL文
	 * @return $this
	 * @example //メインテーブルを'flute'に設定する
	 *          $QB->from('flute');
	 */
	public function fromSql ($table)
	{
		if (is_string($table)) {
			$this->_part['table'] = $table;
		}
		return $this;
	}

	/**
	 * テーブル内のカラム名を指定して検索します。
	 *
	 * select,update,deleteに対して、where条件を付け足すことができます。
	 *
	 * @param string|array $field   カラム名
	 * @param string|array $symbol  比較(=,<,>,...)
	 * @param string|array $value   検索対象値
	 * @param string $connect       接続詞
	 * @return $this
	 *
	 * @example //以下の2つのメソッドチェーンは等価です。
	 *          $QB->where('category_id', '=', '100')->all();
	 *          $QB->where('category_id', '100')->all();
	 */
	public function where ($field , $symbol = null, $value = null , $connect = null , $top = null)
	{

		if ($value === null) {
			$value = $symbol;
			$symbol = null;
		}
		$this->_set_Where ($field, $value, $symbol, $connect, $top);
		return $this;
	}

	/**
	 * orWhere をセット
	 *
	 * @param string|array $field   フィールド名
	 * @param string|array $value   値
	 * @param string|array $symbol  比較(=,<,>,...)
	 * @return $this
	 *
	 * @example $QB->orWhere('category_id', '100')->all();
	 */
	public function orWhere ($field , $symbol = null, $value = null)
	{
		if ($value === null) {
			$value = $symbol;
			$symbol = null;
		}
		$this->_set_where ($field, $value, $symbol, 'OR');
		return $this;
	}

	/**
	 * WHRERE IN をセット
	 *
	 * ※配列に対応していません
	 *
	 * @param string $field フィールド名
	 * @param array $value  値を配列で
	 * @return $this
	 *
	 * @example //category_idが1,10,100のいずれかのレコードを検索する。
	 *          $QB->whereIn('category_id', [1, 10, 100])->all();
	 *          //SELECT * FROM "item" WHERE "item"."category_id" IN ("1" , "10" , "100")
	 */
	public function whereIn ($field , $value = null,  $connect = null)
	{
		$field = $this->_e($field);
		$connect = $this->_e($connect);

		//クラス変数へ挿入
		$text = '';
		if ($value) {
			foreach ($value as $key) {
				//バインドに登録する名前
				$bind_name = $this->bindCheck($field);

				//bindValueを登録
				$this->setBind ($bind_name, $key);

				if ($text) $text .= ', ';
				$text .= ":{$bind_name} ";
			}
		}
		if (!$connect) $connect = 'AND';

		$this->_part['where'][] = array (
				'field' => $field ,
				'value' => $text ,
				'symbol' => 'IN' ,
				'connect' => $connect,
		);
		return $this;
	}

	/**
	 * WHRERE NOT IN をセット
	 *
	 * ※配列に対応していません
	 *
	 * @param string $field フィールド名
	 * @param array $value  値を配列で
	 * @return $this
	 *
	 * @example //category_idが1,10,100のいずれかのレコードを検索する。
	 *          $QB->whereIn('category_id', [1, 10, 100])->all();
	 *          //SELECT * FROM "item" WHERE "item"."category_id" IN ("1" , "10" , "100")
	 */
	public function whereNotIn ($field , $value = null,  $connect = null)
	{
		$field = $this->_e($field);
		$connect = $this->_e($connect);

		//クラス変数へ挿入
		$text = '';
		if ($value) {
			foreach ($value as $key) {
				//バインドに登録する名前
				$bind_name = $this->bindCheck($field);

				//bindValueを登録
				$this->setBind ($bind_name, $key);

				if ($text) $text .= ', ';
				$text .= ":{$bind_name} ";
			}
		}
		if (!$connect) $connect = 'AND';

		$this->_part['where'][] = array (
				'field' => $field ,
				'value' => $text ,
				'symbol' => 'NOT IN' ,
				'connect' => $connect,
		);
		return $this;
	}

	/**
	 * where文を直接指定します。
	 *
	 * @param string|array $field   フィールド名
	 * @param string|array $value   値
	 * @param string|array $symbol  比較(=,<,>,...)
	 * @return $this
	 *
	 * @example //category_idが1,10,100のいずれかのレコードを検索する。
	 *          $QB->where_by_sql('"item"."category_id" IN ("1" , "10" , "100")')->all();
	 *          //SELECT * FROM "item" WHERE "item"."category_id" IN ("1" , "10" , "100")
	 */
	public function whereSql ($sql , $bind = null, $connct = 'AND')
	{
		$this->_set_where ($sql, '', 'SQL', $connct);

		if (is_array($bind)) {
			foreach ($bind as $key => $value) {
				$this->setBind($key, $value);
			}
		}

		return $this;
	}

	/**
	 * WHERE句にサブクエリを使用します
	 *
	 * where内でサブクエリのセットができるメソッドです。
	 * このメソッドを使用するためには、事前にサブクエリを準備する必要があります。
	 *
	 * @param string $field   カラム名
	 * @param string $name    サブクエリ名
	 * @param string $symbol  比較句
	 * @param string $connect 接続句
	 * @return $this
	 * @example //cate_idカラムをcate_subクエリの結果を使用して指定する。
	 *          //
	 * 			$QB->sub('cate_sub')->find(1);
	 *          //
	 *          $QB->where_sub('cate_id', 'cate_sub', '>', 'and')->all();
	 */
	public function whereScope ($field, $name, $symbol = '=', $connect = 'AND')
	{
		$symbol = $this->_e($symbol);
		$connect = $this->_e($connect);

		$sub_query = $this->scope($name);
		if (!is_string($sub_query)) {
			throw new \PDOException ("指定されたサブクエリ「{$name}」がありません．<br>");
		}

		if (!$symbol) $symbol = '=';
		if ($connect != 'OR') $connect = 'AND';

		$this->_part['where'][] = array (
				'field' => $field ,
				'value' => "({$sub_query})" ,
				'symbol' => $symbol,
				'connect' => $connect,
		);
		return $this;
	}

	/**
	 * 取得するカラムを指定します。
	 *
	 * @param string $select  カラム名
	 * @param string $as_name カラム名を置換したい名前
	 * @return $this
	 *
	 * @example //主キー1のnameカラムの情報のみを取得
	 *          $QB->select('name')->find(1);
	 *          //SELECT "name" FROM "classes" WHERE  ( "classes"."id" = 1)  LIMIT 1
	 *
	 *          //主キー1のnameカラムの情報のみを取得し、nameを'名前'に置き換える
	 *          $QB->select('name','名前')->find(1);
	 *          //SELECT "name" as "名前" FROM "classes" WHERE  ( "classes"."id" = 1)  LIMIT 1
	 */
	public function select ($select ,$as_name = null)
	{
		if (is_array($select)) {
			foreach($select as $key => $value) {
				if (isset($as_name[$key])){
					if ($as_name[$key]) $as_name = ' as "'.$this->_e($as_name).'"';
				}
				$this->_part['select'][$this->_select_count] = '"'.$this->_e($value).'"'.$as_name;
				++$this->_select_count;
			}
		} else {
			if ($as_name) $as_name = ' as "'.$this->_e($as_name).'"';
			$this->_part['select'][$this->_select_count] = '"'.$this->_e($select).'"'.$this->_e($as_name);
			++$this->_select_count;
		}
		return $this;
	}

	/**
	 * select をSQL文で指定します。
	 *
	 * @param string $select
	 * @return $this
	 *
	 * @example //主キー1のnameカラムの情報のみを取得
	 *          $QB->select_by_sql('COUNT("id")')->find(1);
	 *          //SELECT COUNT("id") FROM "classes" WHERE  ( "classes"."id" = 1)  LIMIT 1
	 */
	public function selectSql ($select)
	{
		if (is_array($select)) {
			foreach($select as $key => $value) {
				$this->_part['select_by_sql'][$this->_select_count] = $value;
				++$this->_select_count;
			}
		} elseif (is_string($select)) {
			$this->_part['select_by_sql'][$this->_select_count] = $select;
			++$this->_select_count;
		}
		return $this;
	}

	/**
	 * 重複するレコードを結果から除きます。
	 *
	 * @return $this
	 *
	 * @example //主キー1のnameカラムの情報のみを取得
	 *          $QB->distinct()->find(1);
	 *          //SELECT Distinct * FROM "classes" WHERE  ( "classes"."id" = 1)
	 */
	public function uniq ()
	{
		$this->selectSql('Distinct');
		return $this;
	}

	/**
	 * データをまとめます。
	 *
	 * 第二引数がtrueの場合は、自動でselectにカラム名をセットします。
	 *
	 * @param mixed $col_name グループ化するカラム名を文字列、又は配列で指定
	 * @param boolen $select_fg trueの場合は、selectにカラム名をセットします。
	 * @param string $as_name   設定された場合は、selectに別名(AS)を付けます。
	 * @return $this
	 *
	 * @example //主キー1のnameカラムの情報のみを取得
	 *          $QB->group('name')->find(1);
	 *          //SELECT "name" FROM "classes" GROUP BY "item"."name" WHERE  ( "classes"."id" = 1)
	 */
	public function group ($col_name, $select_fg = false, $as_name = null)
	{
		if (is_array ($col_name)) {
			foreach ($col_name as $key => $value) {
				$this->_part['group_by'][] = $this->_e($value);
				if ($select_fg) {
					if (isset($as_name[$key])) $name =  $as_name[$key];
					else $name = $as_name;
					$this->select($value ,$name);
				}
			}
		} else {
			$this->_part['group_by'][] = $this->_e($col_name);
			if ($select_fg) $this->select($col_name, $as_name);
		}

		return $this;
	}


	/**
	 * 取得した値を元に、レコードを絞り込みます。
	 *
	 * @param string $type    集計方法
	 * @param string $field   カラム名
	 * @param string $name    サブクエリ名
	 * @param string $symbol  比較句
	 * @param string $connect 接続句
	 *
	 * @return $this
	 *
	 * @example //注文テーブル
	 *          $QB->from('order');
	 *
	 *          //ユーザーIDごとにグループ化して、
	 *          //payの合計が100000以上のレコードを取り出す。
	 *          $QB->group('user_id',true)->having('sum','pay','>=','100000')->sum('pay', '総額');
	 *
	 *          //ユーザーIDごとにグループ化して、
	 *          //payのが100000のレコードを取り出す。
	 *          $QB->group('user_id',true)->having('','pay','=','100000')->sum('pay', '総額');
	 */
	public function having ($type, $field, $symbol, $value, $connect = 'AND')
	{
		$type = $this->_e($type);
		$field = $this->_e($field);
		$symbol = $this->_e($symbol);
		$connect = $this->_e($connect);

		//比較初期値
		if (!$symbol) $symbol = '=';
		if (!$connect) $connect = 'AND';

		if (!$field) return $this->_throwError ('カラム名の指定は必須です。');

		//バインドをする際のパラメータ名を取得
		if (!$type) $bind_name = $this->bindCheck($field);
		else $bind_name = $this->bindCheck("{$type}_{$field}");

		//クラス変数へ挿入
		$this->_part['having'][] = array (
				'field' => $field ,
				'type' => $type,
				'value' => ":{$bind_name} " ,
				'symbol' => $symbol,
				'connect' => $connect,
		);

		//bindValueを登録
		$this->setBind ($bind_name, $value);

		return $this;
	}

	/**
	 * having句を直接指定します。
	 *
	 * @param string|array $field   フィールド名
	 * @param string|array $bind   バインドを行いたい場合はその配列
	 * @param string|array $connct  接続詞
	 * @return $this
	 *
	 * @example  //payの合計が100000以上のレコードを取り出す。
	 *          $QB->having_by_sql('SUM(pay) >= :price',['price' => 10000]);
	 */
	public function havingSql ($sql , $bind = null, $connct = 'AND')
	{
		$this->_set_having ($sql, '', 'SQL', $connct);

		if (is_array($bind)) {
			foreach ($bind as $key => $value) {
				$this->setBind($key, $value);
			}
		}

		return $this;
	}

	/**
	 * 挿入、更新をするカラム名を制限します。
	 *
	 * @param mixed $clam 許可したいカラム名の文字列、または配列
	 * @return $this
	 *
	 * @example
	 *          //挿入したデータ
	 *          $data = ['item_id' => 123,'price' => 5000,'stock' => 20];
	 *          $this->value ($data);
	 *
	 *          //与えられたデータの中で、'price','stock'のみ更新に使用する。
	 *          $this->fields(['price','stock']);
	 */
	public function fields ($clam)
	{
		if (is_array($clam)) {
			foreach ($clam as $value) {
				$this->_part['fields'][] = $value;
			}
		} else {
			$this->_part['fields'][] = $clam;
		}
		return $this;
	}


	/**
	 * 挿入、更新したい内容を設定します。
	 *
	 * @param string|array $field   カラム名、または配列
	 * @param string|array $value   値
	 * @return $this
	 *
	 * @example //1つのみ指定する
	 *          $QB->values('name','ミク');
	 *
	 *          //配列で一気に指定する
	 *          $QB->values(['name' => 'ミク', 'category_id' => 1]);
	 */
	public function values ($field, $values= null)
	{
		if (is_array($field)) {
			foreach ($field as $field => $value) {
				$field = $this->_e($field);
				$this->_part['values'][$field] = $value;
			}
		} else {
			$field = $this->_e($field);
			$this->_part['values'][$field] = $values;
		}
		return $this;
	}

	/**
	 * 取得するデータを昇順に並び替えをします。
	 *
	 * @param string|array $order 並び替える基準カラムのカラム名、又は配列
	 * @return $this
	 *
	 * @example $QB->order('name')->all();
	 *          //SELECT * FROM "item" ORDER BY "name"
	 *
	 *          $QB->order(['name','id'])->all();
	 *          //SELECT * FROM "item" ORDER BY "name" , "id"
	 */
	public function order ($order)
	{
		if (is_array ($order)) {
			foreach ($order as $value) {
				$this->_part['order'][] = '"'.$this->_e($value).'"';
			}
		} else {
			$this->_part['order'][] = '"'.$this->_e($order).'"';
		}
		return $this;
	}

	/**
	 * 取得するデータを降順に並び替えをします。
	 *
	 * @param string|array $order 並び替える基準カラムのカラム名、又は配列
	 * @return $this
	 *
	 * @example $QB->reorder('name')->all();
	 *          //SELECT * FROM "item" ORDER BY "name" DESC
	 *
	 *          $QB->reorder(['name','id'])->order('title')->all();
	 *          //SELECT * FROM "item" ORDER BY "name" DESC , "id" DESC , "title"
	 */
	public function reorder ($order)
	{
		if (is_array ($order)) {
			foreach ($order as $value) {
				$this->_part['order'][] = '"'.$this->_e($value).'" DESC';
			}
		} else {
			$this->_part['order'][] = '"'.$this->_e($order).'" DESC';
		}
		return $this;
	}

	/**
	 * order句をSQL文で直接指定します。
	 *
	 * @param string|array $order 並び替える基準カラムのカラム名、又は配列
	 * @return $this
	 *
	 * @example $QB->order_by_sql('"name" DESC')->all();
	 *          //SELECT * FROM "item" ORDER BY "name" DESC
	 */
	public function orderSql ($order)
	{
		if (is_array ($order)) {
			foreach ($order as $value) {
				$this->_part['order'][] = "{$value} ";
			}
		} else {
			$this->_part['order'][] = "{$order} ";
		}
		return $this;
	}

	/**
	 * クエリの取得数と、取得返し位置を設定します。
	 *
	 * 第一引数のみを指定したときは、limit(取得数)、
	 * 第一引数、第二引数の両方を指定したときは、limit(開始位置,取得数)となります。
	 *
	 * @param string $st 開始位置または取得数
	 * @param string $num 取得数
	 * @return $this
	 *
	 * @example //最初から、20件を取得する。（つまり、1～20件目を取得）
	 *          $QB->limit(20)->all();
	 *
	 *          //100件目から、20件を取得する。（つまり、100～120件目を取得）
	 *          $QB->limit(100 ,20)->all();
	 *
	 */
	public function limit ($start, $num = null)
	{
		if ($num) {
			$this->_part['limit_start'] = $start;
			$this->_part['limit_num'] = $num;
		} else {
			$this->_part['limit_num'] = $start;
		}
		return $this;
	}


	/**
	 * テーブルを結合します。
	 *
	 * >>JOINの結合条件を自動生成する場合は、カラム名'テーブル名_id'が結合されるテーブル（左辺）に存在し、カラム名'id'が結合するテーブル（右辺）に存在する必要があります。
	 *
	 * @param string $join_table 結合したいテーブル名
	 * @param string $type       結合を行う方法(LEFT, RIGHT, OUTER, INNER, LEFT OUTER, RIGHT OUTER)
	 *
	 * @return $this
	 * @example //categoryテーブルをclassesテーブルに結合
	 *          $QB->from('classes')->join('category')->all();
	 *          //	SELECT * FROM "classes" INNER JOIN "category" ON "classes"."category_id" = "category"."id"
	 *
	 *          //categoryテーブルをclassesテーブルに結合し、itemテーブルをcategoryテーブルに結合
	 *          $AR->join(['category' => 'item'],'left')->all()
	 *          //SELECT * FROM "classes" LEFT JOIN "category" ON "classes"."category_id" = "category"."id" LEFT JOIN "item" ON "category"."item_id" = "item"."id"
	 */
	public function join ($join_table, $type = 'INNER')
	{
		$type = $this->_e($type);

		if (!is_null($type)) {
			$type =  strtoupper($type);
			$permit_type = array('', 'LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER');
			if (!in_array($type,$permit_type)) {
				$this->throwError('使用できない結合方法(JOIN)です．');
			}
			$type = ' '.$type.' JOIN';
		} else {
			$this->throwError ('結合可能なJOINの形式が選択されていません。');
		}

		//join作成用
		$create_join = function ($type, $this_table, $join_table) {
			return  "{$type} \"{$join_table}\" ON \"{$this_table}\".\"{$join_table}_id\" = \"{$join_table}\".\"id\"";
		};

		//joinを再帰的にするかも
		$array_join = function ($type, $this_table, $join_table) use($create_join){
			foreach ($join_table as $key => $value) {
				if (is_numeric($key)) {
					//JOINをルールから生成
					$this->_part['join'][] = $create_join ($type, $this_table, $value);
				} else {
					$this->_part['join'][] = $create_join ($type, $this_table, $key);

					$this_table = $key;
					$key = $value;
					$this->_part['join'][] = $create_join ($type, $this_table, $key);
				}
			}
		};

		if (is_string($join_table)) {
			$join_table = $this->_e($join_table);
			//JOINをルールから生成
			$this_table = $this->_part['table'];

			$this->_part['join'][] = $create_join ($type, $this_table, $join_table);
		} else {
			$this_table = $this->_part['table'];
			$array_join ($type, $this_table, $join_table);
		}

		return $this;
	}

	/**
	 * テーブルを結合をSQLで直接指定します。
	 *
	 * @param string $join_table JOIN句
	 *
	 * @return $this
	 */
	public function joinSql ($sql)
	{
		$this->_part['join'][] = $sql;

		return $this;
	}

	/**
	 * テーブルを結合します。
	 *
	 * >>JOINの結合条件を自動生成する場合は、カラム名'テーブル名_id'が結合されるテーブル（左辺）に存在し、カラム名'id'が結合するテーブル（右辺）に存在する必要があります。
	 *
	 * @param string $join_table 結合したいテーブル名
	 * @param string $type       結合を行う方法(LEFT, RIGHT, OUTER, INNER, LEFT OUTER, RIGHT OUTER)
	 *
	 * @return $this
	 * @example //categoryテーブルをclassesテーブルに結合
	 *          $QB->from('classes')->join('category')->all();
	 *          //	SELECT * FROM "classes" INNER JOIN "category" ON "classes"."category_id" = "category"."id"
	 *
	 *          //categoryテーブルをclassesテーブルに結合し、itemテーブルをcategoryテーブルに結合
	 *          $AR->join(['category' => 'item'],'left')->all()
	 *          //SELECT * FROM "classes" LEFT JOIN "category" ON "classes"."category_id" = "category"."id" LEFT JOIN "item" ON "category"."item_id" = "item"."id"
	 */
	public function includes ($join_table)
	{
		$this->join ($join_table, 'LEFT OUTER');
		return $this;
	}
}

