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

namespace feke\util;

use feke\config\coreConfig as CC;
use \Feke as Feke;

/**
 * ユーザーの認証を行うクラスです。
 *
 * sessionを使用したFeke framework 用のauthです．
 *
 * **プラグインの読み込み**
 *
 * プラグインとして読み込む場合は、インポートが必要です。
 * ※Controller,Modelbaseには読み込まれています。
 * {{{php|
 * use \plugin\Auth;
 * }}}
 *
 * **認証用Controllerの作成**
 * {{{php|
 * class LoginC extends ClassBase
 * {
 *     //POSTがあった場合、ログインを試みる
 *     public function LoginPost ()
 *     {
 *         if ($this->Auth->login ()) {
 *             //ログイン成功時のコード
 *             //ログイン成功時にページ移行
 *             $this->jump('');
 *         } else {
 *             //ログイン失敗時のコード
 *         }
 *     }
 *
 *     //ログインフォーム
 *     public function LoginAction ()
 *     {
 *         //ログインフォームを表示させる
 *     }
 *
 *     //ログアウトアクション
 *     public function LooutAction ()
 *     {
 *         $this->Auth->logout();
 *         $this->jump('login');
 *     }
 * }
 * }}}
 *
 * @package    Feke
 * @subpackage util
 * @plugin \plugin\Auth
 * @config /util/auth
 *
 **/
class Auth
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * セッションプラグイン
	 */
	use \plugin\Session;



	/**
	 * ログイン中のユーザ情報
	 * @var unknown
	 */
	protected $_user_data;

	/**
	 * 閲覧制限用
	 * @var string
	 */
	protected $_limit_key;

	/**
	 * 設定用配列
	 * @var object
	 */
	protected $_Config = array();

	/**
	 * データベースのコネクション
	 * var object
	 */
	protected $_QB;

	/**
	 * コンストラクター
	 * @class hide
	 * @param string $parm
	 * @param string $conection
	 */
	public function __construct()
	{
		$this->usePlugin(__CLASS__);

		$this->_Config = \Feke::loadConfig('/util/auth', true);
		
		$this->_QB = \Feke::load('QueryBuilder','util');
		//接続
		$this->_QB->connect ($this->_Config->db_connection);

		//操作テーブルの設定
		$this->_QB->from ($this->_Config->table_name);
	}


	/**
	 * ユーザーのログイン処理
	 *
	 * セッションを使用して，ログイン処理を行います．
	 *
	 *
	 * セッションでは,fekeauth配列を作成し，
	 * 主にログイン状態と，ひとつのユニークな情報を保持します．
	 *
	 * なお，sessionの管理については，フレームワークの設定に依存します．
	 *
	 * @param  string $username
	 * @param  string $password
	 * @return ログインに成功時はtrueを、失敗した時はfalseを返します。
	 */
	public function login ($username = null, $password = null)
	{
		//ユーザー名の取得
		if (isset($_POST[$this->_Config->username_post]) and !$username) {
			$username = $_POST[$this->_Config->username_post];
		}
		//パスワードの取得
		if (isset($_POST[$this->_Config->password_post]) and !$password) {
			$password = $_POST[$this->_Config->password_post];
		}
		

		$user_data = $this->_QB->findBy ($this->_Config->login_clam, $username);
		
		if (isset($user_data) and $user_data) {
			//パスワードのハッシュ化
			$password = \feke\util\Hash::getHash ($password);
			//パスワードが一致したら
			if ($user_data->{$this->_Config->password_clam} === $password) {
				$this->Session->set ('fekeauth.login', true);
				$this->Session->set ('fekeauth.unique', $username);
				
				//ユーザーデータの保存
				$this->_user_data = $user_data;

				return true;
			} else {
				$this->Session->set ('fekeauth.login', 0);
			}
		} else {
			$this->Session->set ('fekeauth.login', 0);
		}
		return false;
	}

	/**
	 * ログアウト処理を行います。
	 */
	public function logout ()
	{
		$this->Session->destroy();
	}

	/**
	 * ログイン状態の取得をします。
	 *
	 * @return ログイン状態の場合はtrueを、その他の場合は，falseを返します．
	 */
	public function check ()
	{
		$flag = $this->Session->get('fekeauth.login');
		
		if ($flag === true) {
			//ユーザーの識別情報を取得
			$unique = $this->Session->get('fekeauth.unique');

			//ユーザーデータの取得
			if (!$this->_user_data) {
				$user_data = $this->_QB->findBy ($this->_Config->login_clam, $unique);
				if (isset($user_data)) {
					//ユーザーデータの保存
					$this->_user_data = $user_data;
				} else {
					return false;
				}
			}
			//権限の確認
			if ($this->_Config->group_clam and $this->_limit_key > $this->_user_data->{$this->_Config->group_clam}) {
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * ログインユーザー情報の取得をします。
	 *
	 * @param string $field 取得したいユーザーの情報のカラム名
	 */
	public function userData ($field = null)
	{
		if (isset($this->_user_data->{$field})) {
			return $this->_user_data->{$field};
		} elseif ($field !== null) {
			return null;
		}
		return $this->_user_data;
	}

	/**
	 * 閲覧制限をかけます。
	 *
	 */
	public function limit ($key)
	{
		$this->_limit_key = $key;
	}
}