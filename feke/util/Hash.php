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
 * ハッシュを発行するクラスです。
 *
 * クラス内で使用するハッシュのタイプと
 * ハッシュ用のソルトは、''\feke\config\CoreConfig''にて設定可能です。
 * &color(red){''必ず変更してください．''}
 *
 * なお、ハッシュ発行後にタイプやキーを変更すると、それ以前に発行したハッシュとの整合性がとれなくなりますのでご注意ください。
 *
 * @package    Feke
 * @subpackage util
 */
class Hash
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBaseStatic;
	
	/**
	 * ハッシュを取得します。
	 *
	 * @param srring $word ハッシュ化する文字列
	 * @return ハッシュ化した文字列を返します。
	 * @example \feke\util\Hash::getSha($word);
	 */
	public static function getSha ($word)
	{
		if (!self::$_Config) self::$_Config = \Feke::config('HASH');
		
		return hash(self::$_Config->type, $word);
	}

	/**
	 * 固定設定値を加えたハッシュ処理をします。
	 *
	 * パスワードの生成等に使用する事ができるメソッドです。
	 * セキュリティの根幹にかかわりそうですので，必要に応じて変更してください。
	 *
	 * より強度を求める場合は、ユニークの設定ができる''getHashPassword()''使用すると良いかもしれません。
	 *
	 * @param srring $word ハッシュ化する文字列
	 * @return ハッシュ化した文字列を返します。
	 * @example \feke\util\Hash::getHash ($word);
	 */

	public static function getHash ($word)
	{
		if (!self::$_Config) self::$_Config = \Feke::config('HASH');
		
		//パスワードハッシュ用のkoshou
		$papar = self::$_Config->key;
		$salt = self::getSha($word);
		$word = $word;

		$hash = self::_srtech ($salt, $word, $papar);
		return $hash;
	}

	/**
	 * 固定設定値とユニークを加えたハッシュを取得します。
	 *
	 * パスワードの生成等に使用する事ができるメソッドです。
	 * セキュリティの根幹にかかわりそうですので，必要に応じて変更してください。
	 *
	 * $saltは、個体に応じて変更することで強度を上げることが可能です。
	 * ただし、紛失した場合は復元不可能ですので、''getHash()''と使い分けることをおすすめします。
	 *
	 * @param srring $word ハッシュ化する文字列
	 * @param string $salt ハッシュ化する際に加えるユニークな文字列
	 * @return ハッシュ化した文字列を返します。
	 * @example \feke\util\Hash::getHashPassword ($word,$salt);
	 */
	public static function getHashPassword ($word ,$salt)
	{
		if (!self::$_Config) self::$_Config = \Feke::config('HASH');
		
		//パスワードハッシュ用のkoshou
		$papar = self::$_Config->key;
		$salt = self::getSha($salt);
		$word = $word;

		$hash = self::_srtech ($salt, $word, $papar);

		return $hash;
	}

	/**
	 * ストレッチング
	 * $iの数値を上げれば，線形的に耐性が上がります．
	 *
	 * @param unknown $salt
	 * @param unknown $word
	 * @param unknown $papar
	 * @return unknown
	 */
	private static function _srtech ($salt, $word, $papar)
	{
		if (!self::$_Config) self::$_Config = \Feke::config('HASH');
		
		$hash = $word;
		for ($i=0; $i<self::$_Config->strech; $i++) {
			$hash = self::getSha($salt.$hash.$papar);
		}
		return $hash;
	}
}
