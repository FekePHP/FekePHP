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

/**
 * ファイルダウンロードクラス
 *
 *
 *
 * @package   Feke
 * @subpackage util
 *
 */
class Download
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * ZipClassのインスタンス
	 * @var object
	 */
	private static $_zip;

	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public static function __construct ()
	{
		self::$_zip = \Feke::load ('Zip','util');
	}

	/**
	 * クライアントへファイルをダウンロードさせます。
	 *
	 * zipが指定された場合は、そのままダウンロードを開始し、
	 * ファイルやフォルダが指定された場合は、一時ファイルなzipを用意してからクライアントへファイルをダウンロードさせます。
	 *
	 * @param string $file_name ダウンロードさせたいzipファイル、またはファイル名、ディレクトリ名
	 * @param string $put_file_name 作成したzipファイルの保管場所
	 * @return 成功時はtrueを返します。
	 * @throws 失敗時は、Error例外を投げます。
	 * @example try {
	 *              //アプリケーションの画像フォルダを指定
	 *              $file_name = storage_path ().'/image';
	 *              //クライアントにダウンロードさせる
	 *              \feke\util\Download::run($file_name);
	 *          } catch (\Error $e) {
	 *              //失敗した場合
	 *              echo 'ダウンロードの開始に失敗しました。';
	 *          }
	 *          //成功した場合
	 *
	 *          //ヘッダを出力するのでいかなる文字列も表示してはいけません。
	 *          //リダイレクトで対応してください。
	 *
	 */
	public static function run ($file_name, $put_file_name = null)
	{
		try {
			if (!self::$_zip) self::__construct ();

			//zipファイルであれば
			if (preg_match('/\.zip$/i', $file_name)) {
				self::$_zip->download ($file_name);
			} else {
				//一時ファイル作成
				if (!$put_file_name) $put_file_name = tempnam(app_path ()."/tmp", "zip");
				self::$_zip->create($put_file_name ,$file_name);

				self::$_zip->close();

				self::$_zip->download ($put_file_name);

				unlink($put_file_name);
			}
		} catch (\Error $e) {
			return $this->throwError($e->get());
		}
		return true;
	}
}