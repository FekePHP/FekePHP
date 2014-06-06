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
 * QueryBuilderの取得実行系メソッドが集まっているトレイトです。
 *
 * ここに記載されているメソッドは、QueryBuilderをメソッドチェーンとして使用するとき、''最後に指定することのできる取得用メソッド''となります。
 *
 *
 * **返り値について**
 * ***サンプルテーブル (item)***
 * |id|name|price|category|
 * |1|りんご|100|fruits|
 * |2|ばなな|200|fruits|
 * |3|塩|300|seasoning|
 * |4|醤油|400|seasoning|
 * |5|砂糖|1000|seasoning|
 * ***find(1),findBy()***
 * オブジェクト形式で1レコード分の検索結果が返されます。
 * もし、見つからなかった場合は、falseが返されます。
 * {{{php|
 *     $obj = $QB->find(1);
 *     //レコードの取得は ''返り値->カラム名''で取得できます。
 *     echo $obj->name;  // りんご
 *     echo $obj->...
 * }}}
 *
 * ***find(array),findAllBy(), all(), first(),....系***
 * 配列で検索結果が返されます。
 * もし、見つからなかった場合は、falseが返されます。
 * {{{php|
 *     //レコードの取得は foreach等で、配列の要素を取得してから、''返り値->カラム名''で取得できます。
 *     $obj = $QB->all();
 *     foreach ($obj as $row) {
 *         echo $row->name; // りんご
 *         echo $row->...
 *     }
 *
 *     //又は
 *     $QB->all();
 *     foreach ($QB->getData() as $row) {
 *         echo $row->name;
 *         echo $row->...
 *     }
 * }}}
 *
 * ***count(), ave(),...***
 * クループ化されていない場合は、計算結果をそのまま返します。
 * {{{php|
 *     //count(),ave()などのメソッドは取得した値をそのまま返します。
 *     //テーブルに5件レコードが存在するので
 *     //$obj に ''5''が返されます。
 *     $count = $QB->count('id');
 *     echo $count; // 5
 *
 *     //idの値が,「1,2,3,4,5」なので
 *     $obj = $QB->ave('id');
 *     echo $obj; // 3
 *
 *     //group(),has()等を使用した場合
 *     //指定されたカラムごとの結果が返ります。
 *     $count = $QB->group('category')->count('id');
 *     echo $count->fruits;    // 2
 *     echo $count->seasoning; // 3
 *
 * }}}
 * ***pluck()***
 * 指定されたカラムの各値を1次元配列にして返します。
 * {{{php|
 *     $obj = $QB->pluck('name');
 *     $obj = ['りんご','ばなな','塩',....];
 * }}}
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */

trait Find
{

	/**
	 * 主キーの値を指定して最初の一件を取得します。
	 *
	 * @param mixed $value 検索したい主キーの値・配列
	 * @example $data = $QB->find(100);
	 *          //発行されるSQL
	 *          //SELECT * FROM "item" WHERE ( "item"."id" = "100" ) LIMIT 1
	 *
	 *          $data = $QB->find([1,10,100]);
	 *          //発行されるSQL
	 *          //SELECT * FROM "item" WHERE ( "item"."id" IN ("1", "10", "100") ) LIMIT 1
	 */
	public function find ($value = null)
	{
		if ($value === null) {
			$this->_data = array();
		}

		 if (false !== $this->findBy ($this->_primary_key, $value)) {
		 	if (is_array($value)) return $this->_data;
		 	return $this->_data[0];
		 }
		 return false;
	}

	/**
	 * カラムの値を指定して最初の一件を取得します。
	 *
	 * @param string $field 検索したいカラム名、又は検索条件の配列
	 * @param mixed $find 検索したいカラムの値・配列
	 * @example //「category_id」が100のレコードを検索したいとき
	 *          $QB->findBy ('category_id', 100);
	 *          //発行されるSQL
	 *          //SELECT * FROM "item" WHERE ( "item"."category_id" = "100" ) LIMIT 1
	 *
	 *          //「category_id」が1,10,100のレコードを検索したいとき
	 *          $QB->findBy ('category_id',[1,10,100]);
	 *          //SELECT * FROM "item" WHERE ( "item"."category_id" IN ("1", "10", "100") ) LIMIT 1
	 *
	 *          //「category_id」が100, 「name」が「定規」のレコードを検索したいとき
	 *          $QB->findBy (['category_id' => 100, 'name' => '定規');
	 *          //SELECT * FROM "item" WHERE ( "item"."category_id" = "100" and "item"."name" = "定規" ) LIMIT 1
	 *
	 *          //「category_id」が1,10,100, 「name」が「定規」のレコードを検索したいとき
	 *          $QB->findBy (['category_id' => [1,10,100], 'name' => '定規');
	 *          //SELECT * FROM "item" WHERE ( "item"."category_id" IN ("1", "10", "100") and "item"."name" = "定規" ) LIMIT 1
	 */
	public function findBy ($field, $value)
	{
		if (false !== $this->limit(1)->findAllBy ($field, $value)) {
			return $this->_data[0];
		}
		return false;
	}

	/**
	 * カラムの値を指定してすべてのレコードを取得します。
	 *
	 * @param string $field 検索したいカラム名、又は検索条件の配列
	 * @param mixed $find 検索したいカラムの値・配列
	 * @example //「category_id」が100のレコードを検索したいとき
	 *          $QB->findAllBy ('category_id', 100);
	 *          //発行されるSQL
	 *          //SELECT * FROM "item" WHERE ( "item"."category_id" = "100" )
	 *
	 *          その他も、findByと同じです。
	 */
	public function findAllBy ($field, $value)
	{
		if (is_string($value) or is_numeric($value)) {
			$this->where($field, '=', $value, 'AND' , true);
		} elseif (is_array($value)){
			$this->whereIn($field, $value);
		} elseif (is_array($field)) {
			foreach ($field as $field_name => $value_text) {
				if (is_array($value_text)) $this->whereIn($field_name, $value_text);
				else $this->whereIn($field_name, $value_text);
			}
		}

		$this->get();
		if (isset($this->_data[0])) {
			return $this->_data;
		}
		return false;
	}


	/**
	 * カラムの値を指定して検索します。
	 * @class hide
	 * @param unknown $find
	 */
	public function findOnly ($value)
	{
		return $this->findOnlyBy ($this->_primary_key, $value);
	}

	/**
	 * 検索条件を指定して最初の1件を取得
	 * @class hide
	 */
	public function findOnlyBy ($field, $value)
	{
		if (is_string($value) or is_numeric($value)) {
			$this->where($field,$value);
		} elseif (is_array($value)){
			$this->whereIn($field, $value);
		}

		return $this->get('', true);
	}



	/**
	 * 検索条件を指定して初めの1件を取得し、1件もなければ作成
	 */
	public function findCreate ($field, $value)
	{
		$result = $this->findOnlyBy ($field, $value);
		if (is_array($result[0])) {
			return $result;
		} else {
			$this->insert();
		}
	}

	/**
	 * SQLを直接指定してクエリを実行します
	 * @param string $sql 実行したいSQL文
	 * @param string|array $bind   バインドを行いたい場合はその配列
	 * @example $QB->findSql("SELECT * FROM \"item\" WHERE ( \"item\".\"id\" = 100 )");
	 */
	public function findSql ($sql, $bind = array())
	{
		if (is_array($bind)) {
			foreach ($bind as $key => $value) {
				$this->setBind($key, $value);
			}
		}
		$this->_sql = $sql;
		return $this->get();
	}

	/**
	 * 指定件数ずつ分割してレコードを取得します。
	 *
	 * 内部では、findBatch()が実行されるごとに、指定件数分のレコードを取得するので、メモリを節約できます。
	 *
	 * @param numeric $each 分割して取得する件数
	 * @param numeric $start レコードの取得開始位置
	 * @example //10件目以降、1000件ずつ取得
	 *          $QB->where('category_id','>','1')->findBatch (1000,10);
	 *          do {
	 *              foreach ($QB->getData() as $row) {
	 *                  //レコードに対する処理内容
	 *              }
	 *              //次の分割へ
	 *          } while ($QB->findBatch());
	 *
	 */
	public function findBatch ($each = null, $start = null)
	{
		if ($each > 0 ) {
			//サブクエリに登録
			$this->setScope('_findBatch');
			$this->get();

			//分割件数
			$this->_each_num = $each;
			$this->_each_start = $start;
			if (!$this->_each_start) $this->_each_start = 0;

			$this->limit($this->_each_start ,$this->_each_num);
			return $this->findSql($this->scope('_findBatch'));
		} elseif ($each === -1 ) {
			//分割件数
			$this->_each_num = '';
			$this->_each_start = '';
			return;
		}else {
			//次の分割へ
			//取得件数のセット
			$this->_each_start += $this->_each_num;
			//前に使用したSQLの取得
			$this->limit($this->_each_start ,$this->_each_num);

			$this->findSql($this->scope('_findBatch'));

			if ($this->_data) {
				return $this->_data;
			} else {
				$this->_each_num = '';
				$this->_each_start = '';
				return false;
			}

		}
	}

	/**
	 * すべてのレコードを取得します。
	 * @example //テーブル内のすべてのレコードを取得
	 *          $QB->all ();
	 *          //発行されるSQL
	 *          //SELECT * FROM "item"
	 *
	 *          //テーブル内のすべてのレコードを取得
	 *          $QB->where('category_id','>','1')->all ();
	 *          //発行されるSQL
	 *          //SELECT * FROM "classes" WHERE  ( "classes"."category_id" > 1)
	 *
	 *
	 */
	public function all()
	{
		return $this->get();
	}


	/**
	 * 先頭のレコードを取得します。
	 * @param string $num 取得件数
	 * @example $QB->first();
	 *          //実行されるSQL
	 *          //SELECT * FROM "item" LIMIT 1
	 *
	 *          $QB->first(10);
	 *          //実行されるSQL
	 *          //SELECT * FROM "item" LIMIT 10
	 */
	public function first ($num = null)
	{
		if (!is_numeric($num) or $num < 1) $num = 1;
		$this->limit($num);
		return $this->get();
	}

	/**
	 * 末尾のレコードを取得します。
	 * @param string $num 取得件数
	 * @example $QB->last();
	 *          //実行されるSQL
	 *          //SELECT * FROM "item" ORDER BY id DESC LIMIT 1
	 */
	public function last ($num = null)
	{
		if (!is_numeric($num) or $num < 1) $num = 1;
		$this->limit($num);
		$this->order('id DESC');
		return $this->get();
	}

	/**
	 * ランダムに指定件数のレコードを取得します。
	 *
	 * 速度を出すために、内部でサブクエリを発行しています。
	 *
	 * @param string $num 取得件数
	 * @example $QB->random();
	 *          //実行されるSQL
	 *          //SELECT * FROM "item" ORDER BY id DESC LIMIT 1
	 */
	public function rand ($num = null)
	{
		if (!is_numeric($num) or $num < 1) $num = 1;
		$this->limit($num);

		if ($this->_db_name === 'mysql') $rand_SQL = "RAND()";
		else $rand_SQL = "RANDOM()";

		$select_text = $this->_create_select();

		$this->_sql = "SELECT {$select_text} FROM \"{$this->_part['table']}\" ,
						(SELECT \"{$this->_part['table']}\".\"{$this->_primary_key}\" FROM \"{$this->_part['table']}\" ORDER BY {$rand_SQL} LIMIT 0 , {$num}) AS rand_table
						WHERE  \"{$this->_part['table']}\".\"{$this->_primary_key}\" = rand_table.\"{$this->_primary_key}\"";
		return $this->get();
	}

	/**
	 * 引数で指定した要素数を取得
	 */
	private function take ($limit = null)
	{
		return $this->get($limit);
	}



	/**
	 * 検索結果の行数を取得します。
	 *
	 * @param string $col_name 指定するカラム名
	 * @param string $as_name  カラム名を別名で指定する
	 * @example $QB->count('id');
	 *          //実行されるSQL
	 *          //SELECT COUNT("id") FROM "item"
	 */
	public function count ($col_name = null,$as_name = 'count')
	{
		$table_name = $this->_part['table'];
		if ($col_name !== null) $col_name = "\"{$table_name}\".\"{$col_name}\"";
		else $col_name = "*";
		$part = "COUNT({$col_name}) as \"{$as_name}\"";

		return $this->_findCalculate ($part, $as_name);
	}

	/**
	 * カラムの平均値を求め取得します。
	 *
	 * @param string $col_name 指定するカラム名
	 * @param string $as_name  カラム名を別名で指定する
	 * @example $QB->ave('id');
	 *          //実行されるSQL
	 *          //SELECT AVG("id") FROM "item"
	 */
	public function ave ($col_name, $as_name = 'ave')
	{
		$table_name = $this->_part['table'];
		$part = "AVG(\"{$table_name}\".\"{$col_name}\") as \"{$as_name}\"";

		return $this->_findCalculate ($part, $as_name);
	}

	/**
	 * カラムの最大値を求め取得します。
	 *
	 * @param string $col_name 指定するカラム名
	 * @param string $as_name  カラム名を別名で指定する
	 * @example $QB->max('id');
	 *          //実行されるSQL
	 *          //SELECT MAX("id") FROM "item"
	 */
	public function max ($col_name, $as_name = 'max')
	{
		$table_name = $this->_part['table'];
		$part = "MAX(\"{$table_name}\".\"{$col_name}\") as \"{$as_name}\"";

		return $this->_findCalculate ($part, $as_name);
	}

	/**
	 * カラムの最小値を求め取得します。
	 * @param string $col_name 指定するカラム名
	 * @param string $as_name  カラム名を別名で指定する
	 * @example $QB->min('id');
	 *          //実行されるSQL
	 *          //SELECT MIN("id") FROM "item"
	 */
	public function min ($col_name, $as_name = 'min')
	{
		$table_name = $this->_part['table'];
		$part = "MIN(\"{$table_name}\".\"{$col_name}\") as \"{$as_name}\"";

		return $this->_findCalculate ($part, $as_name);
	}


	/**
	 * カラムの合計値を求め取得します。
	 * @param string $col_name 指定するカラム名
	 * @param string $as_name  カラム名を別名で指定する
	 * @example $QB->sum('id');
	 *          //実行されるSQL
	 *          //SELECT SUM("id") FROM "item"
	 */
	public function sum ($col_name, $as_name = 'sum')
	{
		$table_name = $this->_part['table'];
		$part = "SUM(\"{$table_name}\".\"{$col_name}\") as \"{$as_name}\"";

		return $this->_findCalculate ($part, $as_name);
	}

	/**
	 * 集計系のfindメソッドを処理します。
	 *
	 * @param string $col_name 指定するカラム名
	 * @example $QB->count('id');
	 *          //実行されるSQL
	 *          //SELECT COUNT("id") FROM "item"
	 */
	protected function _findCalculate ($part, $as_name)
	{
		$this->selectSql($part);

		$this->get();

		if (isset($this->_data[0]->$as_name)) return $this->_data[0]->$as_name;

		return $this->_data;
	}

	/**
	 * 指定したカラムの配列を取得します。
	 *
	 * 引数には、1カラム名、カラム名の配列、又は可変長引数での指定ができます。
	 *
	 * @param string $col_name カラム名の値、又は配列
	 * @example 下の2つの引数は同じ意味となります。
	 *          $QB->pluck('id','name','title');
	 *          $QB->pluck(['id','name','title']);
	 *
	 *          //実行されるSQL
	 *          //SELECT "id" , "name" FROM "item"
	 */
	public function pluck ($col_name)
	{
		if (is_array($col_name)) {
			$params = $col_name;
		} else {
			$params = func_get_args();
		}

		$count = 0;
		foreach ($params as $param) {
			 $this->select($param);
			 ++$count;
		}

		$this->_get();

		$list = array();
		//カラム数がひとつの場合
		if ($count === 1) {
			foreach ($this->_data as $value) {
				$list[] = $value->{$params[0]};
			}
		} else {
		//複数
			foreach ($this->_data as $key => $array) {
				$list[$key] = array();
				foreach ($array as $field => $value) {
					$list[$key][] = $value;
				}
			}
		}
		return $list;

	}



	/**
	 * SQLを結合します。
	 *
	 * @param array  $sql_name 結合したいサブクエリの名前の配列
	 * @param string $type 結合方法
	 * @example //クエリ1
	 *          $QB->sub('one')->from('cat')->where('id','1000')->all();
	 *          //クエリ2
	 *          $QB->sub('two')->from('dog')->findBy('name','won!');
	 *          //クエリ3
	 *          $QB->sub('three')->from('panda')->order('id')->all();
	 *
	 *          //結合し実行
	 *          $QB->union(['one','two','three']);
	 *
	 *          //実行されたSQ
	 *          //SELECT * FROM "cat" WHERE ( "cat"."id" = :id ) UNION SELECT * FROM "dog" WHERE ( "dog"."name" = :name ) UNION SELECT * FROM "panda" ORDER BY id
	 */
	public function union ($sql_name, $type = 'UNION')
	{
		if (is_array($sql_name)) {
			$sql = null;
			foreach($sql_name as $name) {
				if ($this->scope($name)) {
					if ($sql) $sql .= " {$type} ";
					$sql .= $this->scope($name);
				} else {
					throw new \PDOException ("指定されたサブクエリ「{$name}」がありません．<br>");
				}
			}
			$this->_sql = $sql;
		}
		return $this->get();
	}

	/**
	 * SQLを結合します。
	 *
	 * unionメソッドの第二引数が'UNION ALL'に固定されているメソッドです。
	 *
	 * @param array  $sql_name 結合したいサブクエリの名前の配列
	 *
	 * @example //結合して実行
	 *          $QB->unionAll(['one','two','three']);
	 */
	public function unionAll ($sql_name)
	{
		return $this->union($sql_name, 'UNION ALL');
	}

}

