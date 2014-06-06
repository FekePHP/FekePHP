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

namespace feke\util\documentor;

/**
 * FekePHP用のドキュメンターです。
 *
 * @package    feke
 * @subpackage util
 */

trait MoldHtml
{
	/**
	 * コメントタグをHTMLへ整形する
	 *
	 * @param object $class HTML化をする解析データ
	 * @param object $any   追加解析データ
	 * @return string 成功時は設定ファイルに基づいて整形したHTMLを、失敗した場合はfalseを返します。
	 * @throws \Error
	 */
	private function _moldHtml ($data, $any = null)
	{
		$list = null;

		//タグの解析
		$obj = $this->_moldTag($data->tag, $data);

		//設定が読み込めていない場合は例外
		if (!self::$_config) return $this->throwError ('設定ファイルが読み込めていません。');

		//クラスコメント用
		if ($data->kind === 'class') {
			$list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, "{$data->name} クラス");
			$list .= mold_wiki($data->comment);
		} elseif ($data->kind === 'file') {
			//ファイル情報用
			$list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, "{$data->name} PHPファイル");
			$list .= mold_wiki($data->comment);
		} else {
			//静的オプション用
			if (isset($data->static)) $static = $this->_moldStatic($data->static);
			else $static = null;

			//グローバル関数用
			$function = $this->_moldFunction($data->kind);

			//継承フラグ
			$extend_fg = null;
			if (isset($data->extend_fg)) $extend_fg = $data->extend_fg;
			$extend = $this->_moldExtend($extend_fg);

			$list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, "{$function}{$extend}{$static}{$data->name} ({$obj->method_params})");
			$list .= mold_wiki($data->comment);
		}
		/*
		//phpファイルの存在場所
		if (isset($data->dir)) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'ファイルの場所');
			$list .= "<p>{$data->dir}</p>";
		}*/

		//名前空間の表示
		if (isset($data->namespace)) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'namespace（名前空間）');
			$list .= "<p>{$data->namespace}</p>";
		}

		//継承クラスの表示
		if (isset($data->extends)) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'extends（継承クラス）');

			$link = preg_replace("/\/|\\\\/m", '-', $data->extends);
			$link = preg_replace('/^-/','', $link).'_php';

			$list .= "<p><a href=\"./path_{$link}\">{$data->extends}</a></p>";
		}

		//設定ファイルの解析
		if (isset($obj->config_file)) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, '設定ファイル');

			foreach ($obj->config_file as $config_file_name) {
				$config_type = ['.ini','.yml','.yaml','.json'];

				//ファイルの存在確認
				$file_path = self::compPath ($config_file_name ,'/config', $config_type);

				if ($file_path) {
					$link = str_replace (root_path(), '', $file_path);

					$link = preg_replace("/\/|\\\\/", '-', $link);
					$link = preg_replace('/^-/m','', $link);
					$link = preg_replace('/[.](.*?)$/m',"_$1", $link);

					$link = "<a href=\"./path_{$link}\">/config{$config_file_name}</a>";
				} else {
					$link = "/config{$obj->$config_file_name}";
				}
				$list .= "<p>{$link}</p>";

				$config_table = null;

				//ここからコンフィグファイルの解析
				if ($file_path !== false) {
					//拡張子の取得
					preg_match ("/\.(.*?)$/", $file_path ,$m);
					if (isset($m[1])) {
						$file_extention = $m[1];
					}
					//iniファイルの場合
					if ($file_extention === 'ini') {
						$config_data = $this->analyzeINI ($file_path);
					}
					//設定リストの作成
					foreach ($config_data->data as $name => $section) {
						$config_table .= sprintf ('<tr><th colspan=3>セクション　%s</th></tr>',$name);
						foreach ($section as $param) {
							if (!is_object($param)) continue;

							$config_table .= sprintf ('<tr><th>%s</th><td>%s</td><td>%s</td></tr>',$param->name, h($param->value), $param->comment);
						}
					}
					$config_table = sprintf('<table>%s</table>',$config_table);
				}
			}
		}

		if (isset($any->trait[0])) {
			$trait_list = null;
			foreach ($any->trait as $trait) {
				if (isset($trait->dir)) {
					$link = str_replace(['/',"\\"], '-', $trait->dir);
					$link = preg_replace('/^-|\.php/','', $link);
					$trait_list .= sprintf(self::$_config->HTML->TRAIT_TR, "<a href=\"./path_{$link}_php\">{$trait->name}</a>",$trait->comment);
				} else {
					$trait_list .= sprintf(self::$_config->HTML->TRAIT_TR, $trait->name,$trait->comment);
				}
			}
			//トレイトをくくるHTML
			if ($trait_list) {
				$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'trait');
				$list .= sprintf(self::$_config->HTML->TRAIT_TABLE, $trait_list);
			}
		}

		if ($obj->plugin_list) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, '関連プラグイン');
			$list .= "<ul>{$obj->plugin_list}</ul>";
		}

		//クラス定数
		if (isset($any->const)) {
			$const_list = null;
			foreach ($any->const as $const) {
				$const_list .= sprintf(self::$_config->HTML->CONST_TR, $const->name, mold_wiki($const->comment));
			}
			if ($const_list) {
				$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'const(クラス定数)');
				$list .= sprintf(self::$_config->HTML->CONST_TABLE, $const_list);
			}
		}

		if ($obj->param_list) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'パラメータ');
			$list .= $obj->param_list;
		}

		if ($obj->return_list) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, '返り値');
			$list .= $obj->return_list;
		}

		if ($obj->throw_list) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, '例外');
			$list .= "<dl>{$obj->throw_list}</dl>";
		}

		if ($obj->sample_code) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, 'サンプルコード');
			$list .= $obj->sample_code;
		}

		if ($obj->link_list) {
			$list .= sprintf(self::$_config->HTML->SMALL_TITLE, '参考リンク');
			$list .= "<ul>{$obj->link_list}</ul>";
		}


		if (isset($data->type)) $data = $data->type;
		else $data = null;

		//隠し要素
		if ($obj->style_class) {
			$data = $obj->style_class;
		}

		$list = sprintf(self::$_config->HTML->ELEMENT, $data, $list);
		return $list;
	}

	/**
	 * phpファイルの詳細を作成する。
	 *
	 * クラスがある場合は、クラスの詳細を表示し、ない場合は、ファイルの詳細を表示します。
	 *
	 * @param object $class クラスの解析データ
	 * @return string ファイル、クラスの詳細HTML
	 */
	private function _moldTopHtml ($class)
	{
		//phpファイル内にクラスがある場合
		if(isset($class->class->tag)) {
			return $this->_moldHtml($class->class, $class);
		} elseif (isset($class->file->tag)) {
			//ただのphpの時
			return $this->_moldHtml($class->file, $class);
		}
		return false;
	}

	/**
	 * メソッドリストを作成する。
	 *
	 * @param object $methods メソッドの解析データ
	 * @return string メソッドのリスト
	 */
	private function _moldMethodList ($methods)
	{
		$method_list = null;

		//ソートする
		$public = array();
		$protected = array();
		$private = array();

		foreach ($methods as $data) {
			$method_list .= $this->_moldHtml($data);
		}
		return $method_list;
	}

	/**
	 * グローバル関数リストを作成する。
	 *
	 * @param object $functions 関数の解析データ
	 * @return string グローバル関数のリスト
	 */
	private function _moldFunctionList ($functions)
	{
		$functions_list = null;
		foreach ($functions as $data) {
			$functions_list .= $this->_moldHtml($data);
		}
		return $functions_list;
	}

	/**
	 * スタティック用のHTMLを発行する
	 *
	 * @param boolen $flag
	 * @param string $size アイコンのサイズ
	 * @return string
	 */
	private function _moldStatic ($flag, $size = null)
	{
		$text = null;
		if ($flag === true) {
			if ($size === 's') $text = self::$_config->HTML->STATIC_SMALL;
			else $text = self::$_config->HTML->STATIC;
		} elseif ($size === 's') {
			$text = self::$_config->HTML->SPACE_SMALL;
		}
		return $text;
	}

	/**
	 * スタティック用のHTMLを発行する
	 *
	 * @param boolen $flag
	 * @param  string $size アイコンのサイズ
	 * @return string
	 */
	private function _moldFunction ($flag, $size = null)
	{
		$text = null;
		if ($flag === 'function') {
			if ($size === 's') $text = self::$_config->HTML->FUNCTION_SMALL;
			else $text = self::$_config->HTML->FUNCTION;
		} elseif ($size === 's') {
			$text = self::$_config->HTML->SPACE_SMALL;
		}
		return $text;
	}

	/**
	 * 継承・トレイト用のHTMLを発行する
	 *
	 * @param boolen $flag
	 * @param  string $size アイコンのサイズ
	 * @return string
	 */
	private function _moldExtend ($flag, $size = null)
	{
		$text = null;
		if ($flag === 'trait') {
			if ($size === 's') $text = self::$_config->HTML->TRAIT_SMALL;
			else $text = self::$_config->HTML->TRAIT;
		} elseif ($flag === 'extend'){
			if ($size === 's') $text = self::$_config->HTML->EXTEND_SMALL;
			else $text = self::$_config->HTML->EXTEND;
		} elseif ($size === 's') {
			$text = self::$_config->HTML->SPACE_SMALL;
		}
		return $text;
	}

	/**
	 * phpのロングコメントを取得します。
	 *
	 * @param $text string コメントを切り抜きたいソース
	 * @return string 成功時は取得したコメント、失敗した場合はfalseを返します。
	 */
	private function _getPhpComment ($text)
	{
		$match = "/\/\*\*(.*?)\*\//s";
		if (preg_match_all($match, $text, $m)) {
			$count = count($m[1]) - 1;
			$mold = preg_replace("/^\t/m",'',$m[1][$count]);
			return preg_replace("/^[ ]+[*][ ]?/m", '', $mold);
		}
		return false;
	}

	/**
	 * phpのコメント(文字列・配列可)を削除します。
	 *
	 * @param $text string コメントを削除したいソース
	 * @return string 成功時は取得したコメント、失敗した場合はfalseを返します。
	 */
	private function _deletePhpComment ($text)
	{
		if (is_array($text)) {
			foreach ($text as $key => $value) {
				$value = preg_replace("/\/\*.*?\*\//s", '', $value);
				$text[$key] = preg_replace("/\/\/.*/", '', $value);
			}
		} elseif (is_string($text)) {
			$text = preg_replace("/\/\*.*?\*\//s", '', $text);
			$text = preg_replace("/\/\/.*/", '', $text);
		} else {
			return false;
		}
		return $text;
	}
}