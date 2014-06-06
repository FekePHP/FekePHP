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
 * ブラウザに向けたヘッダーを出するクラスです。
 *
 * **使用方法**
 * {{{php|
 * //rss用のヘッダーを出力
 * \feke\util\header::rss(true);
 * }}}
 * @package    feke
 * @subpackage util
 */

class Header
{

	/**
	 * zipをクライアントへダウンロードさせるヘッダです。
	 *
	 * @param string $file_path ダウンロードさせるzipパス
	 */
	public static function downloadFile ($file_path)
	{
		header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($file_path));
		file_get_contents($file_path);
	}

	/**
	 * zipをクライアントへダウンロードさせるヘッダです。
	 *
	 * @param string $file_path ダウンロードさせるzipパス
	 */
	public static function downloadText ($file_name, $input_data)
	{
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen($input_data));
		echo $input_data;
	}


	/**
	 * 指定されたヘッダを出力します。
	 *
	 * @param string 出したいMineTypeを指定
	 */
	public static function any ($type)
	{
		header('Content-Type: '.$type);
	}

	/**
	 * rss用のヘッダです。
	 *
	 * @param boolen $flag trueの場合はアプリケーション用として出力
	 */
	public static function rss ($flag = null)
	{
		if ($flag) header('Content-Type: application/rss+xml; charset=utf-8');
		else header('Content-Type: text/xml');
	}


	/**
	 * xml用のヘッダです。
	 */
	public static function xml ($flag = null)
	{
		if ($flag) header("Content-Type: application/xml; charset=utf-8");
		header("Content-Type: text/xml");
	}

	/**
	 * htmlのヘッダです。
	 */
	public static function html ($flag = null)
	{
		header("Content-Type: text/html; charset=utf-8");
	}

	/**
	 * テキストのヘッダ
	 */
	public static function text ($flag = null)
	{
		header("Content-Type: text/plain; charset=utf-8");
	}

	/**
	 * PDFのヘッダ
	 */
	public static function pdf ($flag = null)
	{
		header('Content-Type: application/pdf');
	}

	/**
	 * javascriptのヘッダです。
	 */
	public static function js ($flag = null)
	{
		header('Content-Type: application/x-javascript');
	}

	/**
	 * cssのヘッダです。
	 */
	public static function css ($flag = null)
	{
		header('Content-Type: text/css');
	}

	/**
	 * cssのヘッダです。
	 */
	public static function csv ($flag = null)
	{
		header('Content-Type: text/plain');
	}

	/**
	 * iniのヘッダです。
	 */
	public static function ini ($flag = null)
	{
		header('Content-Type: text/plain');
	}

	/**
	 * jpgのヘッダです。
	 */
	public static function jpg ($flag = null)
	{
		header('Content-Type: image/jpeg');
	}

	/**
	 * pngのヘッダです。
	 */
	public static function png ($flag = null)
	{
		header('Content-Type: image/png');
	}

	/**
	 * gifのヘッダです。
	 */
	public static function gif ($flag = null)
	{
		header('Content-Type: image/gif');
	}

	/**
	 * bmpのヘッダ
	 */
	public static function bmp ($flag = null)
	{
		header('Content-Type: image/bmp');
	}

	/**
	 * zipのヘッダです。
	 */
	public static function zip ($flag = null)
	{
		header('Content-Type: application/zip');
	}

	/**
	 * lzhのヘッダです。
	 */
	public static function lzh ($flag = null)
	{
		header('Content-Type: application/x-lzh');
	}

	/**
	 * .tar .tgz のヘッダです。
	 */
	public static function tar ($flag = null)
	{
		header('Content-Type: application/x-tar');
	}

	/**
	 * .tar .tgz のヘッダです。
	 */
	public static function tgz ($flag = null)
	{
		header('Content-Type: application/x-tar');
	}

	/**
	 * mp3のヘッダです。
	 */
	public static function mp3 ($flag = null)
	{
		header('Content-Type: audio/mpeg');
	}

	/**
	 * mp4のヘッダです。
	 */
	public static function mp4 ($flag = null)
	{
		header('Content-Type: audio/mp4');
	}

	/**
	 * wavのヘッダです。
	 */
	public static function wav ($flag = null)
	{
		header('Content-Type: audio/x-wav');
	}

	/**
	 * midiのヘッダです。
	 */
	public static function midi ($flag = null)
	{
		header('Content-Type: audio/midi');
	}

	/**
	 * flashのヘッダです。
	 */
	public static function flash ($flag = null)
	{
		header('Content-Type: application/x-shockwave-flash');
	}

	/**
	 * mpegのヘッダです。
	 */
	public static function mpeg ($flag = null)
	{
		header('Content-Type: video/mpeg');
	}

	/**
	 * wmvのヘッダです。
	 */
	public static function wmv ($flag = null)
	{
		header('Content-Type: video/x-ms-wmv');
	}

	/**
	 * JSONのヘッダです。
	 *
	 * @param boolen $flag trueの場合はアプリケーション用として出力
	 */
	public static function json ($flag = null)
	{
		if ($flag) header("Content-Type: application/json; charset=utf-8");
		else header("Content-Type: text/javascript; charset=utf-8");
	}

	/**
	 * JSONのヘッダです。
	 *
	 * @param boolen $flag trueの場合はアプリケーション用として出力
	 */
	public static function yaml ($flag = null)
	{
		if ($flag) header("Content-Type: application/yaml; charset=utf-8");
		else header("Content-Type: text/javascript; charset=utf-8");
	}
}