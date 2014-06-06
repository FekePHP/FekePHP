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

namespace feke\util;

use feke\config\Config;

/**
 * クエリを半自動で生成するクラスです。
 *
 * 値の挿入は全てバインドし、クエリを実行します。
 * したがって，クエリビルダー使用時は値のエスケープは行っていません．
 *
 * （テーブル名，フィールド名のエスケープは実装しています。）
 *
 * クエリの作成は，メソッドチェーンを利用して作成すると簡単です．
 *
 * >>>このページはとても長くなってしまっているので、メソッドが分類ごとにわかれているtraitリストからもご確認ください。
 *
 * **対応データベース**
 * -MySQL5以上
 * -SQLite3以上
 * -PostgreSQL8以上
 *
 * **サンプルの前提**
 * サンプルで表示されているSQLの例は、バインド済のものを表示しています。
 *
 * **仕様について**
 * 詳細は、この下の''trait''リストにて確認して下さい。
 *
 * **エラーについて**
 * 他のクラスと同様に、エラー発生時は例外''\Error''を投げます。
 * 必要に応じて、キャッチしてご利用ください。
 *
 * **ActiveRecord専用メソッドについて**
 * 一部、ActiveRecord専用メソッド、専用レコード取得方法があります。
 *
 * **実装について**
 * 内部はPDOを使用し動作しています。
 * データベースの種類によって実行されるクエリが大きく異なります。
 *
 * **使用例**
 * {{{php|
 * //インスタンス作成
 * $QB = \Feke::loadUtil('QueryBuilder');
 *
 * //プラグイン読み込み時
 * $this->QB->connect();
 * $this->QB->find(1);
 * }}}
 *
 * **サブクエリについて**
 * サブクエリを含むクエリは、一回sub('クエリ名')メソッドで保存してから、次のクエリで呼び出すことで発行できます。
 *
 * サブクエリのバインド値については、ユーザーがsetBind()で上書きしない限りは、保持され続けます。
 * なお、アンダーバーから始まる名前はフレームワークが使用する場合があるので、指定しないでください。
 *
 * {{{php|
 * //サブクエリ名「sub1」を作成
 * $QB->sub('sub1')->find(1);
 * //サブクエリ名「sub2」を作成
 * $QB->sub('sub2')->find(2);
 * //「sub1」と「sub2」を使用して、サブクエリ名「sub3」を作成
 * $QB->sub('sub3')->union(['sub1','sub2']);
 *
 * //サブクエリ取得メソッドを使用して実行
 * $QB->findSql($this->getSql('sub3'));
 * $QB->where_sub('sub3'))->all();
 *
 * ※意味のないクエリです
 * }}}
 *
 * @package    Feke
 * @subpackage querybuilder
 */

class QueryBuilder
{

	/**
	 * 取得系メソッドについて(find,all,count...)
	 * @load
	 */
	use querybuilder\Find;


	/**
	 * オプション用メソッドについて(where,select,from...)
	 * @load
	 */
	use querybuilder\Option;

	/**
	 * 設定系メソッドについて
	 * @load
	 */
	use querybuilder\Set;

	/**
	 * 挿入系メソッドについて
	 * @load
	 */
	use querybuilder\Insert;

	/**
	 * 更新系メソッドについて
	 * @load
	 */
	use querybuilder\Update;

	/**
	 * 削除系メソッドについて
	 * @load
	 */
	use querybuilder\Delete;

	/**
	 * テーブル・カラム情報取得・操作系メソッドについて
	 * @load
	 */
	use querybuilder\Table;

	/**
	 * トランザクションについて
	 * @load
	 */
	use querybuilder\Transaction;

	/**
	 *  取得系メソッドについて2
	 * @load
	 */
	use querybuilder\Get;

	/**
	 * データ加工系メソッドについて
	 * @load
	 */
	use querybuilder\Mold;

	/**
	 * QueryBuilderが使用する非publicなメソッド群
	 * @load
	 */
	use querybuilder\Utility;

	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * 接続情報
	 */
	protected $_connection_data;
	
	/**
	 * コネクション
	 * @var object
	 */
	protected $_db;

	/**
	 * クエリの結果？
	 * @var object
	 */
	protected $_res;

	/**
	 * SQL文生成用変数
	 * @var string
	 */
	protected $_sql;

	/**
	 * 一つ前のSQL文生成用変数
	 * <<注意>>クエリの全文は、$_do_sqlを参照
	 * @var array
	 */
	protected $_old_sql;

	/**
	 * サブクエリ保存用配列
	 * @var string
	 */
	protected $_sub_sql = array();

	/**
	 * クエリにつける名前
	 * @var string
	 */
	protected $_sub_query = false;

	/**
	* SQL生成用配列
	* テーブル名,where,値等，すべてここに保存
	* @var array
	*/
	protected $_main_table;
	
	/**
	 * スキーマ
	 */
	protected $_schema = 'public';

	/**
	 * 返り値のタイプ
	 * @var array
	 */
	protected $_mode = 'object';

	/**
	 * SQL生成用配列
	 * テーブル名,where,値等，すべてここに保存
	 * @var array
	 */
	protected $_part;

	/**
	 * 一つ前のSQL生成用配列
	 * @var array
	 */
	protected $_old_part;

	/**
	 * 主キー
	 * @var string
	 */
	protected $_primary_key = 'id';

	/**
	 * _auto_incrementの有無
	 * @var boolen
	 */
	protected $_auto_increment = true;

	/**
	 * テーブルのカラム情報を自動で取得します。
	 */
	protected $_auto_setting = false;

	/**
	 * ルールを元にして入力データを検証します。
	 */
	protected $_check_input = false;

	/**
	 * カラムの検証に使用するルール
	 */
	protected $_col_rule = array();

	/**
	 * 現在の行数
	 * @var numeric
	 */
	protected $_row_count = -1;

	/**
	 * 挿入・更新するデータ
	 */
	protected $_new_data = array();



	/**
	 * find_each 用 取得レコード数
	 * @var numeric
	 */
	protected $_each_num;

	/**
	 * find_each 用 取得開始レコード数
	 * @var numeric
	 */
	protected $_each_start;

	/**
	 * limit 用 取得レコード数
	 * @var numeric
	 */
	protected $_limit_num;

	/**
	 * limit 用 取得開始レコード数
	 * @var numeric
	 */
	protected $_limit_start;

	/**
	 * select句の要素数
	 * @var numeric
	 */
	protected $_select_count = 0;

	/**
	 * データベースの種類
	 * @var string
	 */
	protected $_db_name;

	/**
	 * トランザクション
	 */
	protected $_transaction_fg = false;


	/**
	 * クエリの影響があったレコード数
	 */
	protected $_count = 0;

	/**
	 * SELECT系クエリの影響があったレコード数
	 */
	protected $_get_count = 0;

	/**
	 * each()ループフラグ
	 */
	protected $_each_flag = false;

	/**
	 * バインド用配列
	 * テーブル名,where,値等，すべてここに保存
	 * @var array
	 */
	protected $_bind = array();
	protected $_bind_save = array();
	protected $_bind_type = array();

	/**
	 * 保存用SQL文用配列
	 * デバックに使用
	 * @var string
	 */
	public static $_do_sql;


	/**
	 * データベース接続メソッドです。
	 *
	 * 基本は設定CoreConfigに記載せれている設定値を元にデータベースへ接続します。
	 * 接続したい、コネクション名をconnect()へ渡すだけでデータベースとつながります。
	 *
	 * @param string|array $conect CoreConfigに記載されているコネクションを使用する場合は、その変数名を指定し、直接パラメータをして接続したい場合は、配列にて指定するとつながります。
	 * @example // 引数に何を渡さなかった場合は、mainコネクションに接続します。
	 *          $QB->connect();
	 *
	 *          //配列からの接続例
	 *          $array = (
	 *              'TYPE' => 'mysql',
	 *              'HOST' => 'localhost',
	 *              'PORT' => '3306',
	 *              'NAME' => 'fekephp',
	 *              'USER' => 'root',
	 *              'PASS' => 'password',
	 *          );
	 *         $QB->connect($array);
	 */
	public function connect ($conect = 'main')
	{
		//データベース不使用設定の場合はリターン
		if (!\Feke::config('DATABASE')->use) return;

		//接続先データベース
		if (is_array($conect)) {
			$config = $conect;
		}elseif (is_object($conect)) {
			$config = to_array($conect);
		} else {
			$conect = strtoupper($conect);
			$config = to_array(\Feke::config("DB_{$conect}"));
		}

		$config = array_change_key_case($config, CASE_UPPER);
		
		$this->_connection_data = $config;
		
		try {
			if (!$config['NAME']) {
				//対応外のデータベースの場合は，例外を投げる
				//$this->throwError ("データベース名が指定されていません。",true);
			}
			//接続時の初期設定
			$mysql_array = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=\'ANSI_QUOTES\'',
				//フェッチモード
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				//エラーの場合，例外を投げる
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				//バッファードクエリを使用する
				\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			);
			$sqlite_array = array(
				//フェッチモード
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				//エラーの場合，例外を投げる
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			);
			$pro_array = array(
				//フェッチモード
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				//エラーの場合，例外を投げる
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			);
			//MySQL
			if (strtolower($config['TYPE']) == 'mysql') {
				$dsn = sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8',$config['HOST'],$config['NAME'],$config['PORT']);
				$this->_db = new \PDO($dsn, $config['USER'], $config['PASS'], $mysql_array);
				$this->_db_name = $config['TYPE'];
				//引用符の設定
				//$this->_db->query("SET sql_mode='ANSI_QUOTES'");
			//SQLite app_path()補完版
			} elseif (strtolower($config['TYPE']) == 'sqlite_app') {
				$dsn = app_path().$config['NAME'];
				$this->_db = new \PDO('sqlite:'.$dsn,null,null, $sqlite_array);
				$this->_db_name = 'sqlite';
			//SQLite root_path()補完版
			} elseif (strtolower($config['TYPE']) == 'sqlite_root') {
				$dsn = root_path().$config['NAME'];
				$this->_db = new \PDO('sqlite:'.$dsn,null,null, $sqlite_array);
				$this->_db_name = 'sqlite';
			//SQLite srorage_path()補完版
			} elseif (strtolower($config['TYPE']) == 'sqlite_storage') {
				$dsn = storage_path().$config['NAME'];
				$this->_db = new \PDO('sqlite:'.$dsn,null,null, $sqlite_array);
				$this->_db_name = 'sqlite';
			//SQLite
			} elseif (strtolower($config['TYPE']) == 'sqlite') {
				$dsn = $config['NAME'];
				$this->_db = new \PDO('sqlite:'.$dsn,null,null, $sqlite_array);
				$this->_db_name = 'sqlite';
			//PostgreSQL
			} elseif (strtolower($config['TYPE']) == 'postgresql') {
				$dsn = sprintf('pgsql:dbname=%s host=%s port=%s',$config['NAME'],$config['HOST'],$config['PORT']);
				$this->_db = new \PDO($dsn, $config['USER'], $config['PASS'], $pro_array);
				$this->_db_name = $config['TYPE'];
			} else {
				//対応外のデータベースの場合は，例外を投げる
				$this->throwError ("対応していない，データベースです．",true);
			}

		} catch (\PDOException $e) {
			$this->throwError($e->getMessage()."<br>データベースの接続に失敗しました。",true);
		}

	}
	
	/**
	 * 返り値のモードの設定です。
	 *
	 * @class hide
	 * @param string $mode objectを指定した場合はオブジェクト形式、arrayを指定した場合は配列でクエリ結果が返されます。
	 * @example //取得するデータをオブジェクト形式にする。
	 *          $QB->mode('object');
	 */
	public function mode ($mode)
	{
		$this->_mode = strtolower($mode);
		if ($this->_mode == 'object') {
			$this->_db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
		}elseif ($this->_mode == 'array') {
			$this->d_b->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		}
	}

	/**
	 * クエリを実行します。
	 *
	 * @class hide
	 */
	public function run ($type, $findOnly = false)
	{
		$this->_select_count = 0;

		//サブクエリでなければ実行
		if ($this->_sub_query === false) {
			try {
				$this->_res = $this->_db->prepare($this->_sql);

				//SQLを保存
				static::$_do_sql = $this->_sql;

				//プリペアステートメント処理
				$this->_do_vind();

				//SQLを実行
				$result = $this->_res->execute();

			} catch (\PDOException $e) {
				$show_sql = $this->_sql;

				//一応ロールバック
				$this->rollBack ();

				//初期化
				$this->reset();
				$this->throwError($e->getMessage()."<br>SQL : <b>{$show_sql}</b>");
			}

			//初期化
			$this->reset();

			//get用
			if ($type === 'get') {

				$this->_row_count = -1;
				$this->_count = $this->_res->rowCount();

				if ($findOnly === true) return $result;

				$this->_data = $this->_res->fetchAll();

				$this->_count = count($this->_data);
				$this->_get_count = $this->_count;

				if (isset($this->_data[0])) return $this->_data;
				else return false;

			} elseif ($type === 'update' or $type === 'delete' or $type === 'insert') {
				$this->_count = $this->_res->rowCount();
				return true;

			} elseif ($type === 'create') {
				return true;
			} else {
				//その他
				return $result;
			}

		} else {
			//サブクエリを保存
			$this->_sub_sql[$this->_sub_query] = $this->_sql;

			//初期化
			$this->reset();
			$this->_sub_query = false;
		}
	}

	/**
	 * クエリを直接発行します。
	 *
	 * {{{php|
	 * //発行するクエリ
	 * $sql = 'SELECT * FROM "table_name" WHERE "category_id" = :category_id and "id" > :id';
	 * //バインドしたい配列
	 * $array = ['category_id' => 10, 'id' => 100];
	 *
	 * //実行
	 * $result = $QB->query ($sql, $array);
	 * }}}
	 *
	 * @param string $sql_text   実行したいSQL文
	 * @param array $bind_array  プレースホルダーにバインドしたい値の配列
	 */
	public function query ($sql_text, $bind_array = array())
	{
		$this->_sql = $sql_text;
		$result = null;
		try {
			$this->_res = $this->_db->prepare($this->_sql);

			//SQLを保存
			static::$_do_sql = $this->_sql;

			foreach ($bind_array as $key => $value) {
				//プリペアステートメント処理
				$this->_res->bindValue(":{$key}", $value);
			}

			//SQLを実行
			$result = $this->_res->execute();

			try {
				$result = $this->_res->fetchAll();
			} catch (\PDOException $e) {
			}
		} catch (\PDOException $e) {
			$show_sql = $this->_sql;

			//一応ロールバック
			$this->rollBack ();

			//初期化
			$this->reset();
			$this->throwError($e->getMessage()."<br>SQL : <b>{$show_sql}</b>");
		}
		$this->reset();
		return $result;
	}

	/**
	 * 文字列をクォートします。
	 *
	 * @param string $text 対象の文字列
	 */
	public function quote ($text)
	{
		return $this->_db->quote ($text);
	}
}

