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

namespace feke\util\validation;

/**
 * Validation Rules class
 *
 * @package     Feke
 * @subpackage  util.validation
 */

trait Message
{
	/**
	 * デフォルトエラーメッセージの設定
	 *
	 * name属性ごとのエラーメッセージが設定されていない場合のみ，
	 * こちらのメッセージが適用されます．
	 *
	 * なお，メッセージの適用順は，基本的に設定したルールの順番に従います．
	 *
	 * @param  string $name   フォームの名前
	 * @param  array  $option ルールの設定パラメータ
	 * @return string         バリデーションのエラーメッセージ
	 */

	protected static function _getDefErrorMessage ($name, $option)
	{
		$name =  strtolower($name);

		//optionの分解
		$func = function ($options) {
			foreach($options as $value) {
				$ms .= "「{$value}」か";
			}
			return $ms;
		};

		switch ($name) {

			case 'checktag':
				return "HTMLタグの入力はできません．";
			case 'alpha':
				return "半角英字で入力してください。";
			case 'alphanumeric':
				return "半角英数字で入力してください。";
			case 'alphanumericbar':
					return "半角英数字とアンダーバーで入力してください。";
			case 'boolean':
				return "値が不正の可能性があります。";
			case 'length':
				return "{$option[1]}～{$option[2]}文字で入力してください。";
			case 'custom':
				return "値が一致しません。";
			case 'date':
				return "日付けのフォーマットで入力してください。";
			case 'decimal':
				return "小数を入力してください。";
			case 'equalto':
				return "この項目は文字列で「{$option[1]}」としなければなりません。";
			case 'extension':
				return $func($option)."の拡張子のファイル名を入力してください。";
			case 'inlist':
				return $func($option)."を入力してください．";
			case 'integer ':
				return "整数を入力してください。";
			case 'isunique':
				return "入力された{$option[3]}はすでに登録されてます。お手数ですが違う{$option[3]}を入力してください。";
			case 'isuniqueupdate':
				return "入力された{$option[3]}はすでに登録されてます。お手数ですが違う{$option[3]}を入力してください。";
			case 'maxlength':
				return "{$option[1]}文字以下で入力してください。";
			case 'minlength':
				return "{$option[1]}文字以上で入力してください。";
			case 'numeric':
				return "半角数字で入力してください。";
			case 'mail':
				return "メールアドレスの形式で入力してください。";
			case 'notempty':
				return "必須項目です。";
			case 'phone':
				return "電話番号が不正です。";
			case 'postal':
				return "郵便番号が不正です。";
			case 'phone':
				return "電話番号が不正です。";
			case 'range':
				return "{$option[1]}～{$option[2]}の値を入力してください。";
			case 'required':
				return "必須項目です。";
			case 'url':
				return "URLが不正です。";
			case 'zenkaku':
				return "全角で入力してください。";
			case 'hankaku':
				return "半角で入力してください。";
			case 'zenkakukana':
				return "全角カナで入力してください。";
			case 'hiragana':
				return "ひらがなで入力してください。";
		}
		return "{$name} is error";
	}
}