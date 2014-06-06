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
 * ファイル出力・変換クラス
 *
 * 配列、オブジェクトをini,csv,YAML,json,rss2,atom,PHP,HTTPクエリ,serialize,xml形式へ変換します．
 *
 * ※RSS2を及びatomについては、必要に応じて他のライブラリに差し替えてください。
 *
 * **ブラウザへの出力例**
 * {{{php|
 * try {
 *     $Output = \Feke::load('Output', 'util');
 *
 *      //保存するデータ
 *      //実際にはクエリやロードクラスから取得した値を使用することが多いと思います。
 *      $input_data = array('ice' => ['name'=>'hage', 'type' => 'strovry']);
 *
 *      //出力する形式
 *      $type = 'json';
 *
 *      //出力を実行
 *      $Output->output ($input_data, $type);
 *
 * } catch (\Error $e) {
 *     //失敗した場合
 *     //エラー処理を適当に
 * }
 * //成功時
 * }}}
 *
 * >>XMLへ変換する場合は、pear XML_Serializerをインストールする必要があります。
 * >>pear install XML_Serializer
 *
 * @package    Feke
 * @subpackage util
 * @config /util/output
 */
class Output extends filer\Filer
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBaseStatic;

	/**
	 * 設定値
	 */
	protected static $_config;

	/**
	 * ファイルのパス
	 * @var unknown
	 */
	protected static $_file_path;

	/**
	 * 変換するデータ
	 */
	protected static $_original;


	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public function __construct ()
	{
		self::$_config = \Feke::loadConfig('/util/output',true);
	}

	/**
	 * 配列,オブジェクトを指定形式にして保存します。
	 *
	 * ini,yml,jsonに対応
	 *
	 * @param array|object $input_data  保存したいデータ
	 * @param string       $save_path   保存先のパス
	 * @return 成功時はtrueを返します。
	 * @throws 失敗時は、例外「Error」を投げます
	 * @example //保存するデータ
	 *          $input_data = array('ice' => ['name'=>'hage', 'type' => 'strovry']);
	 *          //保存先
	 *          $save_path = storage_path().'/text/item.ini';
	 *          $Output->save ($input_data, $save_path);
	 */
	public function save ($input_data, $save_path, $type, $option = array())
	{
		self::$_data = null;

		self::$_original = to_array($input_data);

		$type = strtolower($type);
		$method_name = "_to{$type}";
		if (method_exists($this, $method_name)) {
			$head_type = self::$method_name($option);
		} else {
			return $this->throwError ("指定された形式「{$type}」には対応していません。");
		}

		//取得した内容を表示
		echo self::$_data;

		return true;
	}

	/**
	 * 配列,オブジェクトを指定形式にしてブラウザへ出力します。
	 *
	 * rss,yml,jsonに対応
	 *
	 * @param array|object $input 出力したいデータ
	 * @param string       $type  出力したい形式
	 * @return 成功時はtrueを返します。
	 * @throws 失敗時は、例外「Error」を投げます
	 * @example //保存するデータ
	 *          $input_data = array('ice' => ['name'=>'hage', 'type' => 'strovry']);
	 *          //保存先
	 *          $type = 'json';
	 *          $Output->output ($input_data, $type);
	 */
	public function output ($input, $type, $option = array(), $mine = null)
	{
		self::$_data = null;

		self::$_original = to_array($input);
		$type = strtolower($type);
		$method_name = "_to{$type}";
		if (method_exists($this, $method_name)) {
			$head_type = self::$method_name($option);
		} else {
			return $this->throwError ("指定された出力形式「{$type}」には対応していません。");
		}

		//ヘッダを表示
		\feke\util\Header::{$head_type}($mine);

		//取得した内容を表示
		echo self::$_data;

		return true;
	}

	/**
	 * 配列,オブジェクトを指定形式に変換して取得します。
	 *
	 * ini,yml,jsonに対応
	 *
	 * @param array|object $input 変換データ
	 * @param string       $type  変換したい形式
	 * @return 成功時はtrueを返します。
	 * @throws 失敗時は、例外「Error」を投げます
	 * @example //保存するデータ
	 *          $input_data = array('ice' => ['name'=>'hage', 'type' => 'strovry']);
	 *          //保存先
	 *          $type = 'json';
	 *          $Output->mold ($input_data, $type);
	 */
	public function mold ($input, $type, $option = array())
	{
		self::$_data = null;

		self::$_original = to_array($input);
		$type = strtolower($type);
		$method_name = "_to{$type}";
		if (method_exists($this, $method_name)) {
			self::$method_name($option);
		} else {
			return $this->throwError ("指定された変換形式「{$type}」には対応していません。");
		}
		return self::$_data;
	}


	/**
	 * ini形式へ変換
	 *
	 * @return string
	 */
	private static function _toINI ()
	{
		foreach (self::$_original as $key => $value) {
			if (is_array($value)) {
				if (isset($key) and is_scalar($key)) {
					self::$_data .= "[{$key}]\n";
				}
				foreach ($value as $key2 => $value2) {
					if (isset($value2) and is_array($value2)) return self::thow_error('2階層以上の配列は変換できません');
					self::$_data .= "{$key2} = {$value2}\n";
				}
			} else {
				self::$_data .= "{$key} = {$value}\n";
			}
		}

		return 'ini';
	}

	/**
	 * rss1形式へ変換
	 *
	 * @return string
	 */
	/*
	private static function _to_rss1 ()
	{
	}*/

	/**
	 * rss2形式へ変換
	 *
	 * @return string
	 */
	private static function _toRss2 ()
	{
		$RSS = \Feke::load ('\feke\util\output\RSS2', '', self::$_config->RSS2);
		$RSS->errorMode('exce');

		$channel = array();
		$item = array();

		foreach (self::$_original as $key => $value) {
			if (is_array($value)) $item[] = $value;
			else $channel[$key] = $value;
		}

		$RSS->set_channel ($channel);
		$RSS->set_item ($item);

		self::$_data =  $RSS->output();

		return 'rss';

	}

	/**
	 * atom形式へ変換
	 *
	 * @return string
	 */
	private static function _toAtom ()
	{
		$RSS = \Feke::load ('\feke\util\output\Atom', '', self::$_config->ATOM);
		$RSS->errorMode('exce');

		$channel = array();
		$entry = array();

		foreach (self::$_original as $key => $value) {
			if (is_array($value)) $entry[] = $value;
			else $channel[$key] = $value;
		}

		$RSS->set_channel ($channel);
		$RSS->set_item ($entry);

		self::$_data =  $RSS->output();

		return 'rss';

	}

	/**
	 * CSV形式へ変換
	 *
	 * @param array $option ['引用符','引用符用のエスケープ','セルの区切り','改行コード']
	 *
	 * @return string
	 */
	private static function _toCsv ($option)
	{
		if (isset($option[0])) $innyou = $option[0];
		else $innyou = '"';

		if (isset($option[1])) $escepe = $option[1];
		else $escepe = '"';

		if (isset($option[2])) $sprit = $option[2];
		else $sprit = ',';

		if (isset($option[3])) $last = $option[3];
		else $last = "\n";

		if (is_array(self::$_original)) {
			foreach (self::$_original as $array) {
				$row_count = count($array);
				break;
			}

			foreach (self::$_original as $array) {
				$count = 0;
				foreach ($array as $value) {
					$count++;
					$value = str_replace($innyou, $escepe.$innyou, $value);
					if ($value !== "") {
						self::$_data .=  $innyou.$value.$innyou;
					}
					if ($count != $row_count) self::$_data .= $sprit;
				}
				self::$_data .= "$last";
			}
		}
		return 'text';
	}

	/**
	 * MS Excel用CSV形式へ変換
	 *
	 * @return string
	 */
	private static function _toExcelCsv ()
	{
		if (is_array(self::$_original)) {
			foreach (self::$_original as $array) {
				$row_count = count($array);
				break;
			}

			foreach (self::$_original as $array) {
				$count = 0;
				foreach ($array as $value) {
					$count++;
					$value = str_replace('"', '""', $value);
					if ($value !== "") {
						self::$_data .=  "\"".$value."\"";
					}
					if ($count != $row_count) self::$_data .= ",";
				}
				self::$_data .= "\n";
			}
		}
		self::$_data = mb_convert_encoding(self::$_data,"SJIS", "UTF-8");
		return 'text';
	}

	/**
	 * YAML形式へ変換
	 *
	 * @return string
	 */
	private static function _toYaml ()
	{
		\Feke::loadLibrary ('Spyc','/spyc/spyc.php');
		self::$_data = \Spyc::YAMLDump (self::$_original);

		return 'yaml';
	}

	/**
	 * JSON形式へ変換
	 *
	 * @return string
	 */
	private static function _toJson ()
	{
		self::$_data = json_encode(self::$_original);

		return 'json';
	}

	/**
	 * phpのserialize形式へ変換
	 *
	 * @return string
	 */
	private static function _toSerialize ()
	{
		self::$_data = serialize(self::$_original);

		return 'text';
	}

	/**
	 * php配列形式へ変換
	 *
	 * @return string
	 */
	private static function _toPhp ()
	{
		$roop = function($key, $value) use (&$roop) {
			self::$_data .= "array(";
			if (is_array($value)) {
				foreach ($value as $key2 => $value2) {
					if (is_array($value2)) $roop ($key2, $value2);
					else {
						$value2 = str_replace("'",'\'',$value2);
						$key2 = str_replace("'",'\'',$key2);
						self::$_data .= "'{$key2}' => '$value2',";
					}
				}
			} else {
				$key = str_replace("'",'\'',$key);
				$value = str_replace("'",'\'',$value);
				self::$_data .= "'{$key}' => '$value',";
			}
			self::$_data .= "),\n";
		};
		if (is_array(self::$_original)) {
			foreach (self::$_original as $key => $value) {

				self::$_data .= "\${$key} = array(\n";
				if (is_array($value)) {
					$roop ($key, $value);
				} else {
					$value = str_replace("'",'\'',$value);
					self::$_data .= "'{$key}' => '{$value}',\n";
				}
				self::$_data .= ");\n";
			}
		} else {
			$key = str_replace("'",'\'',self::$_original);
			self::$_data .= "\${$key} = null;";
		}

		return 'text';
	}

	/**
	 * HTTPクエリ形式へ変換
	 *
	 * @return string
	 */
	private static function _toHttp ()
	{
		self::$_data = http_build_query(self::$_original, '', '&');

		return 'text';
	}

	/**
	 * xml形式へ変換
	 *
	 * @link http://pear.php.net/manual/ja/package.xml.xml-serializer.xml-serializer.examples.php
	 * @return string
	 */
	private static function _toXml ()
	{
		require_once("XML/Serializer.php");
		$options = array(
				XML_SERIALIZER_OPTION_INDENT        => '    ',
				XML_SERIALIZER_OPTION_RETURN_RESULT => true
		);
		$Serializer = new \XML_Serializer($options);
		self::$_data = $Serializer->serialize(self::$_original);

		return 'xml';
	}
}