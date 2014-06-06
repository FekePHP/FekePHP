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
 * zipファイル操作クラスです。
 *
 * zipアーカイブファルを簡易的に操作するクラスです。
 *
 * 主な機能は、
 * -zipの解凍、圧縮
 * -zip内にファイル・データの追加
 * -zip内のファイル・データの削除
 * となります。
 *
 * **読み込み**
 * このクラスは、インスタンスの作成が必須です。
 * {{{php|
 * //インスタンス作成
 * $Zip = \Feke::load ('zip','util');
 *
 * //基準ディレクトリの設定
 * //アプリケーションファイル内のストレージフォルダを指定します。
 * $Zip->rootDir (storage_path ());
 * }}}
 *
 * **使用例**
 * ここでは、アプリケーションの画像ディレクトリを圧縮し、クライアントヘダウンロードさせるサンプルを示しています。
 * {{{php|
 * try {
 *     //zipアーカイブの作成
 *     //zipの作成先のパス
 *     $create_parh = storage_path ().'/zip/imgs.zip';
 *     //zipに予め入れておくパス
 *     $target_path = storage_path ().'/image';
 *
 *     $Zip->create ($create_parh ,$target_path);
 *
 *     //アーカイブを閉じる
 *     $Zip->close();
 *
 *     //作成したzipをダウンロードさせる
 *     $Zip->download ($create_parh);
 *
 * } catch (\Error $e) {
 *     //操作が失敗した時の動作
 * }
 * }}}
 *
 * @package    Feke
 * @subpackage util
 *
 */
class Zip extends filer\Filer
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * ファイルのパス
	 * @var string
	 */
	protected static $_file_path;

	/**
	 * ZIP アーカイブの開閉フラグ
	 * @var boolen
	 */
	private $_open_flag = false;

	/**
	 * ZipArchiveのインスタンス
	 * @var object
	 */
	private $_zip;

	/**
	 * 基準ディレクトリ
	 * @var string
	 */
	private $_rootDir = FEKE_ROOT_PATH;



	/**
	 * コンストラクト
	 * @class hide
	 */
	public function __construct ()
	{
		$this->_zip = new \ZipArchive();
	}

	/**
	 * 指定されたパスのzipファイルを開きます。
	 *
	 * ファイルの追加、削除、zipの解凍などをしたい場合は、このメソッドで先に開く必要があります。
	 *
	 * @param  string $zip_name 操作したいzipのファイル名とパス
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open ('/zip/list.zip');
	 */
	public function open ($zip_name)
	{
		if (!is_file($zip_name)) $zip_name = $this->_rootDir.$zip_name;

		//zipファイルの解凍
		if (true === ($code = $this->_zip->open ($zip_name))) {
			$this->_open_flag = true;
			return true;

		} else {
			$this->_open_flag = false;
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zipファイルを作成
	 *
	 * $filenameに指定があった場合は、そのファイルを挿入します。
	 *
	 * @param   string $zip_name  作成したいzipの名前とパス
	 * @param   string $filename  作成時にzipへ挿入したいファイル名
	 * @return 作成に成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->create ('/zip/list.zip', '/image/1.png');
	 */
	public function create ($zip_name, $filename = null)
	{
		//if (!is_file($zip_name)) $zip_name = $this->_rootDir.$zip_name;

		//zipファイルの解凍
		if (true === $this->_zip->open ($zip_name, \ZipArchive::CREATE)) {
			$this->_open_flag = true;

			if ($filename) {
				return $this->addFile($filename);
			}
			return true;
		} else {
			$this->_open_flag = false;
			return false;
		}
	}

	/**
	 * 指定されたファイルにzipを解凍
	 *
	 * @param string   $open_place zip解凍先のファイルパス
	 * @return 解凍に成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip->unzip (root_path ().'/file');
	 */
	public function unzip ($open_place)
	{
		if (!is_file($open_place)) $open_place = $this->_rootDir.$open_place;

		if ($this->_open_flag) {
			if (!is_readable($open_place)) {
				return $this->throwError ('書き込めないディレクトリです。');
			}
			if(true === $this->_zip->extractTo($open_place)) {
				return true;
			} else {
				return $this->throwError ($this->_zipError (100));
			}
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * 指定されたフォルダをzipとしてダウンロードさせる
	 *
	 * @param  string  $file_path  ダウンロードさせたいファイルのパス
	 * @param  string  $new_name   ダウンロード時のzip名
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->download (storage_path ().'/zip/list.zip');
	 */
	public function download ($file_path)
	{
		if (!is_file($file_path)) $file_path = $this->_rootDir.$file_path;

		if (preg_match("/\.zip$/i",$file_path)) {
			\feke\util\Header::downloadZip ($file_path);
			return;
		} else {
			return $this->throwError ($this->_zipError (105),$file_path);
		}
	}

	/**
	 * アーカイブを閉じる
	 *
	 * @return boolen
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip->close ();
	 */
	public function close ()
	{
		if ($this->_open_flag) {
			$this->_open_flag = false;
			if (true === ($flag = $this->_zip->close())) {
				return true;
			} else {
				return $this->throwError ($this->_zipError (106));
			}
		}
		return $this->throwError ($this->_zipError (100));
	}

	/**
	 * zipへファイルを追加
	 *
	 * @param   string $filename 追加したいファイル名
	 * @param   string $newname  追加したいファイルの新しい名前
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip->addFile ('/images/sky.jpg');
	 */
	public function addFile ($filename, $newname = null)
	{
		if (!is_file($filename)) $filename = $this->_rootDir.$filename;

		if ($this->_open_flag) {
			if (!is_readable($filename)) {
				return $this->throwError (sprintf($this->_zipError (105),$filename));
			}

			if (!$newname) {
				$newname = basename($filename);
			}
			$flag = $this->_zip->addFile($filename, $newname);

			if (true === $flag) {
				return true;
			} else {
				return $this->throwError ($this->_zipError (103));
			}
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zipへデータを追加
	 *
	 * @param   string $filename 追加したいファイル名
	 * @param   string $newname  追加したいデータ内容
	 * @return  成功した時はtrueを返します。
	 * @throws  失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip->addFile ('config.ini', $config_data);
	 */
	public function addData ($filename, $data)
	{
		if (!is_file($filename)) $filename = $this->_rootDir.$filename;

		if ($this->_open_flag) {
			$this->_zip->addFromString($filename, $data);
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zipへディレクトリを追加
	 *
	 * @param   string $filename 追加したいディレクトリ名
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip->addDir ('/font');
	 */
	public function addDir ($dirname)
	{
		if ($this->_open_flag) {
			if(!$this->_zip->addEmptyDir($dirname)) {
		        return $this->throwError (102);
		    }
		    return true;
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zipからファイルポインタを取得
	 *
	 * @param   string $filename 取得したいポインタのzip内のファイル名
	 * @return  成功した時はポインタを返します。
	 * @throws  失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip->getStream ('/sky.jpg');
	 */
	public function getStream ($filename)
	{
		if ($this->_open_flag) {
			if (false === ($fp = $this->_zip->getStream($filename))) {
				return $this->throwError ($this->_zipError (108));
			} else {
				return $fp;
			}
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zipからファイルを取得
	 *
	 * @param   string $filename 取得したいファイルのzip内のファイル名
	 * @return  成功した時はファイルの内容を返します。
	 * @throws  失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip-> getFile ('/sky.jpg');
	 */
	public function getFile ($filename)
	{
		if ($this->_open_flag) {
			if (false === ($data = $this->_zip->getFromName($filename))) {
				return $this->throwError ($this->_zipError (108));
			} else {
				return $data;
			}
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zip からファイルを削除します。
	 *
	 * @param   string $filename 削除したいzip内のファイルのファイル名
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip-> delFile ('/sky.jpg');
	 */
	public function delFile ($filename)
	{
		if ($this->_open_flag) {
			if (false === ($data = $this->_zip->deleteName($filename))) {
				return $this->throwError ($this->_zipError (101));
			} else {
				return true;
			}
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * zi内のファイル名を変更します。
	 *
	 * @param  string $filename 変更したいzip内のファイルのファイル名
	 * @param  string $rename   変更後のファイル名
	 * @return 成功した時はtrueを返します。
	 * @throws 失敗した場合は、Error 例外を投げます。
	 * @example $Zip->open (storage_path ().'/zip/list.zip');
	 *          $Zip-> renameFile ('/sky.jpg', 'sea.jpg');
	 */
	public function renameFile ($filename, $rename)
	{
		if ($this->_open_flag) {
			if (!is_readable($filename)) {
				return $this->throwError (105);
			}
			if (false === ($data = $this->_zip->renameName($filename, $rename))) {
				return $this->throwError ($this->_zipError (104));
			} else {
				return true;
			}
		} else {
			return $this->throwError ($this->_zipError (100));
		}
	}

	/**
	 * 基準操作ディレクトリの設定をします。
	 *
	 * デフォルトでは、''FEKE_ROOT_PATH''が基準となっています。
	 *
	 * @param string $dir_name 各引数の前に加えたいディレクトリ名
	 */
	public function setRoot ($dir_name)
	{
		$this->_rootDir = $dir_name;
	}

	/**
	 * zip関連エラー
	 *
	 * 2桁...ZipArchiveのエラー
	 * 3桁...このクラスのエラーメッセージ
	 *
	 * @param   numeric $code エラーコード
	 * @return string  エラーメッセージ
	 */
	protected function _zipError ($code)
	{
		switch ($code) {
			case 100:
				return 'zip アーカイブが開いていません。';
				break;
			case 101:
				return '指定されたファイル名はzipの中にありません。';
				break;
			case 102:
				return 'ディレクトリが作成できませんでした。';
				break;
			case 103:
				return 'ファイルの追加に失敗しました。';
				break;
			case 104:
				return 'ファイルの名前変更に失敗しました。';
				break;
			case 105:
				return '%sは存在、または読み込めないファイルです。';
				break;
			case 106:
				return 'zipファイルを閉じるのに失敗しました。';
				break;
			case 107:
				return '%s指定されたファイルは、読み込めないか存在しません。';
				break;
			case 108:
				return '解凍に失敗しました。';
				break;
			case 1:
				return '複数ディスクの zip アーカイブはサポートされません。';
				break;
			case 2:
				return '一時ファイルの名前変更に失敗しました。';
				break;
			case 3:
				return 'シークエラー。';
				break;
			case 4:
				return '読み込みエラー。';
				break;
			case 5:
				return 'CRC エラー。';
				break;
			case 6:
				return 'zip アーカイブはクローズされました。';
				break;
			case 7:
				return 'そのファイルはありません。';
				break;
			case 8:
				return 'ファイルが既に存在します。';
				break;
			case 9:
				return 'そのファイルはありません。 ';
				break;
			case 10:
				return 'ファイルが既に存在します。 ';
				break;
			case 11:
				return 'ファイルをオープンできません。 ';
				break;
			case 12:
				return '一時ファイルの作成に失敗しました。';
				break;
			case 13:
				return 'Zlib エラー。';
				break;
			case 14:
				return 'メモリの確保に失敗しました。';
				break;;
			case 15:
				return 'エントリが変更されました。';
				break;
			case 16:
				return '圧縮方式がサポートされていません。';
				break;
			case 17:
				return '予期せぬ EOF です。';
				break;
			case 18:
				return '無効な引数です。 ';
				break;
			case 19:
				return 'zip アーカイブではありません。 ';
				break;
			case 20:
				return '内部エラー。';
				break;
			case 21:
				return '矛盾した Zip アーカイブです。';
				break;
			case 22:
				return 'ファイルを削除できません。';
				break;
			case 23:
				return 'エントリが削除されました。';
				break;
			default:
				return '不明なエラー';
				break;
		}
	}



}