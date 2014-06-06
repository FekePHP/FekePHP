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

/**
 * データの検証を一括で行うクラスです。
 *
 * FekePHPでは、柔軟な検証を行えるよう一部データベースと連携して検証ができるようになっています。
 *
 * -ルールの拡張については、feke\util\validation\ExtendRuleを確認して下さい。
 * **クラスとしての使用例**
 * ''run()''メソッドに配列を渡すことで、簡単に検証を開始することができます。
 * この時、配列は2次元の連想配列配列で指定し、''name''はフォームの名前属性名、''rule''は検証したいルールを入力します。
 * デフォルトでは、検証値は''name''指定にされたPOSTに対応した値が使用されますが、''value''を指定することにより、検証値を変更可能です。
 * {{{php|
 * //インスタンス作成
 * $val = \Feke::load ('Validation','util');
 *
 * //配列で一括指定
 * $array = array(
 *     'fulte' => ['rule' => 'AlphaNumeric'],
 *     'introduction' => ['rule' => 'AllowEmpty|MaxLength:200'],
 *     'id' => ['value' => 1, 'rule' => 'numeric'],
 * );
 *
 * //検証開始
 * if ($val->run($array)) {
 *     //検証が成功した時
 * } else {
 *     //検証が失敗した時
 *     //エラーメッセージ
 *     print_r($val->getError());
 * }
 *
 * }}}
 *
 * **ルールの指定例**
 * ルールは「|」で区切ることによりいくつでも指定することができます。
 * Between、inList、MaxLengthなどルールの指定にオプションが有る場合は、「:」で区切って指定してください。
 * {{{php|
 * //アルファベットのみ
 * 'rule' => 'Alpha'
 *
 * //アルファベットのみかつ100文字以内
 * 'rule' => 'Alpha|MaxLength:100'
 *
 * //ひらがなのみかつ10字以上かつ100文字以内、または空白
 * 'rule' => 'AllowEmpty|Hiragana|Between:10:100'
 *
 * }}}
 * **グローバル関数からの使用例**
 * {{{php|
 * //クラスの呼び出しは必要ありません。
 * //成功時はtrue,失敗時はエラーメッセージを返します。
 * //郵便番号のチェック
 * $value = '111123411';
 * if (true === ($msg = check ($value,'NotEmpty|postal'))) {
 *     echo '成功！';
 * } else {
 *     //エラーメッセージ
 *     echo $msg;
 * }
 * }}}
 *
 *
 *
 * **指定できるルール**
 * |~ルール名|詳細|使用例|
 * |AllowTag|HTMLタグを許可||
 * |AllowEmpty|空白を許可||
 * |Alpha|アルファベットのみ||
 * |AlphaNumeric|半角英数字のみ||
 * |AlphaNumericBar|半角英数字アンダーバーのみ||
 * |Boolean|判定|0,1,true,falseのみ正|
 * |Date|日付けフォーマットのみ||
 * |Decimal|少数のみ||
 * |equalTo|文字列一致|equalTo:りんご~~検証値がりんごの場合のみtrue|
 * |Extension|拡張子の確認|Extension:jpg,png,gif~~検証値の値が、jpg,png,gif拡張子のみtrue|
 * |inList|リストとの一致|inList:a:car:apple~~検証値が「a,car,apple」のいずれかの場合のみtrue|
 * |Integer|整数のみ||
 * |IP4|IP4アドレスのみ||
 * |isUnique|ユニークな値のみ|※1|
 * |isUniqueUpdate|ユニークの確認（アップデート用）|※1|
 * |Length|文字数の範囲|Length:10:100~~10～100文字まで|
 * |MaxLength|最大文字数|MaxLength:100~~100文字まで|
 * |MinLength|最小文字数|MinLength:100~~100文字以上|
 * |Numeric|数字のみ||
 * |Mail|メールアドレスのみ|※簡易チェック|
 * |NotEmpty|空文字列の禁止||
 * |Phone|電話番号のみ（ハイフンあり）||
 * |Postal|郵便番号のみ(ハイフンあり)||
 * |Range|テキストの範囲指定|Range:-1:10~~検証値が-1から10のみtrue|
 * |Required|必須項目の設定||
 * |Url|Urlのみ||
 * |Zenkaku|全角のみ||
 * |Hankaku|半角のみ||
 * |ZenkakuKana|全角カナのみ||
 * |Hiragana|ひらがなのみ||
 * |Custom|ユーザ定義正規表現|Custom:/any/u|
 * ※ルール名は、小文字・大文字のどちらでも構いません。
 *
 *
 * @package    Feke
 * @subpackage util
 */

class Validation
{
	/**
	 * クラスベース読み込み
	 * @load
	 */
	use \feke\base\ClassBase;

	/**
	 * ルールのトレイト
	 */
	use validation\Rule;

	/**
	 * ユーザー用ルール拡張トレイト
	 */
	use validation\ExtendRule;

	/**
	 * エラメッセージのトレイト
	 */
	use validation\Message;

    /**
     * ルールの配列
     * @var array
     */
    protected $_rules = array();

    /**
     * 検証値の配列
     * @var array
     */
    protected $_values = array();

	/**
	 * 検証ルールごとのメッセージ
	 * @var array
	 */
	protected $_def_messages = array();

	/**
	 * 検証ルールごとのメッセージ
	 * @var array
	 */
	protected $_setMessages = array();

	/**
	 * 連結する要素の記録
	 * @var unknown
	 */
	protected $_connect = array();

	/**
	 * 連結する要素の記録
	 * @var unknown
	 */
	protected $_glue = array();

	/**
	 * データベースのコネクション
	 * @var object
	 */
	protected static $_QB;


	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public function __construct() {
		//エンコードの設定
		mb_internal_encoding ('UTF-8');
		self::$_QB = \Feke::loadUtil('QueryBuilder');
		self::$_QB->connect();
	}


	/**
	 * バリデーションを実行します。
	 *
	 * valueがない場合はpostの値をを使用します．
	 * @param array $data 検証するデータ，ルール，オプションを一括でセット
	 * @return boolean 返り値はルールに対するエラーがなければtrue，エラーがあった場合は，falseを返します．
	 * @example
	 */

	public function run ($data = null, $connect = null)
	{
		if (is_array($data)) {
			$this->setAll ($data);
		}

		if ($connect) {
			self::$_QB = $connect;
		}

		foreach ($this->_rules as $name => $rule) {
			//ルールの分解
			$rule = explode ('|', $rule);

			foreach ($rule as $value) {
				//コネクトするフォーム名
				$connect_name = '';
				//コネクトしたフォームのRequired のチェック用
				$connect_RequiredCheck = '';
				$count = 0;
				$Required_count = 0;

				$do_value = null;

				if (isset($this->_connect[$name])) {
					$connect_name = $this->_connect[$name];
					$glue = $this->_glue[$name];
					//検証する値
					$do_value = $this->_values[$name];
					foreach ($connect_name as $con_name) {
						$do_value .= $glue.$this->_values[$con_name];
						$count++;
						if ($this->Required ($this->_values[$con_name],'')) $Required_count++;
					}
					//Requiredのチェック
					if ($value == 'required') {
						if ($Required_count != $count) {
							$connect_RequiredCheck = 'bad';
						}
					}
				} else {
					if (isset($this->_values[$name])) {
						$do_value = $this->_values[$name];
					}
				}

				$param = explode (':', $value);
				$rule_name = strtolower($param[0]);
				array_shift($param);
				$rule_parm = array();
				foreach ($param as $key => $value2) {
					//ルールのパラメータ
					$rule_parm[$key+1] = $value2;
				}

				//空文字列許可の確認
				if ('allowempty' == $rule_name) {
					if (self::allowempty($do_value,'')) {
						break;
					} else {
						continue;
					}
				}

				//エラーメッセージのセット
				$message = "";
				if (isset($this->_setMessages[$name])) {
					//ユーザ定義メッセージ
					$message = $this->_setMessages[$name];
				} else {
					//ルールごとのデフォルトメッセージ
					$message = self::_getDefErrorMessage($rule_name, $rule_parm);
				}

				//ルールの実行
				if (method_exists ($this, $rule_name)) {
					$res = self::$rule_name ($do_value, $rule_parm);
				} else {
					return $this->throwError("フォーム名「{$name}」に指定されている、検証ルール「{$rule_name}」は存在しません。");
				}

				//false の場合のエラー処理
				if ($res === false or $connect_RequiredCheck == 'bad') {
					$this->_error_message[$name] = $message;
					if ($connect_name) {
						foreach ($connect_name as $con_name) {
							$this->_error_message[$con_name] = $message;
						}
					}
					break;
				}
			}
		}
		if ($this->_error_message) {
			return false;
		}
		return true;
	}

	/**
	 * ひとつ限りのバリデーションを実行します。
	 *
	 * @param string $data 検証する内容
	 * @param string $rule ルールの指定
	 *
	 * @return boolean 返り値はルールに対するエラーがなければtrue，エラーがあった場合は，エラーメッセージです。
	 */
	public static function check ($check_string, $rule)
	{
		$rule = explode ('|', $rule);
		foreach ($rule as $value) {
			$do_value = $check_string;

			$parm = explode (':', $value);
			$rule_name = "";
			$rule_parm = array();
			foreach ($parm as $key => $value2) {
				if ($key === 0) {
					//ルール名
					$rule_name = $value2;
				} else {
					//ルールのパラメータ
					$rule_parm[$key] = $value2;
				}
			}

			//空文字列許可の確認
			if (strtolower($rule_name) === 'allowempty') {
				if ( $this->AllowEmpty ($do_value, $rule_parm)  === true) return true;
			}
			//ルールの実行
			if (method_exists ('\feke\util\Validation', $rule_name)) {
				$res = self::$rule_name ($do_value, $rule_parm);
			} else {
				return $this->throwError("フォーム名「{$name}」に指定されている、検証ルール「{$rule_name}」は存在しません。");
			}
			//検証エラーがあった場合はエラーメッセージを返す。
			if ($res !== true) {
				return self::_getDefErrorMessage($rule_name, $rule_parm);
			}
		}
		return true;
	}

	/**
	 * ルール等を一括設定します。
	 *
	 * 検証するデータ，ルール，オプションを一括でセットしたい場合は，setAll()が使用できます．
	 *
	 * 配列内は，以下のようにしてください．
	 * {{{php|
	 * [
	 *     'name' => 'フィールド名',
	 *     'rule' => 'ルールの設定',
	 *     'value' => '（オプション）検証したい値',
	 *     'message' => '（オプション）エラーメッセージの設定'
	 * ];
	 * }}}
	 * ※''rule()''メソッドの引数に配列を渡せば一括でセットするため基本的に不要です。
	 *
	 * @param array $data セットしたい配列（rule・デフォルト値・タグごとのstyle）
	 * @return $this
	 * @example //配列での指定例
	 *          $set = array(
	 *              [
	 *                  'name' => 'fulte',
	 *                  'value' => 'apple',
	 *                  'rule' => 'AlphaNumeric|isUnique:item:name',
	 *                  'message' => 'already been registered.'
	 *              ],
	 *              [
	 *                  'name' => 'category',
	 *                  'value' => 'fruits',
	 *                  'rule' => 'AlphaNumeric|inList:vegetable:fruits:cake',
	 *              ],
	 *              [
	 *                  'name' => 'introduction',
	 *                  'value' => ' very tasty ',
	 *                  'rule' => 'AllowEmpty|MaxLength:200',
	 *              ],
	 *          );
	 *          $Valite->setAll($set);
	 */
	public function setAll ($data)
	{
		if(is_array($data)) {
			foreach ($data as $name => $value) {
				//検証ルールの設定
				if (isset($value['rule'])) {
					$this->setRule ($value['rule'], $name);
				}

				//検証値の設定
				if (isset($value['value'])) {
					$post_value = $value['value'];
				} else {
					if  (isset($_POST[$name])){
						$post_value = $_POST[$name];
					}
					//ドット記法が使用されていた場合は
					elseif (strpos($name,'.') !== false) {
						$params = explode('.', $name);
						if (isset($_POST[$params[0]])) {
							$array = $_POST[$params[0]];
							array_shift($params);
							foreach ($params as $value2) {
								if (isset($array[$value2])) {
									$array = $array[$value2];
								} else {
									$array = null;
									break;
								}
							}
							$post_value = $array;
						} else {
							$post_value = null;
						}
					} else {
						$post_value = null;
					}
				}
				$this->setValue ($post_value, $name);

				//エラーメッセージの設定
				if (isset($value['message'])) {
					$this->setMessage ($value['message'], $name);
				}
			}

			foreach ($data as $name => $value) {
				// コネクト（_connect）の処理
				if (isset($value['connect'])) {
					$conect_name = '';
					$conect_name = $value['connect'];
					$conect_name = explode ('|', $conect_name);
					foreach ($conect_name as $connects) {
						foreach ($data as $key2 => $obj2) {
							if ($key2 ==  $connects) {
								$this->_connect[$name][] = $connects;
								break;
							}
						}
					}
				}
				if (isset($value['glue'])) {
					$this->_glue[$name] = $value['glue'];
				} else {
					$this->_glue[$name] = null;
				}
			}
		} else {
			$this->throwError('引数には配列を指定してください。', true);
		}
		return $this;
	}

	/**
	 * ルール等の一括設定をします。（その2）
	 *
	 * FormHelper用です
	 *
	 * @class hide
	 * @param array $data セットしたい配列（rule・デフォルト値・タグごとのstyle）
	 * @return $this
	 */
	public function setAllForm ($data)
	{
		if(is_array($data)) {
			foreach ($data as $value) {
				if ($value['name']) {
					$name = $value['name'];
				} else {
					continue;
				}
				if ($name) {
					if (isset($value['rule'])) {
						$this->setRule ($value['rule'], $name);
					}
					if (isset($value['value'])) {
						$this->setValue ($value['value'], $name);
					}
					if (isset($value['message'])) {
						$this->setMessage ($value['message'], $name);
					}
				}
			}

			foreach ($data as $key => $obj) {
				// コネクト（_connect）の処理
				if (!isset($obj['connect'])) continue;
				if ($obj['connect']) {
					$conect_name = '';
					$conect_name = $obj['connect']['and'];
					$conect_name = explode ('|', $conect_name);
					foreach ($conect_name as $connects) {
						foreach ($data as $key2 => $obj2) {
							if ($obj2['name'] ==  $connects) {
								$this->_connect[$obj['name']][] = $connects;
								break;
							}
						}
					}
				}
			}
		} else {
			$this->throwError('引数には配列を指定してください。', true);
		}
		return $this;
	}

	/**
	 * ルールの設定をします。
	 *
	 * FekePHPのバリデーションクラスでは，半角英数字や日付け，電話番号のフォーマットだけではなく，ひらがな等日本語関連の検証ルールも用意しており，約30程度の検証が行えます．
	 *
	 * ルールのセットは，他のフレームワークのような配列地獄にならないよう，FekePHPでは，「|」を使用して複数ルールを設定できます．
	 * ルールのオプションは必要に応じて「:」で区切ることで設定できます．
	 *
	 * ※空文字を許可したい場合は，ルールの先頭に「AllowEmpty」を追加してください．
	 *
	 * @param mixed $data セットしたいルール
	 * @param string $name セット先のname属性
	 * @return $this
	 * @example //変数での指定
	 *          //半角英数字，テーブル「item」カラム「name」でユニーク
	 *          $Valite->setRule( 'name' ,'AlphaNumeric|isUnique:item:name');
	 *
	 *          //半角英数字，
	 *          $Valite->setRule( 'category' ,'AlphaNumeric|inList:vegetable:fruits:cake');
	 *
	 *          //空許可，200文字まで
	 *          $Valite->setRule( 'introduction' ,'AllowEmpty|MaxLength:200');
	 *
	 *          //配列での指定例
	 *          $data = array(
	 *              'name' => 'AlphaNumeric|isUnique:item:name',
	 *              'category' => 'AlphaNumeric|inList:vegetable:fruits:cake',
	 *              'introduction' => 'AllowEmpty|MaxLength:200',
	 *          );
	 *          $Valite->setRule($data);
	 */
	public function setRule ($data, $name = null)
	{
		if(is_array($data)) {
			foreach ($data as $name => $value) {
				if ($value['rule'] !== null) {
					if (stripos($value['rule'],'allowempty')) {
						$value['rule'] = 'allowempty|'.$value['rule'];
					}
					$this->_rules[$name] = $value['rule'];
				}
			}
		} else {
			if (stripos($data,'allowempty')) {
				$data = 'allowempty|'.$data;
			}
			$this->_rules[$name] = $data;
		}return $this;
	}

	/**
	 * 検証する値をセットします。
	 *
	 * 値のセットは，
	 * -フィールド名（フォームの名前属性）
	 * -検証したい値
	 * または，配列による一括設定が行えます．
	 *
	 * @param array|string $data セットしたい値
	 * @param string       $name セット先のname属性
	 * @return $this
	 * @example //変数での指定
	 *          $Valite->setValue( 'name' ,'apple');
	 *          $Valite->setValue( 'category' ,'fruits');
	 *          $Valite->setValue( 'introduction' ,'very tasty');

	 *          //配列での指定例
	 *          $data = array(
	 *              'name' => apple',
	 *              'category' => 'fruits',
	 *              'introduction' => ' very tasty ',
	 *          );
	 *          $Valite->setValue($data);
	 */
	public function setValue ($data, $name = null)
	{
		if(is_array($data)) {
			$this->_values = $data;
		} else {
			$this->_values[$name] = $data;
		}return $this;
	}

	/**
	 * ユーザー定義のエラーメッセージのセットします。
	 * デフォルトのメッセージでは不満なときにご利用ください。
	 *
	 * @param array|string $data セットしたいメッセージ
	 * @param string       $name セット先のname属性
	 * @return $this
	 * @example //変数での指定
	 *          $Valite->setMessages( 'name' ,' already been registered.');
	 *
	 *          //配列での指定例
	 *          $data = array(
	 *              'name' =>  already been registered.',
	 *          );
	 *          $Valite->setMessages($data);
	 */
	public function setMessage ($data, $name = null)
	{
		if(is_array($data)) {
			foreach ($data as $name => $value) {
				if ($value['message'] !== null) {
					$this->_setMessages[$name] = $value['message'];
				}
			}
		} else {
			$this->_setMessages[$name] = $data;
		}return $this;
	}

	/**
	 * 結合プロパティの取得
	 *
	 * @class hide
	 * @return array
	 */
	public function getConnect()
	{
		if ($this->_connect) {
			return $this->_connect;
		}
	}
}