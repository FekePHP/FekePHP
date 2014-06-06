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

trait AnalyzePHP
{
	/**
	 * phpファイルを解析します。
	 *
	 * @param string $file 解析したいphpファイル名
	 */
	public function analyzePHP ($file, $extend_fg)
	{
		$this->_method = array();
		$this->_property = array();

		$extend_property = array();
		$extend_method = array();

		//検索用クロージャー(プロパティ・メソッド・クラス定数)
		$search = function ($cnt, $match1, $match2, $kind, $type, $static, $code_name) use ($file,$extend_fg){
			//要素名用
			$kind_name = $kind;
			//プロパティ名用
			$kind = "_{$kind}";

			if (preg_match_all($match1, $this->{$code_name}, $m)) {
				foreach ($m[0] as $value) {
					if (is_array($value)) continue;
					$comment = $this->_getPhpComment($value);
					$name = preg_replace($match2, '', $value);
					if (!is_string($name)) $name = null;
					$this->{$kind}[$cnt]          = new \stdClass();
					$this->{$kind}[$cnt]->name    = $name;

					$this->{$kind}[$cnt]->type    = $type;
					$this->{$kind}[$cnt]->comment = $this->_deleteTag($comment);
					$this->{$kind}[$cnt]->tag     = $this->_getTag($comment, $kind);
					$this->{$kind}[$cnt]->static  = $static;
					$this->{$kind}[$cnt]->path    = $file;
					$this->{$kind}[$cnt]->kind    = $kind_name;

					//継承をしている内容かチェック
					if (isset($extend_fg)) $this->{$kind}[$cnt]->extend_fg = $extend_fg;
					else $this->{$kind}[$cnt]->extend_fg = null;

					//コメントを生成してタイトルを取得
					if (isset($this->{$kind}[$cnt]->comment)) {
						if (preg_match_all("/(.*)/u", (string)$this->{$kind}[$cnt]->comment ,$m)) {
							$this->{$kind}[$cnt]->title = $m[0][0];
						}
					}

					//メソッド・関数専用解析
					if ($kind_name === "method" or $kind_name === "function") {
						//関数名を整える
						$this->{$kind}[$cnt]->name = preg_replace('/\((.*)\)/','',$this->{$kind}[$cnt]->name);

						//引数の配列
						preg_match_all("/[$]([A-Za-z0-9_]+)?[\s,)]/", $name ,$ma);
						$this->{$kind}[$cnt]->argument = $ma[1];

						//中身の解析
						$this->{$kind}[$cnt]->argument_value = array();
						preg_match ('/\((.*)\)/',$name,$nakami);
						$arguments = explode (',', $nakami[1]);
						foreach ($arguments as $key=>$value) {
							if (!isset($this->{$kind}[$cnt]->argument[$key])) continue;

							$match = "/[$]{$this->{$kind}[$cnt]->argument[$key]}[ ]*=[ ]*(.*)[ ]*$/";
							preg_match ($match, $value, $ma);

							if (isset($ma[1])) {
								$new_value = $ma[1];
							} else {
								//初期値がなかったら
								continue;
							}
							//余分なスペースを取る
							$new_value = preg_replace("/^\s*|\s$/","",$new_value);

							//$new_value = preg_replace("/^'|'$/","",$new_value);
							//$new_value = preg_replace("/^\"|\"$/","",$new_value);
							$this->{$kind}[$cnt]->argument_value['$'.$this->{$kind}[$cnt]->argument[$key]] = $new_value;
						}
					}

					//ファイル情報取得専用
					if ($kind === '_file') {
						$this->{$kind}[$cnt]->name = basename($file);

						//ルートパスからのパス
						$match = realpath(root_path());
						$this->{$kind}[$cnt]->dir  = str_replace($match, '', realpath($file));

					}
					$cnt++;
				}
			}

			return $cnt;
		};


		$this->_class_code = null;

		//コードを取得
		$this->_code = file_get_contents($file);
		//コメントを除いたコード
		$this->_php_code = $this->_deletePhpComment ($this->_code);

		$this->_class = new \stdClass();

		//名前空間の取得
		$this->_namespace = null;
		if (preg_match('/namespace [a-z0-9\_\\\\]+;/is', $this->_php_code, $namespace)) {
			$this->_namespace = preg_replace('/namespace |;/', '', $namespace[0]);
		}

		//ファイル情報を入手する
		$kind = 'file';
		$this->_file = array();
		$cnt = 0;
		$type = null;
		$match1 = "/".self::MATCH_COMMENT."/is";
		$match2 = "/\*\/(.*)|\/\*+|[]+\*[]+/";

		$cnt = $search($cnt, $match1, $match2, $kind, $type, false, '_code');
		if(isset($this->_file[0])) $this->_file = $this->_file[0];


		//グローバル関数を取得
		$kind = 'function';
		$this->_function = array();
		$cnt = 0;
		$type = null;
		$match1 = "/".self::MATCH_COMMENT."[\s\n\t]*function[ ]+[a-z0-9_]+[ ]*\((.*?)\)/is";
		$match2 = "/(.*)function[ ]+/is";

		$cnt = $search($cnt, $match1, $match2, $kind, $type, false, '_code');


		//class・traitか確認
		$match_class = "/".self::MATCH_COMMENT."[\s\n]*class[ ]+[a-z0-9_]+/is";
		$match_trait = "/".self::MATCH_COMMENT."[\s\n]*trait[ ]+[a-z0-9_]+/is";
		$match_extends = "/extends[ ]+([A-Za-z0-9_\\\\]+)/is";
		$match_file_comment = "/".self::MATCH_COMMENT."/is";


		//classの解析
		if (preg_match($match_class, $this->_code, $m)) {
			$this->_class->type = 'class';
			$this->_class->kind = 'class';

			//クラス内のコード
			$match = "/class[ ]+([A-Za-z0-9_]+)[ ]*(.*)[{](.*[}])/is";
			preg_match($match, $this->_php_code, $code);

			//クラス名
			$this->_class->name = $code[1];

			//クラス内のコード
			$match = "/class\s+{$this->_class->name}\s*(.*?)\{(.*)\}/is";
			if (preg_match($match, $this->_code, $code)) {
				$this->_class_code = $code[2];
				$extend_code = $code[1];
			}


			//名前空間
			if (isset($this->_namespace) and isset($this->_class->name)) {
				$this->_class->namespace = $this->_namespace."\\".$this->_class->name;
			} else {
				$this->_class->namespace = "\\".$this->_class->name;
			}

			//継承クラスの取得
			if (preg_match($match_extends, $extend_code, $code)) {
				//継承クラスの名前
				$this->_class->extends = $code[1];
				//名前空間を結合
				if (!preg_match('/^\\\\/', $this->_class->extends)) {
					$this->_class->extends = $this->_namespace.'\\'.$this->_class->extends;
				}

				//メソッド等の継承内容保保持するかどうか
				$load_flag = false;
				if (isset($this->_class->tag)) {
					foreach ($this->_class->tag as $array) {
						if (in_array('load', $array)) $load_flag = true;;
					}
				}

				//読み込むかどうか
				if ($this->_over_load === true) {

					if (!isset($Doc)) $Doc = \Feke::load ('Documentor', 'util');
					//リスト
					$real_path = realpath($this->_rootDir.'/'.$this->_class->extends.'.php');
					if ($real_path) {
						$load_extend_class = $Doc->run($real_path,'extend');
						if ($load_flag === true) {
							$extend_method = array_merge($extend_method, $load_extend_class->method);
							$extend_property = array_merge($extend_property ,$load_extend_class->property);
						}
					}
				}
			} else {
				$this->_class->extends = null;
			}

			//クラスのコメント
			$comment = $this->_getPhpComment($m[0]);
			$this->_class->comment = $this->_deleteTag ($comment);

			//クラスの見出し
			if (isset($this->_class->comment)) {
				if (preg_match_all("/(.*)/u", (string)$this->_class->comment ,$m)) {
					$this->_class->title = $m[0][0];
				}
			}

			//クラスのタグ
			$this->_class->tag     = $this->_getTag ($comment, '_class');

			//クラスの場所
			$match = realpath(root_path());
			$this->_class->path = realpath($file);
			$this->_class->dir     = str_replace($match, '', realpath($file));

		} elseif (preg_match($match_trait, $this->_code, $m)) {
			$this->_class->type = 'trait';
			$this->_class->kind = 'trait';

			//トレイト内のコード
			$match = "/trait[ ]+[a-z0-9_]+(.*)[{](.*[}])/is";
			if (preg_match($match, $this->_code, $code)) $this->_class_code = $code[0];

			//トレイト名
			$this->_class->name = preg_replace("/(.*)trait[ ]+/is", '', $this->_deletePhpComment($m[0]));

			//名前空間
			$this->_class->namespace = $this->_namespace."\\".$this->_class->name;

			//トレイトのコメント
			$comment = $this->_getPhpComment($m[0]);
			$this->_class->comment = $this->_deleteTag($comment);

			//トレイトの見出し
			if (isset($this->_class->comment)) {
				if (preg_match_all("/(.*)/u", (string)$this->_class->comment ,$m)) {
					$this->_class->title = $m[0][0];
				}
			}

			//トレイトのタグ
			$this->_class->tag     = $this->_getTag($comment, '_class');

			//トレイトの場所
			$match = realpath(root_path());
			$this->_class->dir     = str_replace($match, '', realpath($file));

		} else {
			$this->_class->type = false;
			$this->_class->kind = 'file';
			return ;
		}

		//クラス定数の取得
		$kind = 'const';
		$this->_const = array();
		$cnt = 0;
		$type = null;
		$match1 = "/".self::MATCH_COMMENT."[\s\n]+const[ ]+[a-z0-9_]+/is";
		$match2 = "/(.*)const[ ]+/is";

		$cnt = $search($cnt, $match1, $match2, $kind, $type, false, '_class_code');


		//トレイトの取得
		$kind = 'trait';
		$this->_trait = array();
		$cnt = 0;
		$type = null;
		$match1 = "/".self::MATCH_COMMENT."[\s\n]+use[ ]+[a-z0-9_,\\\\]+;/is";
		$match2 = "/(.*)use[ ]+|;/is";

		$cnt = $search($cnt, $match1, $match2, $kind, $type, false, '_class_code');

		//トレイトのソースの場所を取得
		if (is_array($this->_trait)) {
			foreach ($this->_trait as $key => $trait) {
				if (!preg_match('/^\\\\/', $trait->name)) {
					$trait->name = $this->_namespace.'\\'.$trait->name;
				}


				if (false !== ($file_path = \feke\core\ClassLoader::classPlace($trait->name))) {
					$this->_trait[$key]->path = realpath($file_path);
					$match = realpath(root_path());
					$this->_trait[$key]->dir     = str_replace($match, '', realpath($file_path));
				}

				//読み込むかどうか
				if ($this->_over_load !== true) continue;

				//メソッド等の継承内容保保持するかどうか
				$load_flag = false;
				if (is_array($this->_trait[$key]->tag)) {
					foreach ($this->_trait[$key]->tag as $array) {
						if (in_array('load', $array)) $load_flag = true;;
					}
				}

				//トレイトの詳細を読み込みに行く
				if ($file_path) {
					if (!isset($Doc)) $Doc = \Feke::load ('Documentor', 'util');
					//リスト
					$load_trait = $Doc->run($file_path,'trait');
					if ($load_flag === true) {
						if (isset($load_trait->method)) $extend_method = array_merge($extend_method, $load_trait->method);
						if (isset($load_trait->property)) $extend_property = array_merge($extend_property ,$load_trait->property);
					}
				}
			}
		}

		//プロパティの取得
		$target = ['public', 'protected', 'private'];
		$kind = 'property';

		$cnt = count($this->_property);
		foreach ($target as $type) {

			$match1 = "/".self::MATCH_COMMENT."[\s\n\t]*{$type}[ ]+[$][a-z0-9_]+/is";
			$match2 = "/(.*){$type}[ ]+/is";

			$cnt = $search($cnt, $match1, $match2, $kind, $type, false, '_class_code');

			$match1 = "/".self::MATCH_COMMENT."[\s\n\t]*{$type}[ ]+static[ ]+[$][a-z0-9_]+/is";
			$match2 = "/(.*){$type}[ ]+static[ ]+/is";

			$cnt = $search($cnt, $match1, $match2, $kind, $type, true, '_class_code');
		}


		//メソッドの取得
		$target = ['public', 'protected', 'private'];
		$kind = 'method';
		$cnt = count($this->_method);
		foreach ($target as $type) {

			$match1 = "/".self::MATCH_COMMENT."[\s\n\t]*{$type}[ ]*function[ ]*[a-z0-9_]+[ ]*\((.*?)\)/is";
			$match2 = "/(.*){$type}[ ]+function[ ]*/is";

			$cnt = $search($cnt, $match1, $match2, $kind, $type, false, '_class_code');

			$match1 = "/".self::MATCH_COMMENT."[\s\n\t]*{$type}[ ]*static[ ]+function[ ]*[a-z0-9_]+[ ]*\((.*?)\)/is";
			$match2 = "/(.*){$type}[ ]+static[ ]+function[ ]*/is";

			$cnt = $search($cnt, $match1, $match2, $kind, $type, true, '_class_code');
		}

		//結合
		$this->_method   = array_merge($this->_method, $extend_method);
		$this->_property = array_merge($this->_property, $extend_property);

		//ソートする
		$public = array();
		$protected = array();
		$private = array();

		foreach ($this->_method as $key => $data) {
			if ($data->type == 'public') $public[$key] = $data;
			if ($data->type == 'protected') $protected[$key] = $data;
			if ($data->type == 'private') $private[$key] = $data;
		}
		$this->_method = $public + $protected + $private;


		//ソートする
		$public = array();
		$protected = array();
		$private = array();

		foreach ($this->_property as $key => $data) {
			if ($data->type == 'public') $public[$key] = $data;
			if ($data->type == 'protected') $protected[$key] = $data;
			if ($data->type == 'private') $private[$key] = $data;
		}
		$this->_property = $public + $protected + $private;

		return true;
	}
}