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
 * FekePHP用のドキュメンターです。
 * このページもこのクラスで解析して出力しています。
 *
 * phpファイルをドキュメント化します。
 * だいたいphpDocumentorの様な感じで書くことができると思います。
 *
 * ドキュメントに記載には、Feke用のwiki文法を使用可能です。
 *
 * 継承クラスやトレイトには専用のリンクが用意されます。
 *
 * **使用できるタグ**
 * |~タグ名|詳細|使用例|
 * |>|>|!メソッド・関数|
 * |param  |メソッドや、関数のパラメータ||
 * |return |返り値||
 * |throws |例外||
 * |example|サンプルコード||
 * |>|>|!クラス・phpファイル|
 * |author|作者||
 * |copyright|コピーライト||
 * |deprecated|互換性の維持||
 * |link|参考リンク||
 * |see|関連ドキュメント||
 * |since|導入されたバージョンなど||
 * |version|バージョン||
 * |license|ライセンス||
 * |config|class内で使用している設定ファイル||
 * |>|>|!その他|
 * |class|cssのクラス||
 * |load|ドキュメントの再帰的取得||
 *
 *
 * **注意**
 * -FekePHP内で使用することを想定しているため、オートローダーが読み込めないクラスに関しては正確に動作しません。
 * -php4の記載方法は非互換です。
 * -一つのphpファイルにつき、classは一つまでしか解析できません。
 *
 *
 * @package    feke
 * @subpackage util
 * @config /util/documentor
 *
 *
 */

class Documentor
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * php解析用トレイト
	 * @load true
	 */
	use documentor\AnalyzePHP;

	/**
	 * ini解析用トレイト
	 * @load true
	 */
	use documentor\AnalyzeINI;

	/**
	 * HTML成型用トレイト
	 * @load true
	 */
	use documentor\MoldHtml;

	/**
	 * コメントタグ成型用
	 * @load true
	 */
	use documentor\MoldTag;

	/**
	 * コメントの正規表現
	 * @var string
	 */
	const MATCH_COMMENT = '\/\*(.*?)\*\/';


	/**
	 * 解析結果を保存
	 * @var string
	 */
	private $_result;

	/**
	 * ファイルリスト
	 * @var array
	 */
	private $_filelist = array();

	/**
	 * クラスリスト
	 * @var array
	 */
	private $_classlist = array();

	/**
	 * コード全体
	 * @var string
	 */
	private $_code;

	/**
	 * PHPのコード
	 * @var string
	 */
	private $_php_code;

	/**
	 * PHPのコード
	 * @var string
	 */
	private $_class_code;

	/**
	 * PHPのコメント
	 * @var string
	 */
	private $_php_comment;

	/**
	 * 名前空間
	 * @var string
	 */
	private $_namespace;

	/**
	 * 関数の情報
	 * @var string
	 */
	private $_function;

	/**
	 * クラスの情報
	 * @var string
	 */
	private $_class;

	/**
	 * クラス定数
	 * @var array
	 */
	private $_const = array();

	/**
	 * クラスのプロパティ
	 * @var array
	 */
	private $_property = array();

	/**
	 * クラスのプロパティ
	 * @var array
	 */
	private $_method = array();

	/**
	 * クラス内のトレイト
	 * @var array
	 */
	private $_trait = array();

	/**
	 * phpファイルの情報
	 * @var array
	 */
	private $_file = array();

	/**
	 * 継承クラスの読み込み
	 * @var boolen
	 */
	private $_over_load;

	/**
	 * 設定のオブジェクト
	 * @var object
	 */
	private static $_config;

	/**
	 * 基準ディレクトリ
	 * @var string
	 */
	private $_rootDir = FEKE_ROOT_PATH;

	/**
	 * 基準URL
	 * @var string
	 */
	private $_root_url = './';


	/**
	 * パンくずリスト用のパスを保存
	 * @varstring
	 */
	private $_pan_path;

	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public function __construct()
	{
		//設定ファおる読み込み
		self::$_config = \Feke::loadConfig ('/util/documentor', true);
	}

	/**
	 * 指定されたファイル、またはディレクトリの解析を行います。
	 *
	 * ディレクトリが指定された場合は、再帰的に下層ディレクトリを読み込み、読み込み可能なphpファイルを自動で解析します。
	 *
	 * @param string $dir       解析する階層
	 * @param string $extend_fg trait または extend
	 * @param boolen $over_load trueの時は、継承・トレイトクラスまで解析する。
	 * @return object           解析結果をファイル指定の場合はオブジェクト形式、ディレクトリの場合は、オブジェクトを配列に格納して値を返します。
	 * @example // /FEKE_ROOT_PATH/feke フォルダ内のファイルを解析
	 *          $Documentor->mold(root_path().'/feke/'));
	 *
	 *          // /FEKE_ROOT_PATH/feke/core/ClassLoaderファイルを解析
	 *          $Documentor->mold(root_path().'/feke/core/ClassLoader.php'));
	 */
	public function run ($dir, $extend_fg = null ,$over_load = null)
	{
		if (!is_string($dir)) {
			return $this->throwError("[string:dir:$dir]");
		}

		$this->_result = array();

		$this->_over_load = $over_load;

		//ファイルが指定された場合
		if (preg_match('/.[a-z0-9]+$/',$dir)) {
			if ($over_load === null) $this->_over_load = true;
			$dir = realpath(self::compPath($dir));

			$obj             = new \stdClass();

			//phpファイルの解析
			if (preg_match ("/\.php$/",$dir)) {
				$this->analyzePHP ($dir, $extend_fg);
				$obj->function   = $this->_function;
				$obj->file       = $this->_file;
				$obj->class      = $this->_class;
				$obj->namespace  = $this->_namespace;
				$obj->const      = $this->_const;
				$obj->property   = $this->_property;
				$obj->method     = $this->_method;
				$obj->trait      = $this->_trait;
				$obj->extend_fg  = $extend_fg;
			}
			//iniファイルの解析
			elseif (preg_match ("/\.ini$/",$dir)) {
				$obj->file = $this->analyzeINI ($dir, $extend_fg);
				$this->_file = $obj->file;
			}

			$this->_result     = new \stdClass();
			$this->_result = $obj;

			return $this->_result;
		}

		//ディレクトリが指定された場合
		$this->_filelist = $this->getFileList ($dir);

		foreach ($this->_filelist as $file) {
			if (false === $this->analyzePHP ($file, $extend_fg)) {
				continue;
			} else {
				$obj = new \stdClass();
				$obj->function   = $this->_function;
				$obj->file       = $this->_file;
				$obj->class      = $this->_class;
				$obj->namespace  = $this->_namespace;
				$obj->const      = $this->_const;
				$obj->property   = $this->_property;
				$obj->method     = $this->_method;
				$obj->trait      = $this->_trait;
				$obj->extend_fg  = $extend_fg;

				$this->_result[]   = $obj;
			}
		}
		return $this->_result;
	}

	/**
	 * 基準操作ディレクトリの設定をします。
	 *
	 * デフォルトでは、''FEKE_ROOT_PATH''が基準となっています。
	 *
	 * @param string $dir_name 各引数の前に加えたいディレクトリ名
	 */
	public function setRoot ($dir_name)
	{
		if (!is_string($dir_name)) {
			return $this->throwError("[string:dir_name:$dir_name]");
		}
		$this->_rootDir = $dir_name;
	}

	/**
	 * 指定されたファイル内を再帰的にリストアップします。
	 *
	 * @param string $dir 解析する階層
	 * @return Ambigous <multitype:string , multitype:>
	 */
	public function getFileList ($dir)
	{
		if (!is_string($dir)) {
			return $this->throwError("[string:dir:$dir]");
		}

		//パスの正規化
		$dir = realpath($dir);
		$filelist = array();
		$real_path = realpath($dir);
		if (!is_readable($real_path)) return $filelist;
		foreach (scandir (realpath($real_path)) as $filename) {
			$new_name = realpath($dir."/".$filename);
			if($filename == '.' || $filename == '..'){
				continue;
			} elseif (is_file($new_name)){
				$filelist[] = $new_name;
			} elseif( is_dir($new_name) ) {
				$filelist = array_merge($filelist, $this->getFileList ($new_name. '/'));
			}
		}
		return $filelist;
	}

	/**
	 */
	public function getDirList ($dir)
	{
		if (!is_string($dir)) {
			return $this->throwError("[string:dir:$dir]");
		}

		//パスの正規化
		$dir = realpath($dir);
		$list = $this->getFileList ($dir);

		$list = str_replace($dir, '', $list);
		$list = str_replace("\\", '/', $list);
		$list = preg_replace('/^\//', '', $list);
		$dir_list = new \stdClass();
		foreach ($list as $value) {
			if (strpos($value, '/') !== false) {
				$dir_list->dir[] = $value;
			} else {
				$dir_list->file[] = $value;
			}
		}
		return $dir_list;
	}


	/**
	 * 対象のファイル名を取得します。
	 */
	public function getFilename ()
	{
		$Key = \Feke::load('Input', 'util');
		$file_name = preg_replace("/_(.*?)$/",'.$1', $Key->path);
		$file_name = str_replace('-','/',$file_name);
		return $file_name;
	}

	/**
	 * 指定ファイルの詳細を生成します。
	 *
	 * @param string $file_name 対象ファイルのパス
	 */
	public function mold ($file_name = null)
	{
		if (!is_string($file_name) and $file_name !== null) {
			return $this->throwError("[string:file_name:$file_name]");
		}

		if (!$file_name) {
			//ファイル名の取得
			$Key = \Feke::load('Input', 'util');
			$file_name = preg_replace("/_(.*?)$/",'.$1', $Key->path);
			$file_name = str_replace('-','/',$file_name);
			$file_name = "{$this->_rootDir}/{$file_name}";
		}

		//ファイルの有無
		$dir_flag = false;
		if (!is_file($file_name)) {
			if (!is_dir($file_name)) {
				$this->throwError("ファイル「{$file_name}」は存在しません。");
			} else {
				$dir_flag = true;
			}
		}

		//phpファイルの解析
		if (preg_match ("/\.php$/",$file_name)) {
			$class = $this->run($file_name);

			//クラスのタグ
			$class_into = $this->_moldTopHtml ($class);

			//メソッドリスト
			$method_list = $this->_moldMethodList($class->method);

			//グローバル関数リスト
			$function_list = $this->_moldFunctionList($class->function);

			//プロパテリスト
			$property_list = '<div class="box content">';
			foreach ($class->property as $value) {
				$tag = null;
				if (isset($value->tag[0][1])) $tag = $value->tag[0][1];
				$property_list .= "<h3>{$value->name} [{$tag}]</h3>";
				$property_list .= mold($value->comment);
			}
			$property_list .= '</div>';

			return $class_into.$function_list.$method_list.$property_list;
		}
		//iniファイルの解析
		else if (preg_match ("/\.ini$/",$file_name)) {
			$file = $this->run($file_name);

			return $this->_moldINI($file);
		}
		//ディレクトリの解析
		else if ($dir_flag === true) {
			$files = $this->getDirList ($file_name);

			//パンくず用のパス
			$this->_pan_path = $this->_delRootdir($file_name);

			//クラスのトップ紹介
			$class_list = null;

			//ファイルリスト
			$file_list = null;

			//ディレクトリリスト
			$dir_list = null;

			//クラス・phpファイルリスト
			$class_list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, 'Class List');
			$file_list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, 'File List');
			if (isset($files->file)) {
				foreach ($files->file as $file) {
					//phpのみ
					if (preg_match("/\.php$/",$file)) $php_flag = true;
					else $php_flag = false;

					$que = $this->run ("{$file_name}/{$file}", '', false);

					//リンク名の取得
					if (isset($que->class->name)) {
						if (is_string($que->class->name)) $name = $que->class->name;
					} elseif (isset($que->file->name)) {
						if (is_string($que->file->name)) $name = $que->file->name;
					} else {
						$name = $file;
					}

					$into = null;
					$link = null;
					if (isset($que->class->dir)) {
						$link = str_replace(['\\','/'], '-', $que->class->dir);
						$link = str_replace('.','_', $link);
						$link = preg_replace('/^-/','', $link);

						if (isset($que->class->title)) $into = $que->class->title;

					} elseif (isset($que->file->dir)) {
						$link = str_replace(['\\'], '-', $que->file->dir);
						$link = str_replace('.','_', $link);
						$link = preg_replace('/^-/','', $link);

						if (isset($que->file->title)) $into = $que->file->title;
					}

					if ($into) $into = "<p>{$into}</p>";

					if ($php_flag) {
						$class_list.= "<li><a href=\"../dital/path_{$link}\">{$name}</a>{$into}</li>";
					} else {
						$file_list.= "<li><a href=\"../dital/path_{$link}\">{$file}</a>{$into}</li>";
					}
				}
			}
			$file_list = sprintf(self::$_config->HTML->ELEMENT, '', $file_list);
			$class_list = sprintf(self::$_config->HTML->ELEMENT, '', $class_list);


			//ディレクトリリスト
			$dir_list .= sprintf(self::$_config->HTML->MIDDLE_TITLE, 'Directory List');

			foreach (scandir ($file_name) as $que) {
				if (!preg_match("/\.[A-Za-z0-9]+$/",$que)) {
					if($que == '.' || $que == '..'){
						continue;
					}
					$new_name = str_replace($this->_rootDir, '', $file_name.'/'.$que);
					$link = str_replace(['\\','/'], '-', $new_name);
					$link = preg_replace('/^-/','', $link);

					$dir_list .= "<li><a href=\"./path_{$link}\">{$new_name}</a></li>";
				}
			}
			$dir_list = sprintf(self::$_config->HTML->ELEMENT, '', $dir_list);

			//配列にして返す
			return ['php_list' => $class_list, 'dir_list' => $dir_list, 'file_list' => $file_list];
		}
	}

	/**
	 * 解析したファイルのサイドバー作成
	 *
	 * @param object $class クラスの解析データ
	 */
	public function moldList ()
	{
		$part = null;
		$list = null;

		if (isset($this->_function)) {
			//関数リスト
			foreach ($this->_function as $value) {

				$obj = $this->_moldTag($value->tag);

				//グローバル関数用
				$function = $this->_moldFunction('function', 's');

				$class = null;
				//隠し要素
				if ($obj->style_class) {
					$class = $obj->style_class;
				}

				$part .= sprintf(self::$_config->HTML->SIDE_BAR_LIST, '', $function, '', '', "{$value->name} ()");
			}
			if ($part) $list .= sprintf(self::$_config->HTML->SIDE_BAR_ELEMENT,'グローバル関数', $part);
		}

		//methodリスト

		$part = null;
		$target = ['public', 'protected', 'private'];
		if (isset($this->_method)) {
			$last_type = null;
			foreach ($this->_method as $value) {
				if ($last_type != $value->type) {
					$part .= sprintf(self::$_config->HTML->SIDE_BAR_MIDASI, $value->type, $value->type);
					$last_type = $value->type;
				}


				$obj = $this->_moldTag($value->tag);

				//静的オプション用
				$static = $this->_moldStatic($value->static,'s');

				//クラス
				$class = null;
				if (isset($value->type)) $class = $value->type;

				//継承フラグ
				$extend_fg = null;
				if (isset($value->extend_fg)) $extend_fg = $value->extend_fg;
				$extend = $this->_moldExtend($extend_fg,'s');

				//隠し要素
				if ($obj->style_class) {
					$class = $obj->style_class;
				}

				$part .= sprintf(self::$_config->HTML->SIDE_BAR_LIST, $class, $extend.$static, '', '', "{$value->name} ()");
			}
			if ($part) $list .= sprintf(self::$_config->HTML->SIDE_BAR_ELEMENT,'メソッド', $part);
		}

		//propertyリスト
		$part = null;
		if (isset($this->_property)) {
			$last_type = null;
			foreach ($this->_property as $value) {
				if ($last_type != $value->type) {
					$part .= sprintf(self::$_config->HTML->SIDE_BAR_MIDASI, $value->type, $value->type);
					$last_type = $value->type;
				}

				$obj = $this->_moldTag($value->tag);

				//静的オプション用
				$static = $this->_moldStatic($value->static, 's');

				//クラス
				$class = null;
				if (isset($value->type)) $class = $value->type;

				//隠し要素
				if ($obj->style_class) {
					$class = $obj->style_class;
				}

				$part .= sprintf(self::$_config->HTML->SIDE_BAR_LIST, $class, $static, '', '', $value->name);
			}
			if ($part) $list .= sprintf(self::$_config->HTML->SIDE_BAR_ELEMENT,'プロパティ', $part);
		}

		return $list;
	}

	/**
	 * 解析したファイルのサイドバー作成
	 *
	 * @param object $class クラスの解析データ
	 */
	public function getPankuzu ($dir = null)
	{
		$list = null;
		if (!$dir) $dir = $this->_pan_path;
		if ($dir) {
			$dir = str_replace("\\",'/',$dir);
			$dir_list = explode ("/", $dir);

			foreach ($dir_list as $key =>$name) {
				if (!$name) continue;
				if (!isset($path)) {
					$path = "./path_{$name}";
				}
				else {
					$path .= sprintf("-%s", $name);
				}
				$type = 'directory';
				if ($key == count($dir_list) - 1 ) {
					if (preg_match('/\.[A-Za-z0-9]+$/',$dir)) {
						$type = 'dital';
					}
				}


				$list .= sprintf(" / <a href=\"../%s/%s\">%s</a>",$type, $path, $name);
			}
		}
		return sprintf("<p><b>Path</b> %s</p>", $list);
	}

	/**
	 * 一括で詳細を生成したファイル情報を取得します。
	 *
	 * @param string|array $option 取得したいデータの階層を指定
	 * @return object 解析結果
	 */
	public function moldFilename ($option = null)
	{
		$type = false;
		if (isset($this->_class->type)) $type = $this->_class->type;

		if ($type !== false) {
			if ($option) return $this->_class->{$option};
			return $this->_class;
		} else {
			if (isset($this->_file->{$option})) {
				return $this->_file->{$option};
			}
			return $this->_file;
		}

	}

	/**
	 * 一括で詳細を生成したファイル情報を取得します。
	 *
	 * @param string|array $option 取得したいデータの階層を指定
	 * @return object 解析結果
	 */
	public function getResult ($option = null)
	{
		if (is_array($option)) {
			$obj = $this->_result;
			foreach ($option as $value) {
				if (is_object($obj)) $obj = $obj->{$value};
				elseif (is_array($obj)) $obj = $obj[$value];
				else return;
			}
			return $obj;
		} elseif (is_string($option)) {
			return $this->_result->{$option};
		} else {
			return $this->_result;
		}
	}

	/**
	 * ルートパスを削除する
	 */
	private function _delRootdir ($path)
	{
		$match = realpath($this->_rootDir);
		$link = str_replace($match, '', realpath($path));
		return $link;
	}



}