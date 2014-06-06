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
 * 可逆可能な暗号化、及び復号を行うクラスです。
 * 暗号化用のソルトは、''\feke\config\CoreConfig''にて設定可能です。
 * &color(red){''必ず変更してください．''}
 *
 * なお、暗号化後にタイプやキーを変更すると、それ以前に発行した暗号との整合性がとれなくなりますのでご注意ください。
 *
 * @package    Feke
 * @subpackage util
 */
class Crypt
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBaseStatic;
	
	/**
	 * 与えられた文字列を暗号化します。
	 *
	 * @param string $value 暗号化する文字列。
	 * @param string $key   暗号化する際に使用するキー。指定がない場合は、CoreConfigのCRYPT_KRYが使用されます。
	 */
	public static function encode ($value, $key = null)
	{
		if (!self::$_Config) self::$_Config = \Feke::config('CRYPT');
		
		//キー
		if (!$key) $key = self::$_Config->key;
		
		//暗号モジュールをオープン
		$td  = mcrypt_module_open(self::$_Config->algorithm, '', self::$_Config->mode, '');
		//キーを作成
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		srand();
		$iv  = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		
		//暗号化処理を初期化
		if (mcrypt_generic_init($td, $key, $iv) < 0) {
		  exit('error.');
		}
		
		//データを暗号化
		$result = base64_encode(mcrypt_generic($td, $value));
		
		//暗号化モジュール使用終了
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return $result;
	}
	
	/**
	 * 与えられた文字列を複合します。
	 *
	 * @param string $value 複合する文字列。
	 * @param string $key   複合する際に使用するキー。指定がない場合は、CoreConfigのCRYPT_KRYが使用されます。
	 */
	public static function decode ($value, $key = null)
	{
		if (!self::$_Config) self::$_Config = \Feke::config('CRYPT');
		
		//キー
		if (!$key) $key = self::$_Config->key;
		
		//暗号モジュールをオープン
		$td  = mcrypt_module_open(self::$_Config->algorithm, '', self::$_Config->mode, '');
		//キーを作成
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		srand();
		$iv  = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		
		//暗号化処理を初期化
		if (mcrypt_generic_init($td, $key, $iv) < 0) {
		  exit('error.');
		}
		
		$result = mdecrypt_generic($td, base64_decode($value));
		
		//暗号化モジュール使用終了
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return $result;
	}
}
