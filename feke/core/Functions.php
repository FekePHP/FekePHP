<?php
/**
 * グローバル関数用PHPファイル
 *
 * FekePHP内で使用するグローバル関数をまとめています。
 *
 * @package    feke
 * @version    0.4
 * @author     Shohei Miyazawa
 * @license    GNU General Public License (GPL)
 * @copyright  Copyright (c) FekePHP (http://fekephp.com/)
 * @link       http://fekephp.com/
 */

/**
 * htmlspecialchars()のエイリアスです。
 *
 * @param string $s エスケープしたい文字列
 * @return string エスケープされた文字列
 */
function h($s)
{
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * var_dump()の拡張関数です。
 *
 * 前後に''pre''タグを付けます。
 *
 * @param mixid $any デバックしたいデータ
 */
function dump ($any)
{
	echo '<pre>';
	var_dump ($any);
	echo '</pre>';
}

/**
 * アプリケーションパスを取得します。
 *
 * @return string FEKE_ROOT_PATH.FEKE_APP_PATH
 */
function app_path ()
{
	return FEKE_ROOT_PATH.'/'.FEKE_APP_NAME;
}

/**
 * FekePHPのルートパスを取得します。
 *
 * @return string FEKE_ROOT_PATH
 */
function root_path ()
{
	return FEKE_ROOT_PATH;
}

/**
 * データフォルダのパスを取得します。
 *
 * @return string FEKE_ROOT_PATH.FEKE_APP_PATH.'/storage'
 */
function storage_path ()
{
	return FEKE_ROOT_PATH.'/'.FEKE_APP_NAME.'/storage';
}

/**
 * 一時フォルダのパスを取得します。
 *
 * @return string FEKE_ROOT_PATH.FEKE_APP_PATH.'/tmp'
 */
function tmp_path ()
{
	return FEKE_ROOT_PATH.'/'.FEKE_APP_NAME.'/tmp';
}


/**
 * DebugBarへ表示したい内容をセットします。
 *
 * @param mixed $data
 * @retrun none
 */
function debug_msg ($data)
{
	\feke\core\Debug::setMessage(['debug' => debug_backtrace()[1], 'line' => debug_backtrace()[0]['line'], 'data' => $data]);
}

/**
 * デバッグレベルの取得をします。
 *
 * @return numeric デバッグレベル
 */
function debug_level()
{
	return \feke\core\Debug::getDebugLevel();
}

/**
 * テキストの整形をするグローバル関数です。
 *
 * テキストのはじめと、複数回改行が重なった場合は、<p>タグに置き換えます。
 *
 * @param string $text 変換したいテキスト
 * @return string 加工された文字列
 */
function mold ($text)
{
	$order   = array("\r\n", "\n", "\r");
	$text = "<p>{$text}</p>";
	$text = str_replace ($order, '<br>', $text);
	$text = str_replace ('<p><br>', '<p>', $text);
	$text = preg_replace ('/<br><br><br><br>/', '<br><br><br>', $text);
	$text = preg_replace ('/<br><br><br>/', '<br><br>', $text);
	$text = preg_replace ('/<br><br>/', '</p><p>', $text);
	return $text;
}

/**
 * wiki文法に近い整形をするグローバル関数です。
 *
 * @param string $text 加工したい文字列
 * @param boolen $php trueの場合はphpコードを許可し、falseの場合は強制的に排除します。
 * @return string 加工された文字列
 */
function mold_wiki ($text, $php = false)
{
	return \feke\util\WikiParser::run($text, $php);
}

/**
 * Markdownを使用して整形するグローバル関数です。
 *
 * @param string $text 加工したい文字列
 * @return string 加工された文字列
 */
function mold_markdown ($text)
{
	return \feke\util\Markdown::run($text);
}
/**
 * 1つ限りのバリデーションを実行するグローバル関数です。
 *
 * @param string $data 検証する内容
 * @param string $rule ルールの指定
 * @return boolen 返り値はルールに対するエラーがなければtrue，エラーがあった場合は，エラーメッセージです。
 */
function check ($check_string, $rule)
{
	return \feke\util\Validation::check ($check_string, $rule);
}

/**
 * クラスのメソッドが存在し、呼び出せる場合のみtrueを返します。
 *
 * @param string|instance $class_name クラス名
 * @param string $method_name メソッド名
 * @return boolen 呼び出せる場合のみtrueを返し、それ以外はfalseを返します。
 */
function is_method ($Instance, $method_name) {
	if (method_exists($Instance, $method_name)) {
		if (is_callable(array($Instance, $method_name))) {
			return true;
		}
	}
	return false;
}

/**
 * 値が存在する場合のみtrueを返します。
 *
 * 配列、オブジェクト、null、空文字だった場合は、falseをかえします
 *
 * @param string mixed 検証したい変数
 * @return boolen 値が存在する場合のみtrue、配列・オブジェクト・null・空文字だった場合は、falseをかえします
 */
function is_value ($text) {
	if (is_array($text) or is_object($text)) return false;
	if ('' !== strval($text)) return true;
	return false;
}

/**
 * 配列をオブジェクトに変換します。
 *
 * @param array $array 変換したい配列
 *
 * @return object
 */
function to_object ($array)
{
	if (!is_array($array)) return $array;

	$object = (object)$array;

	foreach ($object as &$value) {
		$value = to_object ($value);
	}
	return $object;
}


/**
 * オブジェクトを配列に変換に変換します。
 *
 * @param array $array 変換したいオブジェクト
 *
 * @return array
 */
function to_array ($target)
{
	if (is_object($target)) {
		$target = (array)$target;
	}
	if (is_array($target)) {
		foreach ($target as $key => $value) {
			$target[$key] = to_array ($value);
		}
	}
	return $target;
	/*
	if (!is_object($array)) return $array;

	$object = (array)$array;

	foreach ($object as &$value) {
		$value = to_array ($value);
	}
	return $object;*/
}

/**
 * オブジェクトのプロパティを結合します
 */
function objcect_merge ($ojb1, $obj2)
{
	foreach ($obj2 as $key => $value) {
		$ojb1->{$key} = $value;
	}
	return $ojb1;
}

/**
 * 多次元配列を1次元へ変換します。
 *
 * @param array $array
 * @return array
 */
function array_flat (array $array)
{
	$result = array();

	foreach($array as $value){
		if(is_array($value)){
			$result = array_merge($result, array_flat($value));
		}else{
			$result[] = $value;
		}
	}
	return $result;
}

/**
 * 多次元配列同士をキーの書き換えなくマージします。
 *
 * array_merge_recursiveとは、キーの更新しないと事以外、だいたい同じです。
 *
 * @param array $array1
 * @param array $array2
 * @retrun array
 */
function array_plus (array $array1 ,array $array2)
{
	foreach ($array2 as $field => $value) {
		if (isset($array1[$field])) {
			if (is_array($array1[$field])) {
				$array1[$field] = array_plus ($array1[$field] ,$value);
			} else {
				if (is_array($value)) {
					$array1[$field] = [$array1[$field]] + $value;
				} else {
					$array1[$field] = $value;
				}
			}
		} else {
			$array1[$field] = $value;
		}
	}
	return $array1;
}

/**
 * ディレクトリ内を空にします。
 *
 *
 * >>第一引数を間違えたら試合終了です！！
 *
 * @param string $dir $カラにしたいディレクトリ名
 */
function clear_directory ($dir) {
	if (($dir = realpath($dir)) == false) {
		return false;
	}
	if ($handle = opendir($dir)) {
		while (false !== ($item = readdir($handle))) {
			if ($item != "." && $item != "..") {
				if (is_dir("$dir/$item")) {
					remove_directory("$dir/$item");
				} else {
					unlink("$dir/$item");
				}
			}
		}
		closedir($handle);

		return true;
	}
}

/**
 * ディレクトリ内を削除します。
 *
 * >>第一引数を間違えたら試合終了です！！
 *
 * @param string $dir $カラにしたいディレクトリ名
 */
function remove_directory ($dir) {
	if (($dir = realpath($dir)) == false) {
		return false;
	}
	if ($handle = opendir($dir)) {
		while (false !== ($item = readdir($handle))) {
			if ($item != "." && $item != "..") {
				if (is_dir("$dir/$item")) {
					clear_directory("$dir/$item");
				} else {
					unlink("$dir/$item");
				}
			}
		}
		closedir($handle);
		rmdir($dir);

		return true;
	}
}

/**
 * 与えられた値が、trueか判定します。
 *
 * @param mixied $string 判定したい変数
 * @return boolen true,'ture',1,'1','on','yes','y'のいずれか（大文字、小文字問わず）の場合はtrue、その他の場合はfalseを返します。
 */
function is_true ($string)
{
	//スカラー型
	if (is_scalar($string)) {
		$target = ['true', 1, '1', 'on','yes', 'y'];
		if (is_string($string)) {
			
			if (in_array(strtolower($string) ,$target)) {
				return true;
			}
		}
		elseif ($string === true) {
			return true;
		}
	}
	return false;
}

/**
 * Ajaxによるリクエストかどうか確認します。
 *
 * @return boolean Ajax通信だった場合はtrue,それ以外はfalseを返します。
 */
function is_ajax()
{
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
		return true;
	}
	return false;
}
