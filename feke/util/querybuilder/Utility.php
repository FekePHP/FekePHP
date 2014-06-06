<?php
/**
 * Part of the Feke framework.
 *
 * @package Feke
 * @author Shohei Miyazawa
 * @since PHP 5.3
 */
namespace feke\util\querybuilder;

use feke\config\CoreConfig as C;
/**
 * QueryBuilderが使用するメソッドの集合です。
 *
 * 主にクエリビルダー内部で使用するメソッド郡です。
 *
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */


trait Utility
{
	/**
	 * テーブル名とカラム名のエスケープを行います。
	 */
	protected function _e ($text)
	{
		return (string)str_replace(['"',';'], '', $text);
	}

	/**
	 * テーブル名とカラム名のエスケープを行います。
	 */
	public function e ($text)
	{
		return (string)str_replace(['"',';',"'"], '', $text);
	}

	/**
	 * where・like生成のメソット
	 *
	 * @param string|array $field   フィールド名
	 * @param string|array $value   値
	 * @param string|array $symbol  比較(=,<,>,...)
	 * @param string|array $connect 接続(AND/OR)
	 * @return SqlSet
	 */
	protected function _set_where ($field, $value, $symbol, $connect, $top = null)
	{
		$symbol = $this->_e($symbol);
		$connect = $this->_e($connect);

		if (is_array($field)) {
			foreach ($field as $que) {
				//比較初期値
				$symbol_text = null;
				if (isset($que['symbol'])) $symbol_text = $this->_e($que['symbol']);

				if (!$symbol_text) {
					if ($symbol) {
						$symbol_text = $symbol;
					} else {
						$symbol_text = '=';
					}
				}

				$connect_text = null;
				if (isset($que['connect'])) $connect_text = $this->_e($que['connect']);

				if (!$connect_text) {
					if ($connect) {
						$connect_text = $connect;
					} else {
						$connect_text = 'AND';
					}
				}

				$col_name = null;
				if (isset($que['field'])) {
					$col_name = $this->_e($que['field']);
				}
				else return $this->throwError ('カラム名の指定は必須です。');

				$bind_name = $this->bindCheck($col_name);

				//クラス変数へ挿入
				$this->_part['where'][] = array (
						'field' => $col_name ,
						'value' => ":{$bind_name}" ,
						'symbol' => $symbol_text ,
						'connect' => $connect_text,
				);
				//bindValueを登録
				$this->setBind ($bind_name, $que['value']);

			}
		} else {
			//比較初期値
			if (!$symbol) $symbol = $symbol;
			if (!$connect) $connect = $connect;

			if (!$symbol) $symbol = '=';
			if (!$connect) $connect = 'AND';

			$field = $this->_e($field);
			if (!$field) return $this->throwError ('カラム名の指定は必須です。');

			$bind_name = $this->bindCheck($field);

			//クラス変数へ挿入
			if (!$top or empty($this->_part['where'])) {
				$this->_part['where'][] = array (
						'field' => $field ,
						'value' => ":{$bind_name} " ,
						'symbol' => $symbol,
						'connect' => $connect,
				);
			} else {
				$arrays = array (
						'field' => $field ,
						'value' => ":{$bind_name} " ,
						'symbol' => $symbol,
						'connect' => $connect,
				);
				//配列の先頭に追加
				array_unshift($this->_part['where'], $arrays);
			}

			//bindValueを登録
			$this->setBind ($bind_name, $value);
		}
		return $this;
	}

	/**
	 * 変数のリセット
	 *
	 * _bind と
	 *
	 */
	protected function reset ()
	{
		//テーブル名のみ維持
		if(!empty($this->_part['table'])) {
			$table = $this->_part['table'];
		}

		//クエリの表示
		$this->debug ();

		//一応保存
		$this->_old_part = $this->_part;

		//リセット
		$this->_part = array();
		$this->_sql =  null;
		unset($this->_limit_num);
		unset($this->_limit_start);
		$this->_count = 0;

		//バインドのリセット
		foreach ($this->_bind as $key => $value) {
			if ($this->_bind_save[$key] === true) continue;
			unset($this->_bind[$key]);
			unset($this->_bind_save[$key]);
			unset($this->_bind_type[$key]);
		}

		//テーブル名のみ維持
		if(!empty($table) or $this->_main_table) {
			if ($this->_main_table) {
				$this->_part['table'] = $this->_main_table;
			} else {
				$this->_part['table'] = $table;
			}
		}
		return $this;
	}

	/**
	 * すべてをリセットします。
	 *
	 * @class hide
	 */
	public function resetAll ()
	{
		$this->_part = array();
		$this->_bind = array();

		return $this;
	}


	/**
	 * クエリのデバックに使用します。
	 *
	 * @class hide
	 */
	public function debug ()
	{
		//デバック用の操作
		if (debug_level() != 0) {
			$list = array();
			$line = null;
			foreach ($this->_bind as $key => $bind) {
				if (strpos($this->_sql,":{$key}") !== false) $list[$key] = $bind;
			}
			\feke\core\Debug::setBindList($list);

			foreach (debug_backtrace() as $value) {
				if(strpos(($value['class']) ,'feke\util\querybuilder') === false) break;
				if (isset($value['line'])) $line = $value['line'];
			}
			$array = ['sql' => $this->_sql, 'line' => $line] + $value;
			\feke\core\Debug::setSqlList($array);
		}
	}


	/**
	 * selectを生成
	 * @return \feke\util\sql\Select
	 */
	protected function _create_select ()
	{
		$print_table = "\"{$this->_part['table']}\".";

		$select = null;
		//カラム指定の有無
		if (empty($this->_part['select']) and empty($this->_part['select_by_sql'])) {
			$select = '*';
		} else {
			if (isset($this->_part['select_by_sql']) and is_array($this->_part['select_by_sql'])) {
				if (isset($this->_part['select']) and is_array($this->_part['select'])) $this->_part['select'] += $this->_part['select_by_sql'];
				else $this->_part['select'] = $this->_part['select_by_sql'];
			}
			foreach ($this->_part['select'] as $key => $field) {
				if ($select) $select .=	', ';

				if (isset($this->_part['select_by_sql'][$key])) {
					$select .= $field;
				} else {
					$select .= $field;
				}
			}
		}
		return $select;
	}

	/**
	 * group_byを生成
	 * @return \feke\util\sql\Select
	 */
	protected function _create_group_by ()
	{
		$print_table = "\"{$this->_part['table']}\".";

		$text = null;
		//group_byの有無
		if (empty($this->_part['group_by'])) {
			return $this;
		} else {
			foreach ($this->_part['group_by'] as $field) {
				if ($text) $text .=	', ';
				$text .= "{$print_table}\"{$field}\"";
			}
		}
		$this->_sql .= " GROUP BY {$text}";

		return $this;
	}

	/**
	 * joinを生成
	 * @return \feke\util\sql\Select
	 */
	protected function _create_join ()
	{
		if (isset($this->_part['join'])) {
			foreach ($this->_part['join'] as $join) {
				$this->_sql .= $join;
			}
		} else {
			return false;
		}

		return $this;
	}

	/**
	 * orderを生成
	 * @return \feke\util\sql\Select
	 */
	protected function _create_order ()
	{
		$print_table = null;
		/*if ($table_name) {
			$print_table = "\"{$table_name}\".";
		} else {
			$print_table = null;
		}*/

		if (isset($this->_part['order'])) {
			foreach ($this->_part['order'] as $value) {
				if (!isset($order)) $order = " ORDER BY ";
				else $order .= ", ";
				$order .= "{$print_table}{$value}";
			}
			if (isset($order)) $this->_sql .= $order;
		} else {
			return false;
		}

		return $this;
	}

	/**
	 * where句,having句の作成をします。
	 *
	 * セットされれた配列にしたがって，WHEREを生成
	 */
	protected function _create_where ($having_fg = false)
	{
		$print_table = "\"{$this->_part['table']}\".";

		//having用
		if ($having_fg) {
			$key_name = "having";
		} else {
			$key_name = "where";
		}

		$where = '';

		if (isset($this->_part[$key_name][0])) {
			$or_flag = false;
			foreach ($this->_part[$key_name] as $key => $que) {
				if (!$where) {
					$where = " ".strtoupper($key_name)." ";
					if (!$or_flag) {
						$where .= ' ( ';
						$or_flag = true;
					}
				} else {
					if ($que['connect'] == 'OR') {
						if (!$or_flag) {
							$where .= " {$que['connect']} ";
							$or_flag = true;
						} else {
							$where .= " {$que['connect']} ";
						}
					} else {
						if ($que['connect'] == 'AND' and $or_flag == true) {
							$where .= ' ) ';
						}
						$where .= " {$que['connect']} ";
						if(isset($this->_part[$key_name][$key + 1]['connect'])) {
							if ($que['connect'] == 'AND' and $this->_part[$key_name][$key + 1]['connect'] == 'OR') {
								$where .= ' ( ';
							}
						}
						$or_flag = false;

					}
				}

				//where IN 用
				if ($que['symbol'] == 'IN') {
					$where .= "{$print_table}\"{$que['field']}\" IN ({$que['value']}) ";
				//where_by SQL 用
				} elseif ($que['symbol'] == 'NOT IN') {
					$where .= "{$print_table}\"{$que['field']}\" NOT IN ({$que['value']}) ";
				//where_by SQL 用
				} elseif ($que['symbol'] == 'SQL') {
					$where .= "{$que['field']} ";
				//その他
				} else {
					//having用
					if ($having_fg and $que['type']) {
						$where .= sprintf("%s(%s\"%s\") %s %s", strtoupper($que['type']), $print_table, $que['field'], $que['symbol'], $que['value']);
					} else {
						$where .= "{$print_table}\"{$que['field']}\" {$que['symbol']} {$que['value']} ";
					}
				}
				$kakko = null;
			}

			if ($or_flag == true) {
				$where .= ' ) ';
			}
		} else {
			return false;
		}

		$this->_sql .= $where;

		return $this;
	}

	/**
	 * where句,having句の作成をします。
	 *
	 * セットされれた配列にしたがって，WHEREを生成
	 */
	protected function _create_having ()
	{
		return $this->_create_where (true);
	}

	/**
	 * limit 句の生成
	 */
	protected function _create_limit ()
	{
		if (isset($this->_part['limit_num']) and is_numeric($this->_part['limit_num']) and isset($this->_part['limit_start']) and is_numeric($this->_part['limit_start'])) {
			$bind_name = $this->bindCheck('limit_num');
			$this->setBind ($bind_name, (int)$this->_part['limit_num'], 'int');
			$this->_sql.= " LIMIT :{$bind_name} ";
			
			$bind_name = $this->bindCheck('limit_start');
			$this->setBind ($bind_name, (int)$this->_part['limit_start'], 'int');
			$this->_sql .= "OFFSET :{$bind_name} ";

		} elseif (isset($this->_part['limit_num']) and is_numeric($this->_part['limit_num'])) {
			$bind_name = $this->bindCheck('limit_num');
			$this->setBind ($bind_name, (int)$this->_part['limit_num'], 'int');
			$this->_sql .= " LIMIT :{$bind_name} ";
		}
	}

	/**
	 * バインディングの実行
	 *
	 * $this->_part['bind']内の配列を
	 * すべてバインディングするメソッド
	 */
	protected function _do_vind()
	{
		if (!empty($this->_bind)) {
			foreach ($this->_bind as $filed => $value) {
				//echo "<br>{$filed} {$value}";
				if (preg_match("/:{$filed}\s/u",$this->_sql)) {
					if ($this->_bind_type[$filed] == 'int') {
						$this->_res-> bindValue( ":{$filed}", $value, \PDO::PARAM_INT);
					} elseif ($this->_bind_type[$filed] == 'str') {
						$this->_res-> bindValue( ":{$filed}", $value, \PDO::PARAM_STR);
					} else {
						$this->_res-> bindValue( ":{$filed}", $value);
					}
				}
			}
		}
	}
}

