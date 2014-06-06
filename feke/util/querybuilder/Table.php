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
 * QueryBuilderの	テーブル・カラム情報取得・操作用メソッドが集まっているトレイトです。
 *
 * このトレイト内にあるメソッドは、他のメソッドとは違い、テーブル名の指定に、「from(), setMainTable()」が適用されない場合があります。
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */
trait Table
{

	/**
	 * データベースのテーブルを作成します。
	 *
	 * カラムについては、配列で属性を指定する方と、SQLを直接指定する2種類の方法が使用できます。
	 *
	 * ***カラム配列の指定方法***
	 * |項目|指定方法|MySQL|SQLite|PostgreSQL|
	 * |!型|'type' => 'int'|○|○|○|
	 * |!長さ|'length' => '100'|○||○|
	 * |!主キー|'primary' => true|○|○|○|
	 * |!インデックス|'index' => true|○|○|○|
	 * |!ユニークインデックス|'uniq' => true|○|○|○|
	 * |!FULLTEXTインデックス|'fulltext' => true|○|||
	 * |!AUTO INCREMENT|'auto' => true または 'auto' => '1000（開始番号）'|○|○|○|
	 * |!NOT NULL|'not_null'|○|○|○|
	 * |!デフォルト|'default' => '指定したい値'|○|○|○|
	 * |!カラムの照合順序|'collate' = '指定したい値'|○|||
	 * |!属性|'property' = '指定したい値'|○|||
	 * |!コメント|'comment' => '指定したい値'|○||○|
	 * |!CHECK制約|'check' => '指定したい値'||○|○|
	 * |!外部キー制約|'foreign' => ['table' => '指定したいテーブル名', 'col' => '指定したいカラム名']<br>指定がなかった場合は、テーブル名にカラム名、カラム名にidが自動で使用されます。|○|>3.6.19|○|
	 *
	 * ***テーブル配列の指定方法***
	 * |項目|指定方法|MySQL|SQLite|
	 * |!AUTO INCREMENT　の開始値|'auto_start' => '指定したい値'|○|○|○|
	 * |!エンジン|'engine' = '指定したい値'|○|||
	 * |!テーブルの照合順序|'collation' = '指定したい値'|○|||
	 * |!IF NOT EXISTS |'if_not_exists' = 'true'|○|○|○|
	 * |!コメント|'comment' => '指定したい値'|○||○|
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name   作成するテーブル名
	 * @param array  $col_data     作成するカラムの配列
	 * @param array  $table_option 作成するテーブルの配列
	 * @param boolen $sql_only     trueの場合はテーブルを作成せずに、生成したCREATE TABLEのSQL文を取得する
	 *
	 * @example //すべて配列で指定する場合
	 *          $array = array(
	 *              'id' => ['type' => 'int', 'auto' => true, 'primary' => true,],
	 *              'name' => ['type' => 'varchar', 'length' => 100, 'not_null' => true,],
	 *              'category_id' => ['type' => 'int', 'length' => 100, 'not_null' => true,],
	 *          );
	 *
	 *          //カラムのクエリ部分を直接指定する場合
	 *          $array = array(
	 *              'id' => 'int PRIMARY KEY AUTO_INCREMENT NOT NULL',
	 *              'name' => 'varchar(100) NOT NULL',
	 *              'category' => 'varchar(100) NOT NULL',
	 *          );
	 *
	 *          $QB->createTable('item', $array);
	 */
	public function createTable ($table_name, $col_data, $table_option = null, $sql_only = null)
	{
		$table_option = to_array($table_option);
		
		$this->_sql = null;
		$after_sql = null;
		
		$index_array = array();
		$primary_array = array();
		$uniq_array = array();
		$fulltext_array = array();
		$foreign_array = array();
		
		foreach ($col_data as $key=>$array) {
			if ($this->_sql !== null) $this->_sql .= ',';

			$this->_sql .= ' "'.$this->_e($key).'" ';

			//テキスト型の場合はそのまま付ける
			if (is_string($array)) {
				$this->_sql .= "{$array}";
				continue;
			} elseif (is_object($array)) {
				$array = to_array($array);
			}

			//型
			if (isset($array['type'])) {
				$this->_sql .= " ".$this->_e($array['type']);
				//最大文字数
				if (isset($array['length']) and is_value($array['length'])) {
					$this->_sql .= "(".$this->_e($array['length']).")";
				}
			}
			
			//property
			if (isset($array['property']) and is_value($array['property'])) {
				$this->_sql .= " {$array['property']}";
			}

			//AUTO_INCREMENT
			if (isset($array['auto']) and is_true($array['auto'])) {
				if ($this->_db_name == 'sqlite') {
					$auto_tetx = 'AUTOINCREMENT';
				} else {
					$auto_tetx = 'AUTO_INCREMENT';
				}
				$this->_sql .= " {$auto_tetx}";
			}
			
			//COLLATE
			if (isset($array['collate']) and is_value($array['collate'])) {
				$this->_sql .= " {$array['collate']}";
			}
			
			//NOT NULL
			if (isset($array['not_null']) and is_true($array['not_null'])) {
				$this->_sql .= " NOT NULL";
			}

			//DEFAULT
			if (isset($array['default'])) {
				$this->_sql .= " DEFAULT ".$this->quote($array['default']);
			}
			
			//COMMENT
			if (isset($array['comment'])) {
				$this->_sql .= " COMMENT ".$this->quote($array['comment']);
			}
			
			
			//property
			if (isset($array['check']) and is_value($array['check'])) {
				$this->_sql .= " CHECK({$array['check']})";
			}
			
			//PRIMARY KEY
			if (isset($array['primary']) and is_true($array['primary'])) {
				$primary_array[] = $key;
			}
			
			//UNIQUE
			if (isset($array['uniq']) and is_true($array['uniq'])) {
				$uniq_array[] = $key;
			}
			
			//INDEX
			if (isset($array['index']) and is_true($array['index'])) {
				$index_array[] = $key;
			}
			
			//FULLTEXT
			if (isset($array['fulltext']) and is_true($array['fulltext'])) {
				$fulltext_array[] = $key;
			}
			
			//FOREIGN KEY
			if (isset($array['foreign']) and is_array($array['foreign'])) {
				$foreign_array[$key] = $array['foreign'];
			}
			
		}
		$if_not_exists = null;
		if (isset($table_option['if_not_exists']) and is_true($table_option['if_not_exists'])) {
			$if_not_exists = "IF NOT EXISTS ";
		}
		
		$this->_sql = "CREATE TABLE {$if_not_exists}\"".$this->_e($table_name)."\" ( ".$this->_sql;
		
		foreach ($primary_array as $field) {
			$this->_sql .= ", PRIMARY KEY (".$this->_e($field).")";
		}
		foreach ($uniq_array as $field) {
			$this->_sql .= ", UNIQUE INDEX UNIQUE_".$this->_e($field)." (".$this->_e($field).")";
		}
		
		foreach ($index_array as $field) {
			$this->_sql .= ", INDEX INDEX_".$this->_e($field)." (".$this->_e($field).")";
		}
		
		foreach ($fulltext_array as $field) {
			$this->_sql .= ", FULLTEXT INDEX FULLTEXT_".$this->_e($field)." (".$this->_e($field).")";
		}
		
		foreach ($foreign_array as $field => $values) {
			if (isset($values['col'])) $col_name = $values['col'];
			else $col_name = "id";
			if (isset($values['table'])) $f_table_name = $values['table'];
			else $f_table_name = $field;
			$this->_sql .= ", FOREIGN KEY (".$this->_e($field).") REFERENCES ".$this->_e($f_table_name)." (".$this->_e($col_name).")";
		}
		
		if (is_object($table_option)) $table_option = to_array($table_option);
		
		//AUTO_INCREMENT初期値
		if (is_numeric($table_option['auto_start'])) {
			$after_sql .= " {$auto_tetx}=".$this->_e($table_option['auto_start']);
		}
		
		if (isset($table_option['engine']) and is_string($table_option['engine']) and $table_option['engine']) {
			$after_sql .= " ENGINE={$table_option['engine']}";
		}
		
		if (isset($table_option['collation']) and is_string($table_option['collation']) and $table_option['collation']) {
			$after_sql .= " COLLATE={$table_option['collation']}";
		}
		
		//COMMENT
		if (isset($table_option['comment']) and is_string($table_option['comment']) and $table_option['comment']) {
			$after_sql .= " COMMENT=\"".$this->_e($table_option['comment'])."\"";
		}
		
		$this->_sql .= ' ) '.$after_sql;
		
		if ($sql_only === true) {
			$sql = $this->_sql;
			$this->_sql = null;
			return $sql;
		}
		return $this->run('create');
	}
	
	/**
	 * テーブルの削除を行います。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name  削除したいテーブル名
	 *
	 * @example //itemテーブルを削除
	 *          $QB->delete_col ('item', 'into', ['type' => 'text'], 'id');
	 */
	public function deleteTable ($table_name)
	{
		$this->_sql = "DROP TABLE ".$this->_e($table_name);
		return $this->run('create');
	}

	/**
	 * インデックスキーの作成をします。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * $col_nameに配列を渡した場合は、複合インデックスを作成します。
	 *
	 * @param string $table_name テーブル名
	 * @param mixed $col_name カラム名、又は配列
	 * @param string $index_name 登録したい名前
	 *
	 * @example //itemテーブルに、index-nameの複合インデックスを作成します。
	 *          $QB->createIndex ('item', ['id','name']);
	 */
	public function createIndex ($table_name, $col_name, $index_name = null)
	{
		$col_list = null;
		if (is_array($col_name)) {
			foreach ($col_name as $name) {
				if ($col_list) $col_list .= ', ';
				$col_list .= "\"{$name}\"";
			}
		} else {
			$col_list = $this->_e($col_name);
		}
		if ($index_name) $index_name = "\"".$this->_e($index_name)."\"";
		$this->_sql = "ALTER TABLE ".$this->_e($table_name)." ADD INDEX {$index_name}({$col_list})";
		return $this->run('create');
	}

	/**
	 * ユニークキーの作成をします。
	 *
	 * $col_nameに配列を渡した場合は、複合ユニークを作成します。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name テーブル名
	 * @param mixed $col_name カラム名、又は配列
	 * @param string $index_name 登録したい名前
	 *
	 * @example //itemテーブルに、nameのインデックスを作成します。
	 *          $QB->createIndex ('item', 'name');
	 *
	 *          //itemテーブルに、in-nameの複合インデックスを作成します。
	 *          $QB->createIndex ('item', ['id','name']);
	 */
	public function createUniq ($table_name, $col_name, $index_name = null)
	{
		$col_list = null;
		if (is_array($col_name)) {
			foreach ($col_name as $name) {
				if ($col_list) $col_list .= ', ';
				$col_list .= "\"{$name}\"";
			}
		} else {
			$col_list = $this->_e($col_name);
		}
		if ($index_name) $index_name = "\"".$this->_e($index_name)."\"";
		$this->_sql = "ALTER TABLE ".$this->_e($table_name)." ADD UNIQUE {$index_name}({$col_list})";
		return $this->run('create');
	}


	/**
	 * インデックスキー、ユニークキーを削除します。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name テーブル名
	 * @param string $index_name インデックス名
	 *
	 * @example //itemテーブルの「name」インデックスを削除します。
	 *          $QB->createIndex ('item', 'name');
	 */
	public function deleteKey ($table_name, $index_name)
	{
		$index_name = "\"".$this->_e($index_name)."\"";
		$this->_sql = "ALTER TABLE ".$this->_e($table_name)." DROP INDEX {$index_name}";
		return $this->run('create');
	}
	
	/**
	 * カラム用のクエリを組み立てます。
	 * @return string
	 */
	private function _array_to_create ($array)
	{
		$sql = null;
		if (is_object($array)) $array = to_array($array);
		
		if (is_array($array)) {
			if (is_string($array)) {
				$sql .= "{$array}";
				continue;
			} elseif (is_object($array)) {
				$array = to_array($array);
			}

			//型
			if (isset($array['type'])) {
				$sql .= " ".$this->_e($array['type']);
				//最大文字数
				if (isset($array['length']) and is_value($array['length'])) {
					$sql .= "(".$this->_e($array['length']).")";
				}
			}
			
			//property
			if (isset($array['property']) and is_value($array['property'])) {
				$sql .= " {$array['property']}";
			}

			//AUTO_INCREMENT
			if (isset($array['auto']) and is_true($array['auto'])) {
				if ($this->_db_name == 'sqlite') {
					$auto_tetx = 'AUTOINCREMENT';
				} else {
					$auto_tetx = 'AUTO_INCREMENT';
				}
				$sql .= " {$auto_tetx}";
			}
			
			//COLLATE
			if (isset($array['collate']) and is_value($array['collate'])) {
				$sql .= " {$array['collate']}";
			}
			
			//NOT NULL
			if (isset($array['not_null']) and is_true($array['not_null'])) {
				$sql .= " NOT NULL";
			}
			
			//DEFAULT
			if (isset($array['default'])) {
				$sql .= " DEFAULT ".$this->quote($array['default']);
			}
			
			//COMMENT
			if (isset($array['comment'])) {
				$sql .= " COMMENT ".$this->quote($array['comment']);
			}
			
			
			//property
			if (isset($array['check']) and is_value($array['check'])) {
				$sql .= " CHECK({$array['check']})";
			}
		}
		return $sql;
	}
	
	/**
	 * カラムの追加を行います
	 *
	 * $placeを指定しなかった場合は一番最後に追加、「first」を指定した場合は先頭に追加、他のカラム名を指定した場合は、指定したカラムのあとにカラムを追加します。
	 * カラム内容の指定をする配列「$col_param」は、''createTable()''の指定方法と同じです。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 * >>また、場所の指定はMySQLのみ対応しています。
	 *
	 * @param string $table_name  テーブル名
	 * @param string $col_name    追加したいカラム名
	 * @param mixed  $col_param   追加するカラムの内容
	 * @param string $place       カラムの追加場所
	 *
	 * @example //itemテーブルに、「into」カラムを追加
	 *          $QB->addCol ('item', 'into', ['type' => 'text'], 'id');
	 */
	public function addCol ($table_name, $col_name, $col_param, $place = null)
	{
		$this->_sql = null;
		$auto_after_sql = null;

		$this->_sql .= ' "'.$this->_e($col_name).'" ';

		//テキスト型の場合はそのまま付ける
		if (is_string($col_param)) {
			$this->_sql .= "{$col_param}";
		} else {
			if ($this->_db_name == 'postgresql') {
				$col_param = to_array($col_param);
				$col_comment = $col_param['comment'];
				unset($col_param['comment']);
			}
			$this->_sql .= $this->_array_to_create ($col_param);
		}
		$this->_sql = "ALTER TABLE \"".$this->_e($table_name)."\" ADD COLUMN ".$this->_sql."";

		//カラムの先頭
		if (strtolower($place) === 'first') {
			$this->_sql .= ' FIRST';
		} elseif (!$place) {
			$this->_sql .= '';
		} elseif (is_string($place)) {
			$this->_sql .= " AFTER \"".$this->_e($place)."\"";
		}
		
		if ($this->_db_name == 'postgresql') {
			$this->query($this->_sql);
			if (isset($col_comment) and is_value($col_comment)) {
				$this->query("COMMENT ON COLUMN\"{$table_name}\".{$col_name} IS ".$this->quote($col_comment));
			}
			return true;
		} else {
			return $this->query($this->_sql);
		}
	}

	/**
	 * カラムの削除を行います
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name  テーブル名
	 * @param string $col_name    削除したいカラム名
	 *
	 * @example //itemテーブルに、「into」カラムを追加
	 *          $QB->delCol ('item', 'into', ['type' => 'text'], 'id');
	 */
	public function delCol ($table_name, $col_name)
	{
		$table_name = $this->_e($table_name);
		if ($this->_db_name == 'sqlite') {
			$col_list = $this->from($table_name)->getColsdata();
			$create_sql = $this->query("select * from sqlite_master where type='table' and name='{$table_name}'");
			$create_sql = $create_sql[0]->sql;
			
			preg_match_all ("/'((?!'').)*?'/",$create_sql, $conma_array);
			
			foreach ($conma_array[0] as $key => $value) {
				$create_sql = preg_replace("/{$value}/", "___put_{$key}___", $create_sql);
			}
			
			//特定行の削除
			$old_create_sql = $create_sql;
			$create_sql = preg_replace("/\"{$col_name}\".*?,/", '', $create_sql);
			if ($create_sql === $old_create_sql) {
				$create_sql = preg_replace("/,\s?\"{$col_name}\".*?[)]/", ')', $create_sql);
				if ($create_sql === $old_create_sql) {
					$this->throwError('削除するカラムが存在しないか、カラムがひとつしか無いため削除できません。');
				}
			}
			
			foreach ($conma_array[0] as $key => $value) {
				$create_sql = preg_replace("/___put_{$key}___/", "{$value}", $create_sql);
			}
			
			preg_match ("/CREATE TABLE \"$table_name\" [(](.*?)[)]/", $create_sql, $m);
			$create_sql = $m[1];
			
			$col_names = null;
			foreach ($col_list as $array) {
				if (isset($array->field) and $array->field !== $col_name) {
					if ($col_names) $col_names.= " , ";
					$col_names .= "\"{$array->field}\"";
				}
			}
			
			$this->transaction ();
			
			$this->query("CREATE TEMPORARY TABLE \"{$table_name}_backup\" ({$create_sql});");
			$this->query("INSERT INTO \"{$table_name}_backup\" SELECT {$col_names} FROM \"{$table_name}\";");
			$this->query("DROP TABLE \"{$table_name}\";");
			$this->query("CREATE TABLE \"{$table_name}\" ({$create_sql});");
			$this->query("INSERT INTO \"{$table_name}\" SELECT {$col_names} FROM \"{$table_name}_backup\";");
			$this->query("DROP TABLE \"{$table_name}_backup\";");
			
			$this->commit ();
			
			return true;
		} else {
			$col_name = "\"".$this->_e($col_name)."\"";
			$this->_sql = "ALTER TABLE \"{$table_name}\" DROP COLUMN {$col_name}";
			
			return $this->run('create');
		}
	}

	/**
	 * カラムの変更を行います。
	 *
	 * カラム名、型の変更を行うことができます。
	 * 引数は、どちらか一方の指定が必須です。
	 * カラム内容の指定をする配列「$new_param」は、''createTable()''の指定方法と同じです。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name  テーブル名
	 * @param string $col_name    編集したいカラム名
	 * @param string $new_name    新しいカラム名
	 * @param string $new_param   編集したいカラムのオプション
	 *
	 * @example //itemテーブルの、「into」カラムの型を変更
	 *          $QB->editCol ('item', 'into', '', 'vachear(100)');
	 */
	public function editCol ($table_name, $col_name, $new_name = null, $new_param = null)
	{
		$table_name = $this->_e($table_name);
		
		$new_name = "\"".$this->_e($new_name)."\"";
		
		if ($this->_db_name == 'sqlite') {
			$col_list = $this->from($table_name)->getColsdata();
			$create_sql = $this->query("select * from sqlite_master where type='table' and name='{$table_name}'");
			$create_sql = $create_sql[0]->sql;
			//dump($create_sql);exit;
			
			//テキスト型の場合はそのまま付ける
			if (is_array($new_param) or is_object($new_param)) {
				$new_param = $this->_array_to_create ($new_param);
			}
			
			$replace_col_sql = "{$new_name} {$new_param}";
			
			//特定行の削除
			preg_match_all ("/'((?!'').)*?'/",$create_sql, $conma_array);
			
			foreach ($conma_array[0] as $key => $value) {
				$create_sql = preg_replace("/{$value}/", "___put_{$key}___", $create_sql);
			}
			
			$old_create_sql = $create_sql;
			$create_sql = preg_replace("/\"{$col_name}\".*?,/", $replace_col_sql.', ', $create_sql);
			if ($create_sql === $old_create_sql) {
				$create_sql = preg_replace("/\"{$col_name}\".*?[)]/", $replace_col_sql.')', $create_sql);
				if ($create_sql === $old_create_sql) {
					$this->throwError('編集するカラムが存在しないため削除できません。');
				}
			}
			
			foreach ($conma_array[0] as $key => $value) {
				$create_sql = preg_replace("/___put_{$key}___/", "{$value}", $create_sql);
			}
			
			preg_match ("/CREATE TABLE \"$table_name\" [(](.*?)[)]/", $create_sql, $m);
			$create_sql = $m[1];
			
			$col_names = null;
			foreach ($col_list as $array) {
				if (isset($array->field)) {
					if ($col_names) $col_names.= " , ";
					$col_names .= "\"{$array->field}\"";
				}
			}
			$this->transaction ();
			
			$this->query("CREATE TEMPORARY TABLE \"{$table_name}_backup\" ({$create_sql});");
			$this->query("INSERT INTO \"{$table_name}_backup\" SELECT {$col_names} FROM \"{$table_name}\";");
			$this->query("DROP TABLE \"{$table_name}\";");
			$this->query("CREATE TABLE \"{$table_name}\" ({$create_sql});");
			$col_names = str_replace("\"{$col_name}\"",$new_name,$col_names);
			$this->query("INSERT INTO \"{$table_name}\" SELECT {$col_names} FROM \"{$table_name}_backup\";");
			$this->query("DROP TABLE \"{$table_name}_backup\";");
			
			$this->commit ();
			
			return true;
		} elseif ($this->_db_name == 'postgresql') {
			$col_name = "\"".$this->_e($col_name)."\"";
			
			$new_param = to_object($new_param);
	
			if ($col_name == '""') $col_name = null;
			if ($new_name == '""') $new_name = null;
			//カラム変更
			if ($col_name) {
				//カラム名
				if ($col_name !== $new_name) {
					$this->query("ALTER TABLE \"{$table_name}\" RENAME COLUMN {$col_name} TO {$new_name}");
				}
				//型
				if (isset($new_param->type) and is_value($new_param->type)) {
					if (isset($new_param->length) and is_value($new_param->length)) $new_param->type .= "({$new_param->length})";
					$this->query("ALTER TABLE \"{$table_name}\" ALTER COLUMN {$col_name} TYPE $new_param->type");
				}
				//デフォルト値
				if (isset($new_param->default) and is_value($new_param->default)) {
					$this->query("ALTER TABLE \"{$table_name}\" ALTER COLUMN {$col_name} SET DEFAULT ".$this->quote($new_param->default));
				}
				//NOT NULL
				if (isset($new_param->not_null) and is_true($new_param->not_null)) {
					$this->query("ALTER TABLE \"{$table_name}\" ALTER COLUMN {$col_name} SET NOT NULL");
				}
				//コメント
				if (isset($new_param->comment) and is_value($new_param->comment)) {
					$this->query("COMMENT ON COLUMN\"{$table_name}\".{$col_name} IS ".$this->quote($new_param->comment));
				}
			} else {
				return $this->throwError('カラム名の指定は必須です。');
			}
			
			return true;
		}else {
			$col_name = "\"".$this->_e($col_name)."\"";
			
			//テキスト型の場合はそのまま付ける
			if (is_array($new_param) or is_object($new_param)) {
				$new_param = $this->_array_to_create ($new_param);
			}
	
			if ($col_name == '""') $col_name = null;
			if ($new_name == '""') $new_name = null;
			//カラム名変更
			if ($col_name) {
				if (!$new_name) $new_name = $col_name;
				$this->_sql = "ALTER TABLE \"{$table_name}\" CHANGE {$col_name} {$new_name} {$new_param}";
			} else {
				return $this->throwError('新しいカラム名、指定は必須です。');
			}
			
			return $this->run('create');
		}
	}
	
	/**
	 * テーブル名を変更します。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name 変更するテーブル名
	 * @param string $new_name   新しくつけるテーブル名
	 *
	 * @example //itemテーブルのエンジンを変更
	 *          $QB->changeablename ('item', 'itemList');
	 */
	public function changeTablename ($table_name, $new_name)
	{
		$this->_sql = "ALTER TABLE \"".$this->_e($table_name)."\" RENAME TO \"".$this->_e($new_name)."\"";
		return $this->run('create');
	}
	
	/**
	 * MySQLテーブルのストレージエンジンを変更します。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name 変更するテーブル名
	 * @param string $type       データベースのエンジンの種類
	 *
	 * @example //itemテーブルのエンジンを変更
	 *          $QB->changeEngine ('item', 'MyISAM');
	 */
	public function changeEngine ($table_name, $type)
	{
		$this->_sql = "ALTER TABLE \"".$this->_e($table_name)."\" ENGINE = \"".$this->_e($type)."\"";
		return $this->run('create');
	}

	/**
	 * テーブルを空にする
	 *
	 * 指定されたテーブルのレコードを切り捨て、空にします。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name レコードを切り捨てるテーブル名
	 * @example //itemテーブルのレコードをすべて切り捨てます。
	 *          $QB->truncate ('item');
	 */
	public function truncate ($table_name)
	{
		if ($this->_db_name == 'sqlite') {
			$this->_sql = "DELETE FROM \"".$this->_e($table_name)."\"";
		} else {
			$this->_sql = "TRUNCATE \"".$this->_e($table_name)."\"";
		}
		return $this->run('create');
	}
	
/**
	 * テーブルを削除する
	 *
	 * 指定されたテーブルを削除します。
	 *
	 * >>このメソッドは、setMainTable()、及びfrom()で指定されたテーブル名は使用されません。
	 *
	 * @param string $table_name レコードを切り捨てるテーブル名
	 * @example //itemテーブルのレコードをすべて切り捨てます。
	 *          $QB->drop ('item');
	 */
	public function dropTable ($table_name)
	{
		$this->_sql = "DROP TABLE \"".$this->_e($table_name)."\"";
		return $this->run('create');
	}

	/**
	 * 空のレコードのオブジェクトを取得します。
	 *
	 * @return カラム名を持ったオブジェクトを返します。
	 * @example //itemテーブルの空のオブジェクトを作成します。
	 *          $QB->from('item')->none ();
	 */
	public function none()
	{
		return getColsData (true);
	}
	
	/**
	 * テーブルの定義(CREATE TABLE)を取得します。
	 *
	 * @param string $table_name テーブル名
	 * @param boolen $flag       trueの場合は、FekePHPがSQLを生成し、その他の場合はデータベース特有のクエリを発行して取得します。
	 * @return テーブルの定義（SQL）、またはfalseを返します。
	 * @example echo $QB->getCreateTable('item');
	 */
	public function getCreateTable ($table_name, $flag = false)
	{
		if (is_true($flag)) {
			$colsData = $this->getColsData();
			$sql = $this->createTable($table_name, $colsData, null, true);
		} else {
			if ($this->_db_name == 'sqlite') {
				$sql = $this->query("select * from sqlite_master where type='table' and name='{$table_name}'")[0]->sql;
			} elseif ($this->_db_name == 'mysql') {
				$sql = $this->query("SHOW CREATE TABLE \"{$table_name}\"")[0]->{'Create Table'};
			} elseif ($this->_db_name == 'postgresql') {
				$colsData = $this->getColsData();
				$sql = $this->createTable($table_name, $colsData, null, true);
			}
		}
		return $sql;
	}
	
	/**
	 * 主キーを取得します。
	 *
	 * @param string $table_name テーブル名
	 * @return テーブルの主キー、またはfalseを返します。
	 * @example //主キーを取得します。
	 *          echo $QB->getPrimary('item');
	 *
	 */
	public function getPrimary ($table_name = null)
	{
		if ($table_name) $this->from($table_name);

		$list = $this->getColsData();

		foreach ($list as $value) {
			if (!isset($value->key)) continue;
			if (strpos($value->key ,'PRI') !== false) return $value->field;
		}

		return false;
	}

	/**
	 * カラム情報をすべて取得します。
	 *
	 * カラムの情報をオブジェクト形式で取得します。
	 * フィールド名、型、not null、デフォルト値などの情報を取得できます。
	 *
	 * また、第1引数にtrueをセットすることでカラム名のみを配列で取得することも可能です。
	 *
	 * **取得内容**
	 * |~キー名|取得内容|MySQL|SQLite|PostgreSQL|
	 * |field|カラム名|○|○|○|
	 * |type|カラムの型|○|○|○|
	 * |length|長さ|○||○|
	 * |null|NULLの可否|○|○|○|
	 * |default|デフォルト値|○|○|○|
	 * |null|NULLの可否|○|○|○|
	 * |key|主キーの場合はPRI、またその他のインデックス関係の値が挿入されています。|○|○|○|
	 * |extra|その他|○||○|
	 * |comment|テーブルのコメント|○||○|
	 *
	 * @param boolen $name_onry  カラム名のみ取得する
	 * @return カラムの配列を返す。
	 * @example //リストを表示したい場合
	 *          dump($QB->getTableList());
	 *
	 * @link http://d.hatena.ne.jp/ja9/20100830/1283146058 DB内のテーブル名一覧、テーブル情報などを取得するSQL
	 */
	public function getColsData ($name_onry = null)
	{
		$list = array();
		
		if ($this->_db_name == 'mysql') {
			$this->_sql = "SHOW FULL COLUMNS FROM \"{$this->_part['table']}\" ";

			//SQLをセット
			$this->run('get');
			
			foreach ($this->_data as $key => $array) {
				$field = $array->Field;
				$list[$field] = to_object(array_change_key_case(to_array($array)));
			}
			
		} elseif ($this->_db_name === 'sqlite') {
			$this->_sql = "PRAGMA TABLE_INFO( \"{$this->_part['table']}\")";

			//SQLをセット
			$this->run('get');

			//MySQLに合わせる
			foreach ($this->_data as $key => $array) {
				foreach ($array as $key2 => $value) {
					if ($key2 === "name") {
						$key2 = "field";
					} elseif ($key2 === "type") {
						$key2 = 'type';
					} elseif ($key2 === "notnull") {
						$key2 = 'null';
						if ($value) $value = 'NO';
						else $value = 'YES';
					} elseif ($key2 === "pk") {
						$key2 = 'key';
						if ($value) $value = "PRI";
						else $value = null;
					} elseif ($key2 === "dflt_value") {
						//えすけーぷされてる？
						$key2 = 'default';
						$value = str_replace("''", "'", $value);
						$value = preg_replace("/^'|'$/", '', $value);
					}
					$this->_data[$key]->$key2 = $value;
				}
				$this->_data[$key]->length = null;
				$this->_data[$key]->extra = null;
				$this->_data[$key]->comment = null;
			}
			foreach ($this->_data as $key => $array) {
				$list[$array->field] = $array;
			}
		} elseif ($this->_db_name === 'postgresql') {
			$user = $this->_connection_data['USER'];
			//呪文
			$this->_sql =
					'SELECT *,
						pg_attribute.attname       AS field,
						pg_attrdef.adsrc           AS default,
						pg_attribute.attnotnull    AS null,
						CASE pg_type.typname
							WHEN \'int2\'   THEN \'SMALLINT\'
							WHEN \'int4\'   THEN \'INT\'
							WHEN \'int8\'   THEN \'BIGINT\'
							WHEN \'float4\' THEN \'REAL\'
							WHEN \'float8\' THEN \'DOUBLE\'
							WHEN \'bpchar\' THEN \'CHAR\'
							ELSE UPPER(pg_type.typname)
						END AS type,
						
						CASE WHEN pg_attribute.atttypmod > 0 THEN
							CASE pg_type.typname
								WHEN \'numeric\'
									THEN (pg_attribute.atttypmod - 4) / 65536
								WHEN \'decimal\'
									THEN (pg_attribute.atttypmod - 4) / 65536
								WHEN \'date\'
									THEN COALESCE(pg_attribute.atttypmod - 4, 10)
								WHEN \'time\'
									THEN COALESCE(pg_attribute.atttypmod - 4, 8)
								WHEN \'timestamp\'
									THEN COALESCE(pg_attribute.atttypmod - 4, 19)
								ELSE pg_attribute.atttypmod - 4
							END
						END AS length,
						
						CASE
							WHEN pg_index.indisprimary
								THEN \'PRI\'
							WHEN pg_index.indisunique
								THEN \'UNIQUE\'
						END AS key,
						
						pg_description.description as comment
					FROM
						pg_attribute
						INNER JOIN pg_type        ON (pg_attribute.atttypid = pg_type.oid)
						INNER JOIN pg_class       ON (pg_attribute.attrelid = pg_class.oid)
						INNER JOIN pg_namespace   ON (pg_class.relnamespace = pg_namespace.oid)
						LEFT  JOIN pg_index       ON (pg_attribute.attrelid = pg_index.indrelid)
						LEFT  JOIN pg_description ON (pg_attribute.attrelid = pg_description.objoid AND pg_attribute.attnum = pg_description.objsubid)
						LEFT  JOIN pg_constraint  ON (pg_attribute.attrelid = pg_constraint.conrelid)
						LEFT  JOIN pg_attrdef     ON (pg_attribute.attrelid = pg_attrdef.adrelid AND pg_attribute.attnum = pg_attrdef.adnum)
					WHERE
						pg_class.relname = \''.$this->_part['table'].'\'
						AND pg_namespace.nspname = \''.$this->_schema.'\'
						AND pg_class.relkind = \'r\'
						AND pg_attribute.attisdropped IS NOT TRUE
						AND pg_attribute.attnum >= 0';
			
			$this->run('get');
		
			foreach ($this->_data as $key => $array) {
				if (strpos($array->default, "'") === 0) {
					$array->default = preg_replace("/^'|'(.*)+$/",'',$array->default);
				}
				$list[$array->field] = $array;
			}
		}

		//カラム名のみの場合
		if ($name_onry and is_array($this->_data)) {
			$name_list = array();
			foreach ($list as $value) {
				if (isset($value->field)) {
					$name_list[] = $value->field;
				}
			}
			return $name_list;
		}
		
		return $list;
	}

	/**
	 * 指定したカラム情報を取得します。
	 *
	 * 指定されたカラムの情報をオブジェクト形式で取得します。
	 * フィールド名、型、not null、デフォルト値などの情報を取得できます。
	 *
	 * また、第2引数にtrueをセットすることでカラム名のみを配列で取得することも可能です。
	 *
	 * @param boolen $name_onry  カラム名のみ取得する
	 * @return カラムの配列を返す。
	 * @example //カラム「id」の情報を取得したい場合
	 *          echo $QB->from('item')->getColsumn('id');
	 */
	public function getCols ($col_name = null, $name_onry = null)
	{
		$this->_sql = "DESCRIBE \"{$this->_part['table']}\" ";

		//SQLをセット
		$this->run('get');

		//カラム名の指定がある場合
		if ($col_name and is_array($this->_data)) {
			foreach ($this->_data as $value) {
				if ($value->field === $col_name) {
					if ($name_onry) return $value->field;
					return $value;
				}
			}
		}

		return false;
	}

	/**
	 * データベースのテーブルリストを取得します。
	 *
	 * **取得内容**
	 * |~キー名|取得内容|MySQL|SQLite|PostgreSQL|
	 * |name|テーブル名|○|○|○|
	 * |rows|レコードの総数|○|○|○|
	 * |engine|エンジン名|○|||
	 * |collation|照合順序|○|||
	 * |data_length|デーブルのサイズ?|○||○|
	 * |comment|テーブルのコメント|○||○|
	 * |id|デーブルID(oid)|||○|
	 * |user_name|ユーザー名|||○|
	 * |schema_name|スキーマ名|||○|
	 *
	 * @param boolen $name_onry  テーブル名のみ取得する
	 * @return カラムの配列を返す。
	 * @example //カラム「id」の情報を取得したい場合
	 *          echo $QB->from('item')->getColsumn('id');
	 */
	public function getTableList ($name_onry = null)
	{
		$table_list = null;
		if ($this->_db_name == 'mysql') {
			$this->_sql = 'SHOW TABLE STATUS';

			//SQLをセット
			$this->run('get');
			foreach ($this->_data as $key => $table) {
				$table = to_object(array_change_key_case (to_array($table)));
				$table_list[$table->name] = $table;
			}
			$this->_data = $table_list;
			
		} elseif ($this->_db_name === 'sqlite') {

			$this->_sql = "SELECT name as name FROM sqlite_master WHERE type = 'table'";
			//SQLをセット
			$table_list = $this->run('get');
			if (is_array($table_list)) {
				foreach ($table_list as $key => $table) {
					if (isset($table->name)) {
						$table_list[$table->name] = new \stdClass;
						$this->_sql = "select count(*) as count from '{$table->name}'";
						$this->_res = $this->_db->query($this->_sql);
						$table_list[$table->name]->rows = $this->_res->fetchAll()[0]->count;
						$table_list[$table->name]->name = $table->name;
						unset($table_list[$key]);
					}
				}
			}
			$this->_data = $table_list;
		} elseif ($this->_db_name === 'postgresql') {
			$user = $this->_connection_data['USER'];
			$this->_sql =
					'SELECT
						pg_class.relname           as name,
						pg_class.oid               as id,
						pg_class.reltuples         as rows,
						(pg_class.relpages * 8192) as data_length,
						pg_description.description as comment,
						pg_class.reltuples         as rows,
						pg_user.usename            as user_name,
						pg_user.usesysid           as user_id,
						pg_namespace.nspname       as schema_name
					FROM
						pg_class
					INNER JOIN pg_namespace ON (pg_class.relnamespace = pg_namespace.oid)
					INNER JOIN pg_user ON (pg_class.relowner = pg_user.usesysid)
					LEFT JOIN pg_description ON (pg_class.oid = pg_description.classoid and pg_description.objsubid = 0)
					WHERE
						usename = \''.$user.'\'
						AND relkind = \'r\'
					';
			
			$this->run('get');
			
			foreach ($this->_data as $key => $table) {
				if($table->schema_name !== 'public') {
					$table_list["{$table->schema_name}.{$table->name}"] = $table;
				} else {
					$table_list[$table->name] = $table;
				}
			};
			$this->_data = $table_list;
		}

		if ($name_onry and is_array($table_list)) {
			foreach ($table_list as $key => $value) {
				unset($table_list[$key]);
				$table_list[$value->name] = $value->name;
			}
		}
		return $table_list;
	}
}

