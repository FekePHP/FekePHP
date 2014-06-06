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

namespace feke\util\validation;

use feke\core\coreConfig as CC;
use \Feke as Feke;

/**
 * Validation Rules class
 *
 * @package     Feke
 * @subpackage  util.validation
 */

trait Rule
{
	/**
     * 空文字列を検出
     *
     * phpでは，== で判定した場合，
     * '0' ，'' ， false が同一と判定されるため専用のメソットを用意
     *
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
     */
	public static function checkEmpty ($text, $option = null)
	{
		if ('' == strval($text)) return true;
		return false;
	}

	/**
	 * タグを検出
	 *
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function checkTag ($text, $option = null)
	{
		if(strip_tags($text) === $text) {
			return true;
		}
		return false;
	}

	/**
	 * タグを許可
	 *
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function AllowTag ($text, $option = null)
	{
		return true;
	}

    /**
     * 空白を許可
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
     */
	public static function AllowEmpty ($text, $option)
	{
		if (self::checkEmpty($text)){
			return true;
		}
		return false;
	}

	/**
	 * アルファベットのみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Alpha ($text, $option)
	{
		if (preg_match('/^[a-zA-Z]+$/',$text)) {
			return true;
		}
		return false;
	}

	/**
	 * 半角英数字のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function AlphaNumeric ($text, $option)
	{
		if (preg_match('/^[a-zA-Z0-9]+$/',$text)) {
			return true;
		}
		return false;
	}

	/**
	 * 半角英数字アンダーバーのみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function AlphaNumericBar ($text, $option)
	{
		if (preg_match('/^[a-zA-Z0-9_]+$/',$text)) {
			return true;
		}
		return false;
	}

	/**
	 * 文字数の範囲
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Length ($text, $option)
	{
		if (mb_strlen($text) >= $option[1] and mb_strlen($text) <= $option[2]) {
			return true;
		}
		return false;
	}

	/**
	 * 判定
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Boolean ($text, $option)
	{
		$List = array(0, 1, '0', '1', true, false);
		if (in_array($text, $List)) {
			return true;
		}
		return false;
	}

	/**
	 * カスタム（ユーザ定義正規表現）
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Custom ($text, $option)
	{
		if (preg_match($option[1], $text)) {
			return true;
		}
		return false;
	}

	/**
	 * 日付けフォーマットのみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Date ($text, $option)
	{
		$type = $option[1];
		if (!$type) $type = 'ymd';
		$sprit = "- \/\.";
		if ($type == 'ymd') {
			if (preg_match("/^\d{4}[{$sprit}]\d{2}[{$sprit}]\d{2}$/", $text)) {
				return true;
			}
			return false;
		} else {
			if (preg_match("/^\d{2}[{$sprit}]\d{2}[{$sprit}]\d{4}$/", $text)) {
				return true;
			}
			return false;
		}
	}

	/**
	 * 少数のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Decimal ($text, $option)
	{
		if (preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $text)) {
			return true;
		}
		return false;
	}

	/**
	 * 文字列一致
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function equalTo ($text, $option)
	{
		if ($text === (string)$option[1]) {
			return true;
		}
		return false;
	}

	/**
	 * 拡張子の確認
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Extension ($text, $option)
	{
		$parms = explode (',', $option[1]);
		if ($parms) {
			foreach ($parms as $value) {
				if ($match) $match .= '|';
				$match .= "\.{$value}$";
			}
		} else {
			return false;
		}
		if (preg_match("/{$match}/u", $text)) {
			return true;
		}
		return false;
	}

	/**
	 * デフォルト設定値との一致
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function inList ($text, $option)
	{
		if (is_array($option)) {
			if (in_array($text, $option)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 整数のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	//
	public static function Integer ($text, $option)
	{
		if (preg_match('/^[\-+]?[0-9]+$/u', $text)) {
			return true;
		}
		return false;
	}

	/**
	 * IP4アドレスのみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function IP4 ($text, $option)
	{
		if (preg_match('/[1-9]\d|1\d\d|2[0-4]\d|25[0-5])([.](?!$)|$)){4}/',$text)) {
			return true;
		}
		return false;
	}

	/**
	 * ユニークな値のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function isUnique ($text, $option)
	{
		//データベースより
		$data = self::$_QB
			->from($option[1])
			->where($option[2],$text)
			->get();
		if ($data) {
			return false;
		}
		return true;
	}

	/**
	 * ユニークの確認（アップデート用）
	 *
	 * $option = array(1 => 'テーブル名', 2 => 'カラム名', 3 => 'エラーメッセージ用', 4 => '重複を許可する値')
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function isUniqueUpdate ($text, $option)
	{
		//データベースより
		$data = self::$_QB
			->from($option[1])
			->where($option[2], '=', $text)
			->where($option[2], '<>', $option[4])
			->get();
		if ($data) {
			return false;
		}
		return true;
	}

	/**
	 * 最大文字数
	 *
	 * @param string $text  検証対象の値
	 * @param array  $option option[1]に最大文字数をセット
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function MaxLength ($text, $option)
	{
		if (mb_strlen($text) <= $option[1]) {
			return true;
		}
		return false;
	}

	/**
	 * 最小文字数
	 * @param string $text  検証対象の値
	 * @param array  $option option[1]に最小文字数をセット
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function MinLength ($text, $option)
	{
		if (mb_strlen($text) >= $option[1]) {
			return true;
		}
		return false;
	}
	/**
	 * 数字のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Numeric ($text, $option)
	{
		return is_numeric($text);
	}

	/**
	 * メールアドレスの簡易チェック
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Mail ($text, $option)
	{
		if (preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i',$text)) {
			return true;
		}
		return false;
	}

	/**
	 * 空文字列の禁止
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function NotEmpty ($text, $option)
	{
        if (self::checkEmpty($text) or is_array($text)) {
		    return false;
		}
		return true;
	}

	/**
	 * 電話番号のチェック（ハイフンあり）
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Phone ($text, $option)
	{
		if (preg_match("/^\d{3}\-\d{4}\-\d{4}$/", $text)) {
			return true;
		}
		return false;
	}

	/**
	 * 郵便番号のチェック(ハイフンあり)
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Postal ($text, $option)
	{
		if (preg_match("/^\d{3}\-\d{4}$/", $text)) {
			return true;
		}
		return false;
	}

	/**
	 * テキストの範囲指定
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Range ($text, $option)
	{
		if ($text >= $option[1] and $text <= $option[2]) {
			return true;
		}
		return false;
	}
	/**
	 * 必須項目の設定
	 *
	 * textが空文字，配列※ならfalse
	 *
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Required ($text, $option)
	{
		if (self::checkEmpty($text) or is_array($text)) {
		    return false;
		}
		return true;
	}
	/**
	 * Urlのチェック
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	public static function Url ($text, $option)
	{
		if (preg_match("/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i", $text)) {
			return true;
		}
		return false;
	}

	/**
	 * 全角のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	static function Zenkaku($text, $option)
	{
		$len = strlen($text);
		$mblen = mb_strlen($text, "UTF-8") * 3;
		if($len == $mblen) {
			return true;
		}
		return false;
	}

	/**
	 * 半角のみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	static function Hankaku($text, $option)
	{
		$len = strlen($text);
		$mblen = mb_strlen($text, "UTF-8");
		if($len == $mblen) {
			return true;
		}
		return false;
	}

	/**
	 * 全角カナのみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	static function ZenkakuKana($text, $option)
	{
		if (preg_match("/^[ァ-ヶー]+$/u", $text)){
			return true;
		}
		return false;
	}

	/**
	 * ひらがなのみ
	 * @param string $text  検証対象の値
	 * @param array  $option オプション
	 * @return 条件に一致したらtrue,それ以外はfalseを返す。
	 */
	static function Hiragana($text, $option)
	{
		if (preg_match("/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、]+$/u", $text)) {
			return true;
		}
		return false;
	}
}