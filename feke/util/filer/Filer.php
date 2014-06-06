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

namespace feke\util\filer;

/**
 * ファイル操作/読み込みクラスの基底クラス
 *
 * 拡張子偽装のチェック、配列操作を行っています．
 *
 * 設定にて設定されている拡張子のみアップロードできます．
 *
 * 初期設定は
 * /aap/config.config.php内の
 * ・FILE_MAX_SIZE   ... 最大ファイルサイズ
 * ・FILE_PUT_PATH   ... 保存先ファイルパス
 * が設定できます．
 * なお，これらは配列からでも書き換え可能です．
 *
 *
 * ※windowsのみ，php.iniにて「php_fileinfo.dll」を有効にしてください．
 * http://php.net/manual/ja/fileinfo.installation.php
 *
 * @package    Feke
 * @subpackage util
 *
 */
class Filer
{
	/**
	 * MimeTypeの配列
	 */
	protected static $_ContentTypes;


	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public function __construct ($obj = null)
	{
		if (!self::$_ContentTypes) {
			$object = \Feke::_('object');
			self::$_ContentTypes = \Feke::loadConfig('mineType')->ARROW_EXTENTION;
		}
	}

	/**
	 * 書き込み権限の確認
	 *
	 * ディレクトリの存在と書き込み権限の確認を行います．
	 *
	 * @param  unknown $put_path  ファイルパス
	 * @return boolean|massage
	 */
	protected static function _checkWritable ($put_path)
	{
		//パスの正規化
		$put_path = realpath($put_path);
		// ディレクトリが有効か確認
		if ($put_path === false || !is_dir($put_path)) {
			return "有効なディレクトリではありません.";
		}

		// 書き込み権限の確認
		if (!is_writable($put_path)) {
			return "ファイルの書き込み権限がありません";
		}
		return true;
	}

	/**
	 * 拡張子の判定
	 *
	 * 登録されていない拡張子の場合は，例外を投げます．
	 *
	 * @parm  ファイルパス
	 * @param 許可するMimeType
	 * @parm  許可する拡張子
	 */
	protected function _checkExtension ($path, $arr_mime_type = null, $arr_extension = null, $type = null)
	{
		//MimeTypeの確認
		//拡張子偽造チェック
		$fo = new \finfo(FILEINFO_MIME_TYPE);

		$type = $fo->file($path);

		if ($type === false) {
			$this->throwError ('MimeTypeを取得できませんでした．');
		}

		//MimeTypeの制限
		if(is_string($arr_mime_type)) {
			$arr_mime_types = explode('|',$arr_mime_type);
			if ((array_search($type, $arr_mime_types)) === false) {
				$this->throwError ("{$type}は許可されていないファイル形式です．");
			}
		}

		//拡張子の制限
		if (is_string($arr_extension)) {
			$arr_extensions = explode('|',$arr_extension);
			if ((array_search($extension, $arr_extensions)) === false) {
				$this->throwError ("{$arr_extension}は許可されていないファイル形式です．");
			}
		}

		//mineTypeがtext/---と、image/---の場合はそのまま通す
		return self::_getExtensio($path);
	}

	/**
	 * MineTypeから拡張子を割り出します
	 */
	protected function _mimeToExtension ($mime_type)
	{
		if (property_exists(self::$_ContentTypes,$mime_type)) {
			return self::$_ContentTypes->$mime_type;
		}
		return false;
	}

	/**
	 * ファイル名から拡張子を取得
	 *
	 * @parm ファイル名
	 */
	protected static function _getExtensio ($filename)
	{
		$parms = explode('.', $filename);
		return (string)$parms[count($parms) - 1];
	}

	/**
	 * json読み込みエラー
	 */
	protected static function _jsonError ()
	{
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				return false;
				break;
			case JSON_ERROR_DEPTH:
				return 'スタックの深さの最大値を超えました';
				//return ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				return '形式が無効、あるいは壊れています';
				//return ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				return '制御文字エラー。おそらくエンコーディングが違います';
				//return ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				return '構文エラーです．';
				//return ' - Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				return '正しくエンコードされていません．';
				//return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				return '不明なエラー';
				//return ' - Unknown error';
				break;
		}
	}



}