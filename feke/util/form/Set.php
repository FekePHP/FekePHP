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

namespace feke\util\form;

/**
 * FormSet class
 *
 * フォーム生成クラスの設定用クラスです．
 *
 *
 * @package    Feke
 * @subpackage helper
 */
trait Set
{
	/**
	 * フォームの配列をセット
	 *
	 * @param array $form
	 */
	public function setAll ($parm)
	{
		$this->_params = $parm;
		return $this;
	}

	/**
	 * フォームの配列をセット
	 *
	 * @param array $form
	 */
	public function setValue ($value)
	{
		$this->_in_value = $value;
	}


	/**
	 * エラーメッセージの表示スタイルのセット
	 *
	 * @param array $que = array('front' => '', 'back' => '');
	 */
	public function setErrorStyle ($que)
	{
		$this->_error_style['front'] = $que['front'];
		$this->_error_style['back'] = $que['back'];
	}

	/**
	 * 表示スタイルのセット
	 *
	 * @param array $que = array('front' => '', 'back' => '');
	 */
	public function setFormStyle ($que)
	{
		//テーブルを作成
		if ($que == 'table') {
			$this->_form_style['type'] = 'table';
			$this->_form_style['label'] = "<th>%s</th>";
			$this->_form_style['part'] = "<td>%s</td>";
			$this->_form_style['row'] = "<tr>%s</tr>";
			$this->_form_style['row_num'] = 1;
		}
	}

	/**
	 * _unit のHTMLを変更
	 *
	 * @param unknown $text
	 * @return Form
	 */
	public function setUnitHtml ($text)
	{
		$this->_unitHtml = $text;
		return $this;
	}

	/**
	 * _label のHTMLを変更
	 *
	 * @param unknown $text
	 * @return Form
	 */
	public function setLabelHtml ($text)
	{
		$this->_labelHtml = $text;
		return $this;
	}
}