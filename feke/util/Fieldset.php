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
 * フォーム生成と値の検証を行うクラスです。
 *
 * **使用方法**
 * {{{php|
 * //インスタンス作成
 * $Fieldset = \Feke::loadUtil ('Fieldset');
 *
 * //$インスタンス->フォームの属性名 = ['type' => 'フォームのタイプ','その他属性名' => '値',,,];
 * $Fieldset>name  = ['type' => 'text'];
 * $Fieldset->intr = ['type' => 'textarea','rule' => 'maxLength:200'];
 * $Fieldset->id   = ['type' => 'text','class' => 'id', 'rule' => 'numeric'];
 *
 *
 * //検証開始
 * if ($Fieldset->run()) {
 *     //検証が成功した時
 *
 *     //何らかの処理
 *     return true;
 *
 * } else {
 *     //検証が失敗した時
 *     //フォームを生成
 *     $Fieldset->get();
 *
 *     //エラー取得
 *     $error = $Fieldset->getError();
 * }
 *
 * }}}
 *
 * **作成フォームの指定例**
 * {{{php|
 * //オブジェクトのプロパティに挿入して個別に設定
 * $Fieldset->name = ['type' => 'textarea','rule' => 'maxLength:200'];
 *
 * //set()メソッドで個別に設定
 * $Fieldset->set ('name',['type' => 'textarea','rule' => 'maxLength:200']);
 *
 * //複数件設定
 * $array = array(
 *     //'フォームの属性名' => ['type' => 'フォームのタイプ','その他属性名' => '値',,,],
 *     'name' = ['type' => 'text'];
 *     'intr' = ['type' => 'textarea','rule' => 'maxLength:200'];
 *     'id'   = ['type' => 'text','class' => 'id', 'rule' => 'numeric'];
 * );
 * $Fieldset->setAll ($array);
 *
 * //配列の指定法
 * //ドッド記法で配列なフォームを作成できます。
 * $user_array = ['type' => 'text', 'rule' => 'hiragana'];
 * $Fieldset->{'user.0'} = $user_array + ['value' => 'ユーザー0'],
 * $Fieldset->{'user.1'} = $user_array + ['value' => 'ユーザー1'],
 * $Fieldset->{'user.2'} = $user_array + ['value' => 'ユーザー2'],
 *
 * }}}
 * **複数フォームの同時検証例**
 * ''connect''を指定することで、複数のフォームを連結することができます。
 * {{{php|
 * $array =
 *     'phone.1' => ['type' => 'text', 'connect' => 'phone.2|phone.3', 'glue' => '-','rule' => 'phone'];
 *     'phone.2' => ['type' => 'text'];
 *     'phone.3' => ['type' => 'text'];
 * );
 * $form = $Fieldset->get($array);
 * echo $form['phone'][1];
 * //出力例
 * //<input type="text" name="phone[1]">-<input type="text" name="phone[2]">-<input type="text" name="phone[3]">
 * }}}
 *
 * **指定できるオプションキー**
 * ここに指定されていないキーは、すべて、フォームの中で'キー名'='値'として挿入されます。
 * |~キー名|内容||
 * |!type|フォームの種類。|生成するフォームのタイプを指定します。(text,hide,select等、詳しくは、\feke\util\Formクラスへ)|
 * |!option|formのselectタグ作成時に使用。|selectタグ内のオプションを指定できます。'オプション内のvalue属性' => '表示する値'で指定出来ます。詳しくは、\feke\util\Formクラスへ|
 * |!rule|検証ルール。|Validationクラスと同じ指定方法でルールを指定できます。|
 * |!connect|フォームの連結。|連結していフォームの属性名を順番に記載します。ここで、連結したフォームは値の検証時も連結した状態で検証されます。|
 * |!glue|フォームを連結したときに、フォーム間に挿入される文字列。|検証時は、この文字列を加えて検証されます。|
 * |!before|フォームの前にタグで挟んでラベルを張ります。||
 * |!after|フォームの後にタグで挟んでラベルを張ります。||
 * |!before_no|フォームの前にタグで挟んでラベルを張ります。(テキストのエスケープをキャンセルします。)||
 * |!after_no|フォームの後にタグで挟んでラベルを張ります。(テキストのエスケープをキャンセルします。)||
 * |!label|フォームの後にラベルを張ります。||
 * |!label_no|フォームの後にラベルを張ります。(テキストのエスケープをキャンセルします。)||
 * |!|||
 * |!|||
 *
 *
 * @author sham
 *
 */
class Fieldset
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 *
	 */
	protected $_params;

	protected $_options;
	
	protected $_in_value = array();

	public function __construct ()
	{
		$this->_options = array();
		$this->_params = new \stdClass;

		$this->_params->part = array();
		$this->_params->up_file = null;

		$this->_Config = \Feke::loadConfig ('/util/fieldset');
	}

	/**
	 * テンプレートへ挿入する値をセットする
	 * @param string  $filed
	 * @param mixed   $value
	 * @class hide
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * 値の取得
	 * @param string $filed
	 * @class hide
	 */
	public function __get($filed)
	{
		if (strpos($filed, '_') === 0) $this->throwError ('最初がアンダーバーの場合は取得できません。');

		if (isset($this->_data[$filed])) {
			return $this->_data[$filed];
		}
		return;
	}

	/**
	 * 設定内容から値を検証します。
	 *
	 * @param array $array
	 * @return boolean 返り値はルールに対するエラーがなければtrue，エラーがあった場合は，falseを返します．
	 */
	public function run ($array = null, $connect = null)
	{
		if ($array) $this->setAll($array);

		//検証
		$Val = \Feke::loadUtil ('Validation');

		//検証開始
		if (true === $Val->run($this->_params->part, $connect)) {
			//検証が成功した時
			return true;
		}
		$this->_error_message = $Val->getError();
		return false;
	}

	/**
	 * 設定内容からフォームを作成します。
	 *
	 * 第一引数は、run()でセットされた値を引き継ぐので、指定している場合は不要です。
	 *
	 * @param array $array
	 * @return 正常に生成した場合は、生成したフォームの配列を返します。
	 */
	public function get ($array = null)
	{
		if ($array) {
			$this->setAll($array);
		}

		//統合関係の前処理
		$this->_params->to_connect = array();
		foreach ($this->_params->part as $filed => $params) {
			if (isset($params['connect'])){
				$option_array = explode ('|', $params['connect']);

				foreach ($option_array as $field_name) {
					$this->_params->to_connect[$field_name] = $filed;
				}
			}
		}

		$this->_params->up_file = false;

		foreach ($this->_params->part as $filed => $params) {
			if (isset($params['type'])) {
				//オプションの実行
				if (method_exists ('\feke\util\Form', $params['type'])) {
					$connect_name = $filed;
					if (isset($this->_params->to_connect[$filed])) {
						$connect_name = $this->_params->to_connect[$filed];
					}
					if (!isset($this->_data[$connect_name])) $this->_data[$connect_name] = null;

					//接続用の文字列
					$glue = null;
					if (isset($this->_params->part[$connect_name]['glue'])) {
						$glue = $this->_params->part[$connect_name]['glue'];
					}
					$before = null;
					$after = null;
					
					if (isset($params['id']) and is_value($params['id'])) {
						$label_for = ' for="'.h($params['id']).'"';
					} else {
						$label_for = null;
					}
					
					if (isset($params['before'])) {
						$before = "<label{$label_for}>".h($params['before']);
						$after = '</label>';
					} elseif (isset($params['after'])) {
						$before = "<label{$label_for}>";
						$after = h($params['after']).'</label>';
					} elseif (isset($params['before_no'])) {
						$before = "<label{$label_for}>".$params['before_no'];
						$after = "<label{$label_for}>";
					} elseif (isset($params['after_no'])) {
						$before = "<label{$label_for}>";
						$after = $params['after_no'].'</label>';
					} elseif (isset($params['label'])) {
						$before = null;
						$after = "<label{$label_for}>".h($params['label']).'</label>';
					}elseif (isset($params['label_no'])) {
						$before = null;
						$after = "<label{$label_for}>".$params['label_no'].'</label>';
					}

					$new_form = \feke\util\Form::{$params['type']} ($params);

					if ($connect_name === $filed) {
						if ($this->_data[$connect_name]) $this->_data[$connect_name] = $glue.$before.$this->_data[$connect_name].$after;
						else $this->_data[$connect_name] = $before.$new_form.$this->_data[$connect_name].$after;
					} else {
						$this->_data[$connect_name] .= $glue.$before.$new_form.$after;
					}

				} else {
					$this->throwError("編集オプション「{$params['type']}」は存在しません。", true);
				}
				if (strtolower($params['type']) === 'file') {
					//form_start用
					$this->_params->up_file = true;
				}
			}
		}
		//検証結果からエラーをセット
		foreach ($this->_data as $filed => $params) {
			if (isset($this->_error_message[$filed]) and $this->_Config->CONFIG->PRINT_ERROR) {
				$this->_data[$filed] .= sprintf($this->_Config->HTML->ERROR_STYLE, $this->_error_message[$filed]);
			}
		}

		//セット
		$upfile = null;
		if ($this->_params->up_file === true) {
			$upfile =  " enctype=\"multipart/form-data\"";
		}
		//$this->_data['_open'] = "<form action=\"\" method=\"post\"{$upfile}>";
		//$this->_data['_close'] = '</form>';

		//リセット
		$this->_params = null;

		//配列名の調整
		foreach ($this->_data as $field => $value) {
			if (strpos($field, '.') !== false) {
				$params = explode('.', $field);
				$array = $this->_data[$field];
				$new_array = array();
				krsort($params);
				foreach ($params as $value2) {
					$new_array[$value2] = $array;
					$array = $new_array;
					$new_array= array();
				}
				$this->_data = array_plus($this->_data,$array);
				unset($this->_data[$field]);
			}
		}
		return $this->_data;
	}

	/**
	 * 変数を割り当てを行います。
	 *
	 * テンプレート内で使用する変数をセットします。
	 * 第一引数には、ドット記法を使用して配列を表現できます。
	 *
	 * なお、オブジェクトは内部で配列へ変換されるためメソッド等は消滅します。
	 * メソッドや関数を渡したい場合は、setObject()を使用してください。
	 *
	 * @param string  $filed 割り当てたい変数名
	 * @param mixed   $value 変数の値
	 *
	 * @example $View->set('name', 'Feke');
	 */
	public function set ($name, $value)
	{
		if (strpos($name, '_') === 0) $this->throwError ('最初がアンダーバーの変数名は使用できません。');

		$new_value = array();

		//オプション文字列の分解
		if (is_string($value)) {
			$option_array = explode ('|', $value);

			foreach ($option_array as $unit) {
				$param = explode (':', $unit);
				//配列化
				$op_name = $param[0];
				array_shift($param);
				$new_value[$op_name] = implode($param);
			}
		} else {
			$new_value = $value;
		}
		$new_value['name'] = $name;

		$post_value = null;
		if (strpos($name,'.') !== false) {
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
			}
		} else {
			if (isset($_POST[$name])) {
				$post_value = $_POST[$name];
			} else {
				if (isset($this->_in_value[$name]))
				$post_value = $this->_in_value[$name];
			}
		}

		if (isset($post_value) and is_string($post_value)) {
			$this->_params->part[$name]['value'] = $post_value;
			$new_value['value'] = $post_value;
		}

		$this->_params->part[$name] = $new_value;

		return true;
	}

	/**
	 * 一括でセットします。
	 * @param array $array
	 * @return boolean
	 */
	public function setAll ($array)
	{
		foreach ($array as $name => $field) {
			$this->set ($name, $field);
		}
		return $this;
	}
	
	/**
	 * デフォルト値の設定をします。
	 *
	 * @param array $form
	 */
	public function setValue ($value)
	{
		$this->_in_value = to_array($value);
		
		return $this;
	}
}