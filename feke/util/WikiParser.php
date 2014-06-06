<?php
/**
 * Part of the Feke framework.
 *
 * @package feke
 * @version 0.4
 * @author Shohei Miyazawa
 * @license GNU General Public License (GPL)
 * @copyright Copyright (c) FekePHP (http://fekephp.com/)
 * @link http://fekephp.com/
 */
namespace feke\util;

/**
 * FekePHP用のwikiパーサークラスです。
 *
 * ちょっとしたHTMLの自動整形にとっても便利です。
 * FekePHP内では、ドキュメンターに使用しています。
 *
 * 一部違いはありますが、[[livedoor Wiki　簡単記法リファレンス>>http://seesaawiki.jp/w/tag_guide/]]を見ていただけるとわかりやすいと思います。
 *
 * **使用方法**
 * {{{php|
 * $text = '変換したいテキスト';
 * //クラスを呼び出して使う場合
 * echo \feke\util\WikiParser::run ($text);
 *
 * //グローバル関数から呼び出して使う場合
 * echo mold_wiki ($text);
 * }}}
 *
 * **主な整形ルール**
 *
 * ***テキスト1***
 * +シングルコーテーション2コで囲むと''フォントが太字''になります。
 * +シングルコーテーション3コで囲むと'''フォントが斜体'''になります。
 * +％2個で囲むと%%フォントに取り消し線%%がつきます。
 * +％3個で囲むと%%%フォントに下線%%%がつきます。
 * {{{php|
 * //各サンプル
 * ''フォントが太字''
 * '''フォントが斜体'''
 * %%フォントに取り消し線%%
 * %%%フォントに下線%%%
 * }}}
 *
 * ***テキスト2***
 * + &color(red){文字}を色付きにします。
 * + 文字の大きさを変更します。
 * {{{php|
 * &color (red) {赤文字}
 * &size (10) {10pxな文字}
 * }}}
 *
 * ***テキスト3***
 * +&sup()｛｝で囲むことで文字を&sup(){上付き}にします。
 * +&sub()｛｝で囲むことで文字を&sub(){下付き}にします。
 * {{{php|
 * &sup(){上付き}
 * &sub(){下付き}
 * }}}
 *
 * ***見出し***
 * - 見出しは、「*」で作成できます。
 * {{{php|
 * * 大　 見出し *
 * ** 中 　見出し **
 * *** 小 　見出し ***
 * }}}
 *
 * ***リスト***
 * -リストは、「-」で作成できます。
 * {{{php|
 * -りんご
 * -ゴリラ
 * ---ラッパ
 * --パンダ
 * -大根
 * }}}
 *
 * ***連番リスト***
 * -連番リストは、「+」で作成できます。
 * {{{php|
 * + 1-1
 * + 1-2
 * ++ 2-1
 * ++ 2-2
 * +++ 3-1
 * + 1-3
 * }}}
 *
 * ***ソースの表示***
 * -ソースを表示したい場合は、[｛｛｛言語名|｝｝｝]で囲います。（実際は半角です）
 * {{{php|
 * ｛｛｛php|
 *      sprint('%s', $aaaa);
 *  ｝｝｝
 *   //※サンプルは全角で代用しています。
 * }}}
 *
 * ***テキストをそのまま表示***
 * -テキストを表示したい場合は、[｛｛｛｝｝｝]で囲います。（実際は半角です）
 * {{{php|
 * ｛｛｛
 *          A
 *         A A
 *        A   A
 *       A     A
 *      AAAAAAAAA
 *  ｝｝｝
 *  //※サンプルは全角で代用しています。
 * }}}
 *
 * ***引用文***
 * -引用文を表示したい場合は、行の先頭に「>」をつけます。
 * {{{php|
 * >こんなかんじに
 * >
 * >閉じ括弧を先頭につけてください。
 *
 *
 * >>バージョンは
 * >>
 *
 * >>>3つあります。
 * >>>
 * }}}
 *
 * ***リンク***
 * -「http(s)://feke...」とURLは記載するだけで自動でリンク化されます。
 * -「［［リンク先URL］］」と記載するとリンク化されます。（実際は半角です）
 * -「［［～～&gt;&gt;リンク先URL］］」と記載するとリンク化されます。（実際は半角です）
 * {{{php|
 *  http://fekephp.com
 *  //<a herf="http://fekephp.com">http://fekephp.com</a>
 *
 *  [[http://fekephp.com]]
 *  //<a herf="http://fekephp.com">http://fekephp.com</a>
 *
 *  [[FekePHP>>http://fekephp.com]]
 *  //<a herf="http://fekephp.com">FekPHP</a>
 * }}}
 *
 * ***画像***
 * -拡張子が「.jpg/.png/.gif」の場合は自動で画像が貼り付けられます。
 * -&amp;ref(画像url,幅,高さ,回りこみ)と指定することができます。
 *{{{php|
 * //画像URL直指定
 * http://fekephp.com/img/top.png
 * //<img src="http://fekephp.com/img/top.png">
 *
 * //幅100px,高さ32px
 * -&amp;ref(http://fekephp.com/img/top.png,100,32)
 * //<img src="http://fekephp.com/img/top.png" style="width:100px;height:32px;">
 *
 *  //幅100px,左回り込み
 * -&amp;ref(http://fekephp.com/img/top.png,100,left)
 * //<img src="http://fekephp.com/img/top.png" style="width:100px;float:left">
 *
 * //高さ32px,右回り込み
 * -&amp;ref(http://fekephp.com/img/top.png,,32,right)
 * //<img src="http://fekephp.com/img/top.png" style="height:32px;float:right">
 * }}}
 *
 * ***表(TABLE)***
 * -「|～～|～～|」と指定することで表を組むことができます。
 * {{{php|
 * |1-1|1-2|1-3|
 * |2-1|2-2|2-3|
 * }}}
 *
 * -「|>|～～|」と指定することで右のセルと結合できます。
 * {{{php|
 * //2-1だったセルと2-2が結合します。
 * |1-1|1-2|1-3|
 * |>|2-2|2-3|
 * }}}
 *
 * -「|^|～～|」と指定することで上のセルと結合できます。
 * {{{php|
 * //2-1だったセルと1-1が結合します。
 * |1-1|1-2|1-3|
 * |^|2-2|2-3|
 * }}}
 *
 * -「|~～～|」と指定することで行全体をヘッダにします。
 * {{{php|
 * //1-1 ～ 1-3がヘッダになります。
 * |~1-1|1-2|1-3|
 * |2-2|2-2|2-3|
 * }}}
 *
 * -「|!～～|」と指定することでそのセルをヘッダにします。
 * {{{php|
 * //1-1 と 2-3がヘッダになります。
 * |!1-1|1-2|1-3|
 * |2-2|2-2|!2-3|
 * }}}
 *
 * -「|[left]～～|」セル内の文字を左に寄せます。
 * -「|[center]～～|」セル内の文字を中央に寄せます。
 * -「|[right]～～|」セル内の文字を右に寄せます。
 * {{{php|
 * //1-1 と 2-1のテキストが左よりになります。
 * |[left]1-1|1-2|1-3|
 * |[left]2-2|2-2|!2-3|
 * }}}
 *
 * -「|～～''~~''～～|」と指定することでセル内の改行を行います。
 * {{{php|
 * //1-1の中で改行します。
 * |1-1~~改行|1-2|1-3|
 * |2-2|2-2|!2-3|
 * }}}
 *
 * -「|[color:red]～～|」,「|[color:#FF0000]～～|」と指定することでセル内の文字色を変更します。
 * {{{php|
 * //1-1のテキストを赤色にします。
 * |[color:red]1-1|1-2|1-3|
 * |2-2|2-2|!2-3|
 * }}}
 *
 * -「|[bgcolor:red]～～|」,「|[bgcolor:#FF0000]～～|」と指定することでセル内の背景色を変更します。
 * {{{php|
 * //1-1の背景を赤色にします。
 * |[bgcolor:red]1-1|1-2|1-3|
 * |2-2|2-2|!2-3|
 * }}}
 *
 *  -「|[size:ピクセル数]～～|」と指定することでセル内の文字の大きさを変更します。
 * {{{php|
 * //1-1のテキストを10pxにします。
 * |[size:10]1-1|1-2|1-3|
 * |2-2|2-2|!2-3|
 * }}}
 *
 *
 *
 * @package feke
 * @subpackage util
 * @config /util/wikiparser
 */
class WikiParser
{
	/**
	 * クラスベース読み込み
	 */
	use\feke\base\ClassBase;

	/**
	 * 設定のオブジェクト
	 *
	 * @var object
	 */
	private static $_config;

	/**
	 * 設定ファイルの読み込み
	 *
	 * @class hide
	 */
	private static function _loadConfig ()
	{
		// 設定ファイル読み込み
		self::$_config = \Feke::loadConfig ('/util/wikiparser', true);
	}

	/**
	 * wiki文法テキストをHTMLへ整形します。
	 *
	 * @param string $text
	 * @param boolen $php trueの場合はphpコードを許可し、falseの場合は強制的に排除します。
	 * @return string 整形されたテキスト
	 */
	public static function run ($text, $php = false)
	{
		if (!isset(self::$_config)) self::_loadConfig ();

		// タブ削除
		$text = str_replace("\t", '', $text);

		// 改行タグを統一
		$order = array("\r\n","\r");
		$text = str_replace($order, "\n", $text);

		// 成形用の無名関数
		$much_function = function  ($array) use( &$mold_tmp)
		{
			$br = null;
			if (strpos($array[0], "\n") === 0)
				$br = "\n";
			if (isset($array[2]))
				return $br . sprintf($mold_tmp, $array[1], $array[2]);
			if ($array[1])
				return $br . sprintf($mold_tmp, $array[1]);
		};

		// 成形用の無名関数2
		$much_function2 = function  ($array) use( &$mold_tmp)
		{
			$br = null;
			if (strpos($array[0], "\n") === 0)
				$br = "\n";
			if (isset($array[2]))
				return $br . sprintf($mold_tmp, $array[2], $array[1]);
			return $br . sprintf($mold_tmp, $array[1], $array[1]);
		};

		// 成形用の無名関数3
		$much_function3 = function  ($array) use( &$mold_tmp)
		{
			$br = null;
			if (strpos($array[0], "\n") === 0)
				$br = "\n";
			$text = strstr($array[0], 'http');
			return $br . sprintf($mold_tmp, $text, $text);
		};

		// 成形用の無名関数4
		$much_function4 = function  ($array) use( &$mold_tmp)
		{
			$br = null;
			if (strpos($array[0], "\n") === 0)
				$br = "\n";
			$text = strstr($array[0], 'http');
			return $br . sprintf($mold_tmp, $text);
		};

		// 成形用の無名関数5
		$much_function5 = function  ($array) use( &$mold_tmp)
		{
			$br = null;
			if (strpos($array[0], "\n") === 0)
				$br = "\n";
			if (strpos($array[1], ',') !== false) {
				$params = explode(',', $array[1]);
				$height = null;
				$width = null;
				if (isset($params[1])) {
					if ($params[1] > 0)
						$width = "width:{$params[1]};";
				}
				if (isset($params[2])) {
					if ($params[2] > 0)
						$height = "height:{$params[2]};";
				}
				$cnt = count($params);
				if (isset($params[$cnt - 1])) {
					$argin = "float:{$params[$cnt - 1]};";
				}
				return "\n<img src=\"{$params[0]}\" style=\"{$argin}{$width}{$height}\">";
			}
			return "\n<img src=\"{$array[1]}\">";
		};


		//ソース部分の抜き出し
		preg_match_all ("/\{\{\{(.*?)\}\}\}/s", $text, $comment_array);
		$comment_array = $comment_array[1];

		$count = -1;
		$function = function ($match) use (&$count) {
			$count++;
			return "{{{_{$count}_}}}";
		};
		$text = preg_replace_callback("/\{\{\{(.*?)\}\}\}/s", $function, $text);


		//phpコード部分を排除
		if ($php === true) {
			preg_match_all ("/<\?(.*?)\?>/s", $text, $php_array);
			$php_array = $php_array[1];
			$count = -1;
			$function = function ($match) use (&$count) {
				$count++;
				return "<?_{$count}_?>";
			};
			$text = preg_replace_callback("/<\?(.*?)\?>/s", $function, $text);
		} else {
			$text = preg_replace("/<\?(.*?)\?>/s", '', $text);
		}

		// 文字を斜体にする（イタリック）
		$mold_tmp = self::$_config->HTML->ITALIC;
		$match = "/[\n]?'''(.*?)'''/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字に下線をつける
		$mold_tmp = self::$_config->HTML->UNDER;
		$match = "/[\n]?%%%(.*?)%%%/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字に取り消し線をつける
		$mold_tmp = "<del>%s</del>";
		$mold_tmp = self::$_config->HTML->DEL;
		$match = "/[\n]?%%(.*?)%%/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字の太字化
		$mold_tmp = self::$_config->HTML->BOLD;
		$match = "/[\n]?''(.*?)''/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字の色
		$mold_tmp = self::$_config->HTML->COLOR;
		$match = "/[\n]?&color[ ]*[(](.*?)[)][ ]*[{](.*?)[}]/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字のサイズ
		$mold_tmp = self::$_config->HTML->SIZE;
		$match = "/[\n]?&size[ ]*[(](.*?)[)][ ]*[{](.*?)[}]/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字の上付き
		$mold_tmp = self::$_config->HTML->SUPER;
		$match = "/[\n]?&sup[ ]*[(][)][ ]*[{](.*?)[}]/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 文字の下付き
		$mold_tmp = self::$_config->HTML->SUB;
		$match = "/[\n]?&sub[ ]*[(][)][ ]*[{](.*?)[}]/s";
		$text = preg_replace_callback($match, $much_function, $text);

		// 画像
		$match = "/[\n]?&ref[ ]*[(](.*?)[)]/";
		$text = preg_replace_callback($match, $much_function5, $text);

		// 画像2
		$match = "/[\n]?#ref[ ]*[(](.*?)[)]/";
		$text = preg_replace_callback($match, $much_function5, $text);

		// リンク1
		$mold_tmp = "<a href=\"%s\" target=\"_blank\">%s</a>";
		$match = "/[\n]?\[\[(.*?)>>(.*?)\]\]/";
		$text = preg_replace_callback($match, $much_function2, $text);

		// リンク2
		$mold_tmp = "<a href=\"%s\" target=\"_blank\">%s</a>";
		$match = "/[\n]?\[\[(.*?)\]\]/";
		$text = preg_replace_callback($match, $much_function2, $text);

		// 画像3
		$mold_tmp = "<img src=\"%s\">";
		$match = "/[^\">]http[s]?:\/\/[0-9a-z_.:;&=+*%$#!?@()~\'\/-]+\.(jpg|gif|png)/i";
		$text = preg_replace_callback($match, $much_function4, $text);

		// リンク3
		$mold_tmp = "<a href=\"%s\" target=\"_blank\">%s</a>";
		$match = "/[^\">]http[s]?:\/\/[0-9a-z_.:;&=+*%$#!?@()~\'\/-]+/i";
		$text = preg_replace_callback($match, $much_function3, $text);

		$data = explode("\n", $text);

		$new_text = null;

		$ul_flag = [0 => false,1 => false,2 => false];
		$ol_flag = [0 => false,1 => false,2 => false];

		$code_flag = false;

		$br_check = false;

		//引用文の作成に使用
		$block_text = null;
		$block_level = null;
		$block_flag = false;

		// ul li 用の関数
		$ul_func = function  ($count, $flag = null) use( &$new_text, &$ul_flag, &$value)
		{
			$cnt = 0;
			foreach ($ul_flag as $key => $que) {
				if ($count > $key)
					continue;
				if ($ul_flag[$key] === true) {
					$ul_flag[$key] = false;
					$new_text .= "\n</ul>";
				}
			}
			foreach ($ul_flag as $key => $que) {
				if ($count > $key) {
					if ($ul_flag[$key] === false) {
						$ul_flag[$key] = true;
						$new_text .= "\n<ul>";
					}
				}
			}
			if ($flag === true)
				$new_text .= "\n<li>{$value}</li>";
		};

		// ol li 用の関数
		$ol_func = function  ($count, $flag = null) use( &$new_text, &$ol_flag, &$value)
		{
			$cnt = 0;
			foreach ($ol_flag as $key => $que) {
				if ($count > $key)
					continue;
				if ($ol_flag[$key] === true) {
					$ol_flag[$key] = false;
					$new_text .= "\n</ol>";
				}
			}
			foreach ($ol_flag as $key => $que) {
				if ($count > $key) {
					if ($ol_flag[$key] === false) {
						$ol_flag[$key] = true;
						$new_text .= "\n<ol>";
					}
				}
			}
			if ($flag === true)
				$new_text .= "\n<li>{$value}</li>";
		};

		// ここから行ごとに処理
		$count = 0;
		while ($count < count($data)) {
			$value = $data[$count];
			$count ++;

			// 引用文3
			if (strpos($value, ">>>") === 0 and $block_level === null) {
				$ul_func(0);
				$ol_func(0);
				if (!$block_flag) {
					$block_text .= preg_replace('/^>>>/', '', $value);
				} else {
					$block_text .= "<br>" . preg_replace('/^>>>/', '', $value);
				}
				$block_flag = true;
				$block_level = 3;
				continue;
			} elseif (strpos($value, ">>>") === 0 and $block_level === 3) {
				$block_text .= "<br>" . preg_replace('/^>>>/', '', $value);
				continue;

			} elseif ($block_flag === true and $block_level === 3) {
				$new_text .= sprintf(self::$_config->HTML->BLOCK3, $block_text);
				$block_text = null;
				$block_flag = false;
				$block_level = null;

				$count--;
				continue;
			}


			// 引用文2
			if (strpos($value, ">>") === 0 and $block_level === null) {
				$ul_func(0);
				$ol_func(0);
				if (!$block_flag) {
					$block_text .= preg_replace('/^>>/', '', $value);
				} else {
					$block_text .= "<br>" . preg_replace('/^>>/', '', $value);
				}
				$block_flag = true;
				$block_level = 2;
				continue;
			} elseif (strpos($value, ">>") === 0 and $block_level === 2) {
				$block_text .= "<br>" . preg_replace('/^>>/', '', $value);
				continue;

			} elseif ($block_flag === true and $block_level === 2) {
				$new_text .= sprintf(self::$_config->HTML->BLOCK2, $block_text);
				$block_text = null;
				$block_flag = false;
				$block_level = null;

				$count--;
				continue;
			}

			// 引用文1
			if (strpos($value, ">") === 0 and $block_level === null) {
				$ul_func(0);
				$ol_func(0);
				if (!$block_flag) {
					$block_text .= preg_replace('/^>/', '', $value);
				} else {
					$block_text .= "<br>" . preg_replace('/^>/', '', $value);
				}
				$block_flag = true;
				$block_level = 1;
				continue;
			} elseif (strpos($value, ">") === 0 and $block_level === 1) {
				$block_text .= "<br>" . preg_replace('/^>/', '', $value);
				continue;

			} elseif ($block_flag === true and $block_level === 1) {

				$new_text .= sprintf(self::$_config->HTML->BLOCK1, $block_text);
				$block_text = null;
				$block_flag = false;
				$block_level = null;

				$count--;
				continue;
			}

			// テーブル
			while (strpos($value, "|") === 0) {
				if (! isset($table)) {
					$table = "\n<table>";
				} else {
					++ $count;
				}
				$params = explode("|", $value);
				$param_count = count($params) - 1;

				if (strpos($value, "|~") === 0) {
					$table .= "\n<thead>";
				}
				$table .= "\n<tr>";
				$h_connect = 1;

				$thead = false;

				foreach ($params as $key => $td) {
					if ($key === 0)
						continue;
					if ($param_count === $key)
						break;
					$option_text = null;
					$style = null;
					$check = false;
					$v_connect = 1;

					// 横セルの結合
					if ($h_connect > 1) {
						$option_text .= " colspan=\"{$h_connect}\"";
					}

					// 縦セルの結合
					$i = 0;
					do {
						// 下の行がテーブル化確認
						if (! isset($data[$count + $i]))
							break;
						if (! strpos($data[$count + $i], "|") === 0)
							break;

							// 下の行を取得
						$under_params = explode("|", $data[$count + $i]);
						if (! isset($under_params[$key]))
							break;
						if ($under_params[$key] === '^') {
							$v_connect ++;
							$i ++;
						} else {
							break;
						}
					} while (1);

					if ($v_connect > 1) {
						$option_text .= " rowspan=\"{$v_connect}\"";
						$v_connect = 1;
					}

					// セル内のスタイル
					if (strpos($td, "[") === 0) {
						// 文字の寄せ
						if (strpos($td, "[left]") !== false) {
							$style .= "text-align:left;";
							$td = str_replace("[left]", '', $td);
						} elseif (strpos($td, "[center]") !== false) {
							$style .= "text-align:center;";
							$td = str_replace("[center]", '', $td);
						} elseif (strpos($td, "[right]") !== false) {
							$style .= "text-align:right;";
							$td = str_replace("[right]", '', $td);
						}

						// 文字の色
						if (strpos($td, "[color:") !== false) {
							preg_match("/\[color:[#0-9a-z]+\]/", $td, $m);
							$td = str_replace($m[0], '', $td);
							$color = str_replace(['[color:',']'
							], '', $m[0]);
							$style .= "color:{$color};";
						}
						if (strpos($td, "[bgcolor:") !== false) {
							preg_match("/\[bgcolor:[#0-9a-z]+\]/", $td, $m);
							$td = str_replace($m[0], '', $td);
							$color = str_replace(['[bgcolor:',']'
							], '', $m[0]);
							$style .= "background-color:{$color};";
						}
						// 文字の大きさ
						if (strpos($td, "[size:") !== false) {
							preg_match("/\[size:[0-9]+\]/", $td, $m);
							$td = str_replace($m[0], '', $td);
							$color = str_replace(['[size:',']'
							], '', $m[0]);
							$style .= "font-size:{$color};";
						}
					}

					// セル内での改行
					$td = str_replace("~~", '<br>', $td);

					if ($style)
						$style = " style=\"{$style}\"";

					if ($td === ">") {
						// 「>」の場合は右のセルと結合
						$h_connect ++;
					} elseif ($td === "^") {
						// 「^」の場合は上のセルと結合
						// 何もしない
						$check = true;
					} elseif (strpos($value, "|~") === 0) {
						// 「"~」が先頭にある場合は行全体をth
						$td = preg_replace('/^~/', '', $td);
						$table .= "<th{$option_text}{$style}>{$td}</th>";
						$thead = true;
						$check = true;
					} elseif (strpos($td, "!") === 0) {
						// 「"！」が先頭にある場合はth
						$td = preg_replace('/^!/', '', $td);
						$table .= "<th{$option_text}{$style}>{$td}</th>";
						$check = true;
					} else {
						// 特に何もなかったら
						$table .= "<td{$option_text}{$style}>{$td}</td>";
						$check = true;
					}

					// セルの書き込みがあった場合
					if ($check) {
						$h_connect = 1;
					}
				}
				$table .= "</tr>";
				if ($thead === true) {
					$table .= "\n</thead>";
				}

				if (isset($data[$count])) {
					$value = $data[$count];
				} else {
					$value = null;
				}
			}
			if (isset($table)) {
				$table .= "\n</table>";
				$new_text .= "{$table}";
				$table = null;
				continue;
			}

			$old_text = $new_text;
			// リスト系
			if (strpos($value, "-") === 0) {
				$match = null;
				for ($i = 1; $i <= 3; $i ++) {
					$match .= '-';
					if (preg_match("/^{$match}[^-]/", $value)) {
						// ul-li(1)の作成
						$value = preg_replace("/^{$match}/", '', $value);
						$ol_func(0);
						$ul_func($i, true);
					}
				}
			} elseif (strpos($value, "+") === 0) {
				$match = null;
				for ($i = 1; $i <= 3; $i ++) {
					$match .= '\+';
					if (preg_match("/^{$match}[^+]/", $value)) {
						// ul-li(1)の作成
						$value = preg_replace("/^{$match}/", '', $value);
						$ul_func(0);
						$ol_func($i, true);
						break;
					}
				}
			}

			if ($old_text === $new_text) {
				// タグを締める
				$ul_func(0);
				$ol_func(0);
			} else {
				continue;
			}

			// 見出し
			if (strpos($value, "*") === 0) {
				$match = null;
				$type = null;
				for ($i = 1; $i <= 3; $i ++) {
					$match .= '\*';
					if (preg_match("/^{$match}([^*]+){$match}$/", $value, $m)) {
						$type = "TITLE_{$i}";
						$new_text .= "\n".sprintf(self::$_config->HTML->{$type}, $m[1]);
						break;
					}
				}
				if ($type) continue;
			}

			//pタグと改行タグの管理
			if ($value) {
				if ($br_check === false) {
					$br_check = true;
					$new_text .= "\n<p>";
				} else {
					$new_text .= "<br>";
				}
				$new_text .= "\n{$value}";
			} else {
				if ($br_check === true) {
					$br_check = false;
					//$new_text .= "\n</p>";
				}
			}
		}

		//コメント分の置き換え
		foreach ($comment_array as $key => $comment) {
			//コメントの言語
			$lang = null;
			if (preg_match('/^(.*?)[ ]*\|/', $comment, $m)) {
				$lang = $m[1];
				$comment = preg_replace ("/^{$lang}[ ]*\|/",'',$comment);
			}
			if ($lang) $comment = sprintf(self::$_config->HTML->CODE, $lang, h($comment));
			else $comment = sprintf(self::$_config->HTML->PRE, h($comment));

			$new_text = preg_replace ("/(<p>\n)?{{{_{$key}_}}}(\n<\/p>)?/s", $comment, $new_text);
		}

		//phpコードの置き換え
		if ($php === true) {
			foreach ($php_array as $key => $comment) {
				$new_text = preg_replace ("/<\?_{$key}_\?>/s", "<?{$comment}?>", $new_text);
			}
		}

		//最後の調整
		$new_text = preg_replace('/(<\/h\d>)<br>/is','$1<p>',$new_text);
		/*$new_text = str_replace('</ul><br>','</ul>',$new_text);
		$new_text = str_replace('</ol><br>','</ol>',$new_text);
		$new_text = str_replace('</pre><br>','</pre>',$new_text);*/

		return $new_text;
	}
}