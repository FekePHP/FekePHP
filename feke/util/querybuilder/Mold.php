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
 * 取得データを加工するトレイトです。
 *
 * 主に、find系メソッドで取得したデータを加工し、値を返します。
 *
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */

trait Mold
{
	/**
	 * 取得したデータを逆順に並べます。
	 *
	 * @return 逆順に並べた後の取得したデータ
	 * @example $QB->all();
	 *          dump($QB->reverseOrder()->getData());
	 */
	public function reverseOrder ()
	{
		krsort($this->_data);
		return $this->_data;
	}

	/**
	 * 取得したデータ全体にクロージャーを走らせます
	 *
	 * @param call   $callback コールバック
	 * @param boolen $over_write trueの場合は、取得データを上書きします。
	 * @return クロージャー適用後のクエリで取得したデータ
	 *
	 * @example //取得したレコードに実行するクロージャー
	 *          //レコードの配列を反対にしています。
	 *
	 *          $function = function ($data) {
	 *              $new_data = array();
	 *              //レコードの総数を取得
	 *              $count = count($data);
	 *              foreach ($data as $key=>$obj) {
	 *                  $new_data[$count - $key + 1] = $obj;
	 *              }
	 *              return $new_data;
	 *          };
	 *          $QB->all ();
	 *          $QB->proofData ($function, true);
	 */
	public function proofData ($callback, $over_write = true)
	{
		if (is_callable($callback)) {
			if (!is_array($this->_data)) return null;
			if ($over_write) {
				$this->_data = call_user_func($callback, $this->_data);
				return $this->_data;
			} else {
				return call_user_func($callback, $this->_data);
			}
		}
		return false;
	}

	/**
	 * 取得したレコード全てにクロージャーを走らせます
	 *
	 * @param call   $callback コールバック
	 * @param boolen $over_write trueの場合は、取得データを上書きします。
	 * @return クロージャー適用後のクエリで取得したデータ
	 *
	 * @example //取得したレコードに実行するクロージャー
	 *          //レコードの内容をすべて表示します。
	 *
	 *          $function = function ($record) {
	 *              $list = "<ul>";
	 *              foreach ($record as $key=>$value) {
	 *                  $list .= "<li>{$key}:{$value}</li>";
	 *              }
	 *              $list .= "</ul>";
	 *
	 *              echo $list;
	 *          };
	 *          $QB->all ();
	 *          $QB->proofRecord ($function);
	 */
	public function proofRecord ($callback, $over_write = true)
	{
		$new_data = array();
		if (is_callable($callback)) {
			if (!is_array($this->_data)) return null;
			foreach ($this->_data as $key => $record) {
				if ($over_write) $this->_data[$key] = call_user_func($callback, $record);
				else $new_data[$key] = call_user_func($callback, $record);
			}
			if ($over_write) return $this->_data;
			else return $new_data;
		}
		return false;
	}

	/**
	 * 取得した値ごとにクロージャーを走らせます。
	 *
	 * @param call   $callback コールバック
	 * @param boolen $over_write trueの場合は、取得データを上書きします。
	 * @return クロージャー適用後のクエリで取得したデータ
	 *
	 * @example //取得したレコードに実行するクロージャー
	 *          //すべての値にnullを上書きします。
	 *
	 *          $function = function ($cell) {
	 *              $cell = null;
	 *          };
	 *          $QB->all ();
	 *          $QB->proofCell ($function, true);
	 */
	public function proofCell ($callback, $over_write = true)
	{
		$new_data = array();
		if (is_callable($callback)) {
			if (!is_array($this->_data)) return null;
			foreach ($this->_data as $key => $record) {
				if (!$over_write) $new_data[$key] = new \stdClass;
				foreach ($record as $col_name => $value) {
					if ($over_write) $this->_data[$key]->$col_name = call_user_func($callback, $value);
					else $new_data[$key]->$col_name = call_user_func($callback, $value);
				}
			}
			if ($over_write) return $this->_data;
			else return $new_data;
		}
		return false;
	}

	/**
	 * 取得したカラムごとにクロージャーを走らせます。
	 *
	 * @param call   $callback コールバック
	 * @param boolen $over_write trueの場合は、取得データを上書きします。
	 * @return クロージャー適用後のクエリで取得したデータ
	 * @example //取得したレコードに実行するクロージャー
	 *          //カラム名が'price'だった時に、取得した値に「円」を付け加えます。
	 *
	 *          $function = function ($col_name, $value) {
	 *              if ($col_name == 'price') $value = "$value 円";
	 *          };
	 *          $QB->all ();
	 *          $QB->proofCol ($function, true);
	 */
	public function proofCol ($callback, $over_write = true)
	{
		$new_data = array();
		if (is_array($callback)) {
			if (is_callable($func)) {
				if (!is_array($this->_data[0]->{$key})) return null;
				foreach ($this->_data as $key => $record) {
					if (!$over_write) $new_data[$key] = new \stdClass;
					foreach ($record as $col_name => $value) {
						//カラムに対応したコールバックがなければ
						if (!isset($callback[$col_name])) continue;

						if ($over_write) $this->_data[$key]->$col_name = call_user_func($callback[$col_name], $col_name, $value);
						else $new_data[$key]->$col_name = call_user_func($callback[$col_name], $col_name, $value);
					}
				}
			}
			if ($over_write) return $this->_data;
			else return $new_data;
		}
		return false;
	}
}

