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
 * QueryBuilder内で設定を行うメソッドが集まっているトレイトです。
 *
 *
 * @package    Feke
 * @subpackage util.querybuilder
 */

trait Set
{
	/**
	 * 主キーのセットをします。
	 *
	 * find(),join()などで使用する主キーの設定をします。
	 * デフォルトでは、''id''が設定されています。
	 *
	 * @param string $name 主キー
	 * @example //主キーを'id'に設定する
	 *          $QB->setPrimary('id');
	 */
	public function setPrimary ($name)
	{
		$this->_primary_key = $this->_e($name);
	}

	/**
	 * メインテーブルの設定をします。
	 *
	 * メインテーブルを設定すると、クエリの実行をするたびに、メインテーブルが使用するテーブルとしてクエリに組み込まれます。
	 *
	 * @param string $name テーブル名
	 * @example //メインテーブルを'flute'に設定する
	 *          $QB->setMainTable('flute');
	 */
	public function setMainTable ($name)
	{
		$this->_main_table = $name;
		$this->_part['table'] = $this->_e($name);
		return $this;
	}
	
	/**
	 * スキーマの設定をします。
	 *
	 * PostgreSQLのみで動作します。
	 *
	 * @param string $name スキーマ名
	 * @example //を'flute'に設定する
	 *          $QB->setSchema('flute');
	 */
	public function setSchema ($name = null)
	{
		if ($name === null) {
			$name = 'public';
		}
		$this->_schema = $name;
		if ($name !== 'public') {
			$name = "'{$name}',public";
		}
		
		$this->query("set search_path to {$name}");
		
		return $this;
	}


	/**
	 * プリペアステートメントの登録を行います。
	 *
	 * 現在は，whereからのみ呼び出し
	 * 通常は呼び出す必要はないはず
	 *
	 * @param string|array $filed カラム名
	 * @param string       $value 値
	 * @param string       $type  値の型
	 *
	 * @example $QB->bind('name', 'ミク');
	 */
	public function setBind ($filed, $value, $type = null)
	{
		$save = '';

		//サブクエリならバインドの値を保存
		if ($this->_sub_query) $save = true;

		if (is_array($filed)) {
			foreach ($filed as $name => $value) {
				$this->_bind[$name] = $value;
				$this->_bind_save[$name] = $save;
				$this->_bind_type[$name] = $type;
			}
		}else {
			$this->_bind[$filed] = $value;
			$this->_bind_save[$filed] = $save;
			$this->_bind_type[$filed] = $type;
		}
		return $this;
	}

	/**
	 * バインド名の重複を確認します。
	 *
	 * バインドの登録地を上書きを防止するためのメソッドです。
	 * 与えられたパラメータ名がす出来登録されている場合は、''$name_1'',''$name_2''....と末端の数字を加算していきます。
	 *
	 * @param string $name  バインドのパラメータ名
	 * @return string
	 *
	 * @example //nameを登録
	 *          $QB->bind('name');
	 *
	 *          //チェック
	 *          echo $QB->bindCheck('name'); //name_1
	 */
	public function bindCheck ($name)
	{
		if (!isset($this->_bind[$name])) {
			return $name;
		}
		$count = 1;

		do {
			$flag = false;
			$new_name = $name.'_'.$count;
			if (!isset($this->_bind[$new_name])) {
				$flag = true;
			}
			++$count;
		} while ($flag === false);

		return $new_name;
	}
}

