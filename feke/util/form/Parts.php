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
 * Form Parts class
 *
 * フォームのパーツクラスです．
 *
 * これらを使うと少し楽ができるような気がします．
 *
 * @package Feke
 * @subpackage helper
 */
class Parts
{

	/**
	 *
	 * 日付フォーム作成メソッド
	 *
	 * ・'_type' => 'date', のときにこれを実行します
	 *
	 * ・指定サンプル
	 *     array(
	 *         '_type' => 'date',
	 *         'name' => 'up_',
	 *         '_sprit' => 'ja',
	 *         '_sort' => 'dmy',
	 *         '_sprit_place' => 'before',
	 *         '_style' => 'width:80px;|width:50px|width:50px',
	 *
	 * ・$parms['name']
	 *     ・名前属性は,指定された名前+(year or month or day)となります．
	 *     (例) 'name' => 'add_' のとき名前属性は，
	 *              'add_year','add_month','add_day'
	 *          となります．
	 *
	 * ・$parms['_styletype']
	 *     ・input type[text] か select を指定できます
	 *
	 * ・$parms['_sprit']
	 *     ・各フォーム間のラベルは,'_sprit'で指定できます．
	 *     ・配列で渡した場合は，
	 *     ※指定しなかった場合は，'/'となります．
	 *
	 * ・$parms['_sprit_plase']
	 *     ・各フォーム間のラベルの表示場所を指定します．
	 *     ・'before'の場合は前に，'after'の場合は後に表示します．
	 *     ※指定しなかった場合は，'after'となります．
	 *
	 * ・$parms['_sort']
	 *     ・フォームの並び順を指定できます．
	 *     ・'y','m','d'のいずれかで指定してください．
	 *     ・3つすべて指定しなかった場合は，指定したフォームのみ生成します．
	 *     ※指定しなかった場合は，'ymd'となります．
	 *
	 * ・$parms['_style']
	 *     ・年，月，日ごとのスタイルを設定します．
	 *     ・_sortの順にかかわらず，前から，年，月，日と挿入します．
	 *  '_style' => 'width:80px;|width:50px|width:50px',
	 *
	 *
	 * @param unknown $parms
	 * @return void|string|Ambigous <string, unknown>
	 */
	public function date($parms)
	{/*
		//フォーム名の左側
		$name	= $parms['name'];
		//input or select
		$type	= $parms['_styletype'];
		//ラベル
		$sprit	= $parms['_sprit'];
		//ラベルの設置場所
		$place	= $parms['_sprit_place'];
		//フォームの並び順(ymd ,mdy, or dmy)
		$sort	= $parms['_sort'];
		//スタイル
		$style	= $parms['_style'];


		if (!$type)  $type  = 'text';
		if (!$sprit) $sprit = '/';
		if (!$place) $place = 'after';
		//一応コピー
		$add_parms = $parms;

		//スプリット関数
		$sprit_function =
			function ($type) use ($sprit)
			{
				if ($sprit == 'ja') {
					if ($type == 'year') {
						return '年';
					} elseif ($type == 'month') {
						return '月';
					} elseif ($type == 'day') {
						return '日';
					}
				} else {
					if ($type = 'year' or $type = 'month') {
						return $sprit;
					}
				}
				return;
			};


		//並び順
		if (!$sort) {
			$sort = array('year','month','day');
		} else {
			$s_sort = strtolower($sort);
			$s_sort = str_split($s_sort, 1);
			foreach ($s_sort as $key => $value) {
				if ($value == 'y') $s_sort[$key] = 'year';
				elseif ($value == 'm') $s_sort[$key] = 'month';
				elseif ($value == 'd') $s_sort[$key] = 'day';
			}
			$sort = array($s_sort[0],$s_sort[1],$s_sort[2]);
		}

		//スタイルの整形
		if ($style) {
			$style = explode('|', $style);
		} else {
			$style = array('','','');
		}

		//フォームの作成
		$cnt = 0;
		$part = '';
		$add_parms = '';
		//input フォーム
		if ($type == 'text') {
			foreach ($sort as $key) {
				$add_parms = array('name' => $name.$key);
				$add_parms = array('style' => $style[$cnt]);
				if ($place == 'after') {
					$part .= $this->input ($add_parms, text);
					$part .= $sprit_function ($key);
				} elseif ($place == 'before') {
					$part .= $sprit_function ($key);
					$part .= $this->input ($add_parms, text);
				}
					$cnt++;
			}
		}

		return $part;
		*/
	}
}