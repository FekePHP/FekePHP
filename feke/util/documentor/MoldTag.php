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

trait MoldTag
{
	/**
	 * phpのコメントタグを取得
	 *
	 * @param $text string タグを切り抜きたいソース
	 * @return string 成功時は取得したタグの配列、失敗した場合はfalseを返します。
	 */
	private function _getTag ($text, $type)
	{
		$tag = array();

		if (preg_match("/@(.*)/s", $text, $m)) {
			$array = preg_split('/[@]/',$m[0]);
			array_shift($array);

			foreach ($array as $value) {

				$data = preg_split('/[ \t]/',$value);

				//改行を統一
				$data = preg_replace ("/\r\n|\r/", "\n", $data);

				$data[0] = preg_replace ("/\r\n+$|\n+$|\r+$/", '',$data[0]);
				$tag[] = $data;
			}
			return $tag;
		}
		return false;
	}

	/**
	 * phpのコメント内タグの削除とコメントの整形を行います。
	 *
	 * @param $text string コメントタグを削除したいソース
	 * @return string 成功時は整形したコメント、失敗した場合はfalseを返します。
	 */
	private function _deleteTag ($text)
	{
		if(!is_string($text)) return false;

		$text = str_replace(["\r\n","\r"],"\n",$text);

		$new_text = preg_replace('/[@](.*)$/s', '', $text);

		do {
			$text = $new_text;
			$new_text = preg_replace('/^\n/s', '', $text);
		} while ($new_text !== $text);

		do {
			$text = $new_text;
			$new_text = preg_replace("/\n\n\n/s", "\n\n", $text);
		} while ($new_text !== $text);
		return $new_text;
	}

	/**
	 * コメントタグをHTMLへ整形する。
	 *
	 * phpファイルのコメント内に記載されているタグを解析しオブジェクト化します。
	 * 解析対象タグは、
	 *     -param
	 *     -return
	 *     -throws
	 *     -example
	 *     -link
	 *     -
	 * です。
	 *
	 * @param object $tag
	 * @return object 整形したタグのデータオブジェクトを返します。
	 */
	private function _moldTag ($tag = null, $data = null)
	{
		//宣言
		$obj = new \stdClass();
		//関数・メソッドのパラメータ部のHTML
		$obj->method_params = null;
		//関数・メソッドのパラメータ
		$obj->param_list    = null;
		//返り値
		$obj->return_list   = null;
		//サンプルコード
		$obj->sample_code   = null;
		//リンクリスト
		$obj->link_list     = null;
		//プラグインリスト
		$obj->plugin_list   = null;
		//静要素
		$obj->static        = null;
		//呼ばれている場所
		$obj->dir           = null;
		//投げる例外
		$obj->throw_list    = null;
		//HTML成形時に使用したいcssのクラス要素
		$obj->style_class   = null;
		//設定ファイル
		$obj->config_file   = null;

		//引数が指定されていない場合は、プロパティから取得
		/*
			if (!$tag) {
		if (isset($this->_result->class->tag)) {
		$tag  = $this->_result->class->tag;
		} elseif (isset($this->_result->file->tag)) {
		$tag  = $this->_result->file->tag;
		}
		}*/
		if (!$tag) return $obj;

		//余分な配列を詰める関数
		//スペースごとに配列化されているので、余分なスペース分を詰める感じ
		$function = function ($param) {
			$new_param = array();
			foreach ($param as $text) {
				if (!$text) continue;
				$new_param[] = $text;
			}
			return $new_param;
		};

		//タグの解析結果からオブジェクトを生成
		foreach ($tag as $param) {
			if ($param[0] === 'param') {
				//余分な配列を詰める
				$param = $function ($param);

				$name = null;
				$type = null;
				$into = null;
				$text = null;

				//変数名
				if (isset($param[2])) $name = preg_replace('/^\s+|\s+$/','',$param[2]);
				//型
				if (isset($param[1])) $type = $param[1];
				//内容
				if (isset($param[3])) {
					foreach ($param as $key =>$value) {
						if ($key < 3) continue;
						$text .= " {$value}";
					}
					$into = preg_replace("/^[ ]?/m", '', $text);
				}

				//デフォルトの値

				if (is_value(isset($data->argument_value[$name]))) {
					$deff_value = $data->argument_value[$name];
				} else {
					$deff_value = "必須";
				}

				$obj->param_list .= sprintf(self::$_config->HTML->PARAM_TR, $name, $type, $deff_value, $into);

				if (!$obj->method_params) $obj->method_params = "{$name}";
				else $obj->method_params .= ", {$name}";

				if ($deff_value !== '必須') {
					$obj->method_params .= " = {$deff_value}";
				}

			} elseif ($param[0] == 'return') {
				//返り値のタグ
				$obj->return_list .= "<dl>";
				if (isset($param[1])) $obj->return_list .= "<dt>{$param[1]}</dt>";
				if (isset($param[2])) $obj->return_list .= "<dd>{$param[2]}</dd>";
				$obj->return_list .= "</dl>";

			} elseif ($param[0] == 'example') {
				//サンプルコードのタグ
				if (isset($param[1])) {
					$param[0] = "";
					$text = null;
					foreach ($param as $value) {
						$text .= " {$value}";
					}
					preg_match ("/^\s/",$text, $space);
					$text = preg_replace("/^{$space[0]}{7}/m", '', $text);

					$obj->sample_code .= sprintf(self::$_config->HTML->SAMPLE_CODE, $text);
				}

			}elseif ($param[0] == 'link') {
				//参考リンクのタグ
				if (isset($param[1])) {
					if (isset($param[2])) $title = $param[2];
					else $title = $param[1];
					$obj->link_list .= "<li><a href=\"{$param[1]}\" target=\"_blank\">{$title}</a></li>";
				}

			} elseif ($param[0] == 'plugin') {
				//関連プラグインのタグ
				//余分な配列を詰める
				$param = $function ($param);

				if (isset($param[1])) {
					$plugin_name = preg_replace("/\n$/m", '', $param[1]);
					if (isset($param[2])) {
						$plugin_title = preg_replace("/\n$/m", '', $param[2]);
					} else {
						$plugin_title = $plugin_name;
					}


					if (false !== ($file_path = \feke\core\ClassLoader::classPlace($plugin_name))) {
						$link = realpath($file_path);
						$match = realpath(root_path());
						$link = str_replace($match, '', realpath($link));
						$link = str_replace('\\', '-', $link);
						$link = preg_replace('/^-|\.php/','', $link);
						$link = "<a href=\"path_{$link}_php\">{$param[1]}</a>";
					} else {
						$link = $param[1];
					}

					$obj->plugin_list .= "<li>{$link}</li>";
				}
			}elseif ($param[0] == 'throws') {
				//例外のタグ
				//余分な配列を詰める
				$param = $function ($param);

				if (isset($param[1])) {
					$throw_into = null;
					$throw_class =  $param[1];
					if(isset($param[2])) $throw_into = $param[2];
					$obj->throw_list .= "<dt>{$throw_class}</dt><dd>{$throw_into}</dd>";
				}

			} elseif ($param[0] == 'class') {
				//cssのクラスのタグ
				//余分な配列を詰める
				$param = $function ($param);

				if(isset($param[1])) $obj->style_class = preg_replace("/\n+$/", '', $param[1]);

			} elseif ($param[0] == 'config') {
				if (!$obj->config_file) $obj->config_file   = array();
				//設定ファイルのタグ
				//余分な配列を詰める
				$param = $function ($param);
				if(isset($param[1])) $obj->config_file[] = preg_replace("/\n+$/", '', $param[1]);
			}
		}
		//パラメータリストをくくるHTML
		if ($obj->param_list) $obj->param_list = sprintf(self::$_config->HTML->PARAM_TABLE, $obj->param_list);

		return $obj;
	}

}