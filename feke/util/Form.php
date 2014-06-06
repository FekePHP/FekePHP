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
 * HTMLのフォーム関連タグを生成するクラスです。
 *
 * @package    Feke
 * @subpackage helper
 */
class Form
{
	/**
	 * スキップする属性
	 */
	protected static $_skip_target = ['rule','connect','option','glue','before','after','before_no','after_no','label', 'label_no'];

	/**
	 * ドッド記法を配列へ
	 */
	protected static function _dotToArray ($name)
	{
		if (strpos($name, '.') !== false) {
			$params = explode('.', $name);
			$valiable_name = $params[0];
			array_shift($params);
			$name = "{$valiable_name}[".implode('][',$params).']';
		}
		return $name;
	}
	/**
	 * inputの作成
	 *
	 * @param  array
	 * @param  string
	 * @return string
	 */
	public static function input ($parms)
	{
		$set = '';
		foreach ($parms as $key => $value) {
			if ($key === "value" and $parms['type'] == 'checkbox' or $parms['type'] == 'radio') {
				if (is_true($value)) {
					$set .= " checked=\"checked\"";
				}
				continue;
			} elseif ($key === "value") {
				$value = h($value);
			} elseif ($key === "name") {
				$value = self::_dotToArray($value);
			} elseif (in_array($key, self::$_skip_target)) {
				continue;
			}
			
			if (is_string($value)) {
				$set .= " {$key}=\"{$value}\"";
			}
		}
		$form = "<input{$set}>";
		return $form;
	}

	/**
	 * input type="text" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function text ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="hidden" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function hidden ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="password" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function password ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="file" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function file ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="radio" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function radio ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="checkbox" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function checkbox ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="submit" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function submit ($parms)
	{
		return self::input($parms);
	}
	
/**
	 * input type="button" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function button ($parms)
	{
		return self::input($parms);
	}

	/**
	 * input type="reset" の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function reset ($parms)
	{
		return self::input($parms);
	}

	/**
	 * textarea の生成
	 *
	 * @param array $parms
	 * @return string
	 */
	public static function textarea ($parms)
	{
		$set = '';
		$value_text = '';

		foreach ($parms as $key => $value) {
			if ($key === 'value') {
				$value_text = h($value);
			} elseif (in_array($key, self::$_skip_target)) {
				continue;
			}
			$set .= " {$key}=\"{$value}\"";
		}
		$form = "<textarea{$set}>{$value_text}</textarea>";
		return $form;
	}

	/**
	 * select の生成
	 *
	 * @param array $parms
	 * @param string
	 * @return string
	 */
	public static function select ($parms, $selected = NULL)
	{
		$set = '';
		$option = '';
		$value_text = array();

		foreach ($parms as $key => $value) {
			if ($key === 'value') {
				if (is_scalar($value)) {
					$value_text[] = $value;
				} else {
					$value_text = to_array($value);
				}
				continue;
			} elseif (is_int($key)) {
				$set .= " {$value}";
				continue;
			} elseif (in_array($key, self::$_skip_target)) {
				continue;
			}
			$set .= " {$key}=\"".h($value)."\"";
		}
		
		if(isset($parms['option']) and (is_array($parms['option']) or is_object($parms['option']))) {
			foreach ($parms['option'] as $value => $name) {
				if (is_array($name)) {
					$option .= "<optgroup label=\"{$value}\">";
					foreach ($name as $value5 => $name5) {
						if (is_array($value_text) and in_array($value5,$value_text)) {
							$selected = " selected";
						} else {
							$selected = "";
						}
						$option .= "<option value=\"".h($value5)."\"{$selected}>{$name5}</option>";
					}
					$option .= "</optgroup>";
				} elseif (is_string($name)) {
					if (is_array($value_text) and in_array($value,$value_text)) {
						$selected = " selected";
					} else {
						$selected = "";
					}
					$option .= "<option value=\"".h($value)."\"{$selected}>{$name}</option>";
				}
			}
		}
		$select = "<select{$set}>{$option}</select>";
		return $select;
	}
}