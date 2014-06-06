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

use feke\config\Config as C;
use feke\config\coreConfig as CC;

/**
 * ページ送りを作成するクラスです。
 *
 * **プラグインとして読み込み**
 * {{{php|
 * use \plugin\Pager
 * }}}
 *
 * **使用例**
 * {{{php|
 * //インスタンス作成
 * $Pager = \Feke::load('Pager', 'util');
 *
 * //対象数120件、1ページあたり10件、getを使用してページを作成する場合
 * echo $Pager->get (120, 10 ,false);
 * }}}
 *
 * @package    feke
 * @subpackage util
 * @plugin \plugin\Pager
 * @config /util/pager
 */
class Pager
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * ページ移管先のurl
	 * @var string
	 */
	private $_url;

	/**
	 * ゲット部分を除いたurl
	 * @var string
	 */
	private $_url_noGet;

	/**
	 * url上のゲット部分
	 * @var string
	 */
	protected $_url_query;

	/**
	 * url上のゲット部分
	 * @var string
	 */
	protected $_config;

	/**
	 * コンストラクタ
	 * @class hide
	 */
	public function __construct()
	{
		$this->_url = \feke\util\Url::obj ();
		$this->_url_noGet = \feke\util\Url::noGet ();
		$this->_url_query =  \feke\util\Url::get ();
		$this->_config = \Feke::loadConfig('/util/pager',true);
	}

	/**
	 * ページ送り作成を作成します。
	 *
	 * URLは指定がない場合は現在のURLを参考しにて作成します。
	 * $typeがtrueの場合はURLパラメータでページ数を取得し、falseの場合は$_GETを使用してページ送りを作成します。
	 *
	 * @param numeric $count    対象の数量
	 * @param numeric $view_num 1ページあたりの数量
	 * @param boolen  $type     使用するページ数の判別方法
	 * @return 整形したHTML
	 * @example //対象数120件、1ページあたり10件、getを使用してページを作成する場合
	 *          echo $Pager->get (120, 10 ,false);
	 */
	public function get ($count, $view_num, array $option = array())
	{
		if (!is_numeric($count)) $this->throwError("[numeric:count:{$count}]");
		if (!is_numeric($view_num)) $this->throwError("[numeric:view_num:{$view_num}]");

		//オプションの整理
		//ページ数の取得方法
		if (isset($option['param_type']) and !is_true($option['param_type'])) {
			$type = false;
		} else {
			$type = true;
		}

		//現在のURL(getなし)
		if (isset($option['url'])) {
			$noGet_url = $option['url'];
		} else {
			$noGet_url = $this->_url_noGet;
		}
		if (!preg_match('/\/$/',$noGet_url)) {
			$noGet_url .= '/';
		}

		//総ページ数
		$page_sum = ceil($count / $view_num);

		//ダミーファイル名を削除
		$damy_name = $this->_config->CONFIG->DAMY_FILE_NAME;
		$match = "/\/{$damy_name}/";
		$noGet_url = preg_replace ($match,'',$noGet_url);

		//ページURLの後半を生成
		$urlQuery = $this->_url_query;
		if (isset($urlQuery)) {
			if ($urlQuery) $urlQuery = "{$damy_name}?{$urlQuery}";
		}

		//ページ数取得
		$page_match = $this->_config->CONFIG->PAGE_MATCH;
		if ($type) {
			preg_match("/{$page_match}_[0-9]+/u", $this->_url_noGet ,$page);
			if (isset($page[0])) {
				$page_num = str_replace("{$page_match}_",'',$page[0]);
			}

			//ページパラメータ削除
			$noGet_url = preg_replace ("/\/{$page_match}_[0-9]{1,}/",'',$noGet_url);

		} else {
			preg_match("/{$page_match}=[0-9]+/u", $urlQuery ,$page);
			if (isset($page[0])) {
				$page_num = str_replace("&{$page_match}=",'',$page[0]);
			}

			//ページパラメータ削除
			$urlQuery = preg_replace ("/&?{$page_match}=[0-9]{1,}/",'',$urlQuery);echo $urlQuery ;

			//get部がない場合
			if (!$urlQuery) {
				$urlQuery = "{$damy_name}?";
			} elseif ($urlQuery !== "{$damy_name}?") {
				$urlQuery .= '&';
			}
		}

		if (!isset($page_num)) {
			$page_num = 1;
		}

		//表示ページリスト数
		$view_cnt = $this->_config->CONFIG->PAGE_COUNT;

		$page_list = null;

		//合計10ページ以下
		if ($page_sum < $view_cnt) {
			for ( $i = 1 ; $i <= $page_sum ; $i++ ) {
				if ($i == $page_num) {
					$put_html = $this->_config->HTML->PAGE_ACTIVE;
				} else {
					$put_html = $this->_config->HTML->PAGE_NORMAL;
				}
				if ($type) {
					$page_list .= sprintf($put_html, "<a href=\"{$noGet_url}{$page_match}_{$i}/{$urlQuery}\">{$i}</a>");
				} else {
					$page_list .= sprintf($put_html, "<a href=\"{$noGet_url}{$urlQuery}{$page_match}={$i}\">{$i}</a>");
				}
			}
		}
		//それ以上
		else {
			//1ページ目
			if ($page_num == 1) {
				$put_html = $this->_config->HTML->PAGE_ACTIVE;
			}else {
				$put_html = $this->_config->HTML->PAGE_NORMAL;
			}
			if ($type) {
				$first_page = sprintf($put_html, "<a href=\"{$noGet_url}{$page_match}_1/{$urlQuery}\">1</a>");
			} else {
				$first_page = sprintf($put_html, "<a href=\"{$noGet_url}{$urlQuery}{$page_match}=1\">1</a>");
			}

			$page_list .= sprintf($this->_config->HTML->PAGE_FIRST, $first_page);

			//ページ範囲の調整
			if ($page_num <= $view_cnt / 2) {
				$start = 2;
			} else if ($page_sum - $page_num <= $view_cnt / 2) {
				$start = $page_sum - 1 - $view_cnt;
			} else {
				$start = $page_num - $view_cnt / 2;
			}

			for ( $i = $start; $i <= $start + $view_cnt ; $i++ ) {
				if ($i < 1) continue;
				if ($i == $page_num) {
					$put_html = $this->_config->HTML->PAGE_ACTIVE;
				}else {
					$put_html = $this->_config->HTML->PAGE_NORMAL;
				}

				if ($type) {
					$page_list .= sprintf($put_html, "<a href=\"{$noGet_url}{$page_match}_{$i}/{$urlQuery}\">{$i}</a>");
				} else {
					$page_list .= sprintf($put_html, "<a href=\"{$noGet_url}{$urlQuery}{$page_match}={$i}\">{$i}</a>");
				}

			}

			//最終ページ
			if ($page_sum == $page_num) {
				$put_html = $this->_config->HTML->PAGE_ACTIVE;
			}else {
				$put_html = $this->_config->HTML->PAGE_NORMAL;
			}
			if ($type) {
				$last_page = sprintf($put_html, "<a href=\"{$noGet_url}{$page_match}_{$page_sum}/{$urlQuery}\">{$page_sum}</a>");
			} else {
				$last_page = sprintf($put_html, "<a href=\"{$noGet_url}{$urlQuery}{$page_match}={$page_sum}\">{$page_sum}</a>");
			}
			$page_list .= sprintf($this->_config->HTML->PAGE_LAST, $last_page);
		}
		//総件数
		if ($this->_config->HTML->PAGE_COUNT) {
			$page_list = sprintf($this->_config->HTML->PAGE_COUNT, $page_list,$count);
		}

		return $page_list;
	}
}