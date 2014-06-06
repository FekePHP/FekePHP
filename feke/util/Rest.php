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
 * Rest出力用クラスです。
 *
 * @package    Feke
 * @subpackage util
 * @plugin     \plugin\Rest
 */

class Rest
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * 第一配列で使用する配列名
	 *
	 * クッキーの第一配列で使用する名前です．
	 * 他のアプリケーションと衝突しにくくなります．
	 *
	 * デフォルトでは，
	 * /app/config/config.phpの「COOKIE_MAME」定数が使用されます．
	 *
	 * @var string
	 */
	private $_outputType;

	public function __construct ()
	{
		$this->_Config = \Feke::loadConfig('/util/rest');

		$this->_outputType = $this->_Config->default_type;
	}

	/**
	 * セットします。
	 * @param string  $filed
	 * @param mixed   $value
	 * @class hide
	 */
	public function __set ($name, $value)
	{
		return $this->set ($name, $value);
	}

	/**
	 * 要素をセットします。
	 *
	 * @param string $name セットしたい要素名
	 * @param mixed  $value セットしたい値
	 * @param string $expire
	 * @param string $domain
	 * @example //名前空間使用時
	 *          \feke\util\Rest::set ('name','feke');
	 *
	 *          //プラグイン使用時
	 *          $this->Rest->set ('name','feke');
	 */
	public function set ($name, $value)
	{
		if (strpos($name, '_') === 0) $this->throwError ('最初がアンダーバーの変数名は使用できません。');

		//クラスの場合はそのまま

		//オブジェクトの場合は配列へ
		if (!is_scalar($value)) {
			$value = to_array($value);
		};

		if (strpos($name, '.') === false) {
			//ドット記法がない場合
			$this->_data[$name] = $value;
		} else {
			//ドット記法がある場合
			$params = explode('.', $name);

			$count = count($params);

			$add_value = $value;
			for ($i = $count - 1; $i >= 0; $i--) {
				$add_array = array();
				$add_array[$params[$i]] = $add_value;
				$add_value = $add_array;
			}

			$this->_data = array_merge_recursive($add_value, $this->_data);
		}
		return true;
	}

	/**
	 * 出力形式を変更
	 */
	public function outputType ($type)
	{
		$this->_outputType = $type;
	}

	/**
	 * 出力する
	 */
	public function output ()
	{
		$OutPut = \Feke::loadUtil ('Output');
		$OutPut->output ($this->_data, $this->_outputType, true);
	}

}