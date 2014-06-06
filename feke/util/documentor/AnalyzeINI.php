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
 * @config /util/documentor
 */

trait AnalyzeINI
{
	/**
	 * iniファイルを解析します。
	 *
	 * セクション、またはパラメータの上の部分のコメント表記がコメントとして保存されます。
	 * なお、コメントが席コロン続きでない場合は、パラメータのコメントとして扱われません。
	 * なお、セクションの指定がない部分は、セクション名が「no_section」として返り値にセットされています。
	 *
	 * @param string $filename 解析したいiniファイル名
	 */
	public function analyzeINI ($filename)
	{
		//ファイルデータを取得
		$data = file_get_contents ($filename);

		$data = preg_replace ("/\r\n/", "\n", $data);
		$data = preg_replace ("/\r/", "\n", $data);
		$data = explode ("\n", $data);

		$line_count = 0;
		$comment = null;
		$list = new \stdClass;

		//1階層目でセクションが宣言されていない部分用
		$section = "no_section";
		$list->data = new \stdClass;
		$list->data->{$section} = new \stdClass;

		//ルートパスからのパス
		$match = realpath(root_path());
		$list->dir  = str_replace($match, '', realpath($filename));

		$list->name  = str_replace($match, '', basename(realpath($filename)));

		//ファイルのタイトル
		if (isset($data[0]) and isset($data[1]))
			if (strpos($data[0], ';') === 0 and $data[1] == "") {
				$list->title = preg_replace('/^;/', '', $data[0]);
			}

		do {
			$text = $data[$line_count];
			//セクション
			if (preg_match("/^\[(.*?)\]/",$text, $m)) {
				$section = $m[1];
				$list->data->{$section} = new \stdClass;
				$list->data->{$section}->comment = $comment;

				//コメントを初期化
				$comment = null;
			}
			//コメントを取得
			else if (strpos($text, ';') === 0) {
				$comment .= preg_replace('/^;/', '', $text);
			}
			//設定を取得
			else if (preg_match("/^(.*?)\s*=\s*['\"]*(.*)['\"]*$/",$text, $m)) {
				//オブジェクト化
				$list->data->{$section}->{$m[1]} = new \stdClass;
				$list->data->{$section}->{$m[1]}->name = $m[1];
				$list->data->{$section}->{$m[1]}->value = $m[2];
				$list->data->{$section}->{$m[1]}->comment = $comment;
				//コメントを初期化
				$comment = null;
			} else {
				//何もない行
				//コメントを初期化
				$comment = null;
			}

			$line_count++;
		} while (isset($data[$line_count]));

		return $list;
	}

	/**
	 * iniファイルをHTMLへ整形する
	 *
	 * @param object $class HTML化をする解析データ
	 * @param object $any   追加解析データ
	 * @return string 成功時は設定ファイルに基づいて整形したHTMLを、失敗した場合はfalseを返します。
	 * @throws \Error
	 */
	private function _moldINI ($config_data, $any = null)
	{
		//設定リストの作成
		$list = null;
		$list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, "{$config_data->file->dir} ファイル");

		//タイトル
		if (isset($config_data->file->title)) {
			$list .= sprintf("<p>%s</p>", $config_data->file->title);
		}

		foreach ($config_data->file->data as $name => $section) {

			$table = null;
			foreach ($section as $param) {
				if (!is_object($param)) continue;
				$table .= sprintf ('<tr><th>%s</th><td>%s</td><td>%s</td></tr>',$param->name, h($param->value), $param->comment);
			}
			//パラメータが存在した場合
			if ($table) {
				$list .= sprintf(self::$_config->HTML->SMALL_TITLE, "{$name} セクション");
				$list .= sprintf('<p>%s</p>', $section->comment);
				$list .= sprintf('<table><thead><th>パラメータ名</th><th>設定値</th><th>コメント</th></thead>%s</table>', $table);
			}
		}
		$list = sprintf(self::$_config->HTML->ELEMENT, '', $list);
		return $list;
	}
}