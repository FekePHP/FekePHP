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

namespace feke\util\output;

/**
 * Atom 出力クラス
 *
 * 与えられた配列の応じてATOMを作成します。
 *
 * @package    Feke
 * @subpackage util.output
 */
class Atom
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * channel 要素の配列
	 * @var array
	 */
	protected static $_channel;

	/**
	 * item 要素の配列
	 * @var array
	 */
	protected static $_item;

	/**
	 * コンストラクタ
	 *
	 * @param object
	 */
	public function __construct ($config = null)
	{
		if (is_object($config)) {
			$this->_Config = $config;
		} else {
			$this->_Config = \Feke::loadConfig('/util/output',true)->RSS2;
		}
	}

	/**
	 * オプションのセット
	 *
	 * @param array $array
	 */
	public function set_channel ($array)
	{
		self::$_channel = $array;
	}

	/**
	 * アイテムのセット
	 *
	 * @param array $array
	 */
	public function set_item ($array)
	{
		self::$_item = $array;
	}

	/**
	 * Atom出力
	 *
	 * @return string
	 */
	public function output ()
	{
		//インプットの確認
		if ($this->_Config->CHECK_INPUT) {
			if (false !== ($flag = self::_ckeck_input ('CHANNEL', self::$_channel))) {
				return $this->throwError($flag);
			}
		}

		$rss .= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$rss .= "<feed xmlns=\"http://www.w3.org/2005/Atom\" xml:lang=\"ja\">\n";

		//channelの指定
		foreach (self::$_channel as $key => $value) {
			$rss .= "\t\t<{$key}>{$value}</{$key}>\n";
		}

		//entryの指定
		if (is_array(self::$_item)) {
			foreach (self::$_item as $array) {
				//インプットの確認
				if ($this->_Config->CHECK_INPUT) {
					if (false !== ($flag = self::_ckeck_input ('ENTRY', $array))) {
						return $this->throwError($flag);
					}
				}
				$rss .= "\t\t<entry>\n";
				foreach ($array as $key => $value) {
					$rss .= "\t\t\t<{$key}>{$value}</{$key}>\n";
				}
				$rss .= "\t\t</entry>\n";
			}
		}
;
		$rss .= "</feed>\n";

		return $rss;
	}


	/**
	 * 入力された配列の確認
	 *
	 * @param string $type  要素名
	 * @param array  $array 確認したい配列データ
	 */
	protected function _ckeck_input ($type, $array)
	{
		//必須な要素
		$config_name = "MUST_{$type}";
		$must = explode(',', $this->_Config->{$config_name});
		/*
		//許可されている要素
		$config_name = "LEAVE_{$type}";
		$leave = explode(',', self::$_config->{$config_name});
		*/

		//必須なchannelの確認
		if (is_array($must)) {
			foreach ($must as $value) {
				if (!array_key_exists($value, $array)) return sprintf("必須な{$type}要素「%s」がありません。",$value);
			}
		}
		/*
		//許可されているchannel要素か確認
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (!in_array($key, $leave)) return sprintf("「%s」は許可された{$type}要素ではありません。", $key);
			}
		}*/
		return false;
	}
}