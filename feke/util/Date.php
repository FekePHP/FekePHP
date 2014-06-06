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
 * 年月日と時間を取得するクラスです。
 *
 * 内部では、DateTimeクラスを使用して取得しています。
 *
 * 各メソッドの$format(日付のフォーマット)は、
 * http://www.php.net/manual/ja/class.datetime.phpドキュメントを参考にしてください。
 *
 * @package    feke
 * @subpackage util
 */

class Date
{
	/**
	 * 現在の日付け，時刻を取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //現在の年と日時
	 *          echo \feke\util\Date::now();//2013-10-12 00:00:00
	 *          //現在の時間のみ
	 *          echo \feke\util\Date::now('H:i:s');//00:00:00
	 */
	public static function now ($format = 'Y-m-d H:i:s')
	{
		$now = new \DateTime;
		return $now->format($format);
	}

	/**
	 * 今日の日付けを取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //今日の日付け
	 *          echo \feke\util\Date::today();//2013-10-12
	 */
	public static function today ($format = 'Y-m-d')
	{
		$now = new \DateTime;
		return $now->format($format);
	}

	/**
	 * 現在の年を取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //現在の年
	 *          echo \feke\util\Date::year();//2013
	 */
	public static function year ($format = 'Y')
	{
		$now = new \DateTime;
		return $now->format($format);
	}

	/**
	 * 現在の月を取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //現在の月
	 *          echo \feke\util\Date::month();//10
	 */
	public static function month ($format = 'm')
	{
		$now = new \DateTime;
		return $now->format($format);
	}

	/**
	 * 現在の日にちを取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //現在の月
	 *          echo \feke\util\Date::month();//12
	 */
	public static function day ($format = 'd')
	{
		$now = new \DateTime;
		return $now->format($format);
	}

	/**
	 * 現在の時刻を取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //現在の時刻
	 *          echo \feke\util\Date::time();
	 */
	public static function time ($format = 'H:i:s')
	{
		$now = new \DateTime;
		return $now->format($format);
	}

	/**
	 * 昨日の日付けを取得します。
	 *
	 * @param string $format 日付を取得するフォーマット
	 * @return string
	 * @example //昨日の日付
	 *          echo \feke\util\Date::yesterday();
	 */
	public static function yesterday ($format = 'Y-m-d')
	{
		$now = new \DateTime('yesterday');
		return $now->format($format);
	}
}
