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
 * FekePHP用のテンプレートエンジンです。
 *
 * FekeParserはシンプルで必要最低限の機能のみ実装した簡易テンプレートエンジンです。
 * テンプレート内の書式は、smartyライクに仕上げています。
 *
 * テンプレート内で複雑な実装をしたい場合は、他のテンプレートエンジンを導入してください。
 * Viewプラグインの設定を変更することで変更できます。
 *
 * **初期設定**
 * ここでは、Viewプラグインで行っている初期設定を例にサンプルを紹介します。
 *
 * {{{php|
 * //インスタンス作成
 * $View = \Feke::load('FekeParser','util');
 *
 * //テンプレートディレクトリの設定
 * $View->rootDir (app_path().'/template');
 *
 * //コンパイルファイルのディレクトリの設定
 * $View->compileDir (cache_path().'/parser_compile');
 *
 * //キャッシュファイルのディレクトリの設定
 * $View->cacheDir (cache_path().'/parser_page');
 *
 * //ベーステンプレートの設定
 * $View->baseTemplate ('/base/def.tpl');
 *
 * //メインテンプレートの設定
 * // /コントローラ名/アクション名.tpl をテンプレートとして設定する
 * $mainTemplate = '/'.\Feke::_('controller').'/'.\Feke::_('action').'.tpl';
 * $View->mainTemplate ($mainTemplate);
 *
 * }}}
 * >>>実際は、FekePHPがすべて初期設定を行うのでキャッシュの生成関連の設定を除き、ユーザーが設定を行う必要はありません。
 *
 * **使用例**
 * ***操作側のphpファイル***
 * 先ほどの初期設定からの続きとなります。
 * {{{php|
 * //変数を割り当てる
 * $View->title = 'コーヒー図鑑';
 * $View->{'item.name'} = "アメリカンコーヒー";
 * $View->{'item.into'} = "美味しいコーヒーです。";
 *
 * //ブラウザへ出力
 * $View->display();
 *
 * //出力内容を取得したい場合は
 * $output = $View->fetch();
 * }}}
 *
 * ***ベーステンプレートファイル***
 * {include file="$mainTemplate"}の部分にメインのテンプレートが挿入されます。
 * {{{html|
 * <!DOCTYPE html>
 * <html lang="ja">
 * <head>
 * <title>{$title}</title>
 * </head>
 * <body>
 * {include file="$mainTemplate"}
 * </body>
 * </html>
 * }}}
 *
 * ***テンプレートファイル***
 * {{{html|
 * <h1>{$title}</h1>
 * <h2>{$item.name}</h2>
 * }}}
 *
 * ***表示内容***
 * {{{html|
 * <!DOCTYPE html>
 * <html lang="ja">
 * <head>
 * <title>コーヒー図鑑</title>
 * </head>
 * <body>
 * <h1>コーヒー図鑑</h1>
 * <h2>アメリカンコーヒー</h2>
 * </body>
 * </html>
 * }}}
 *
 * **変数の割り当て**
 * このクラスのインスタンスに突っ込むか、set()を使用することで、テンプレート内で使用する変数を割り当てます。
 *
 * データベースから取得した、オブジェクト形式の配列もそのまま割り当てることができます。
 * 配列・オブジェクトを挿入した場合は、内部ではともに配列として保存され、ドット記法にて呼び出すことができます。
 * その際に、オブジェクト内にあるメソッド等は消滅します。
 *
 * もし、オブジェクトをそのままテンプレート内で使用したい場合は、''setObject()''を使用してください。
 * テンプレート内で、メソッドチェーンを含め制限なく使用できるようになります。
 *
 * {{{php|
 * //インスタンスから直接挿入
 * $View->name = 'Feke';
 *
 * //メソッドから挿入
 * $View->set('name', 'Feke');
 *
 * //オブジェクトもそのまま挿入できます
 * //※スカラー型のみ保存され、メソッドなどは保持されません。
 * $Item->\Feke::load('ItemAR');
 * $View->item = $Item->find(1);
 *
 * //クラスをそのまま割り当てます
 * $View->setObject ('url', new \feke\util\Url());
 * }}}
 * >>テンプレート出力時に変数が割り当てられていなかった場合は、変数部分には何も表示されません。
 *
 * **テンプレート内での記述方法**
 * ***変数を出力***
 * 半角の大カッコ「{$変数名}」をテンプレートに挿入することで、予めセットした値を表示することができます。
 * その先、表示する値は自動でエスケープされます。
 *
 * エスケープをキャンセルしたい場合は、大カッコを2重にすることで実現可能です。
 * {{{php|
 * //挿入側
 * //エスケープされた状態で挿入されます。
 * {$name}
 *
 * //エスケープをせずに挿入されます。
 * {{$name}}
 *
 * //配列・オブジェクトをインスタンスに挿入、set(),assign()などで割り当てた場合
 * {$name.id}
 * {$name['id']}
 * {$name[0].id}
 * {$name.$id}
 *
 * //setObject()で割り当てた場合
 * {$url->now()}
 * {$url->this_url}
 * {$helper.url->now()}
 * {$helper.['url']->now()}
 * }}}
 *
 * ***変数のオプション***
 * -デフォルト値
 * 「 or 」の後に文字列を指定すると、前の変数がセットされていなかった時にデフォルトの値として表示されます。
 * {{{php|
 * {$name or '名無しさん'}
 * }}}
 *
 * -修飾子
 * 「|」の後にphp関数名、登録した修飾子用関数を指定すると、その関数を使用して変数を修飾子ます。
 * 関数には、第一引数に指定された変数、第二引数以降には「:」で区切られた文字列、又は変数が使用されます。
 *
 *
 * {{{php|
 * //php標準関数を使う場合
 * {$name|strtoupper} //$name 変数を全て大文字にする
 *
 * //複数使用することもできます
 * //コンパイルソースは、strtoupper(substr($name,1,3)) の順に生成されます。
 * {$name|strtoupper|substr:1:3} //$name 変数を全て大文字にし2～4文字目を切り出します。
 * }}}
 *
 * 修飾子を登録したい場合は、''setPlugin()''メソッドを使用してください。
 * {{{php|
 * //phpのstrtoupper関数をupperとして登録
 * $View->setPlugin('modifier', 'upper', 'strtoupper');
 *
 * //登録した修飾子を使う場合
 * {$name|upper} //$name 変数を全て大文字にする
 * }}}
 *
 *
 * ***if文を実行する***
 * phpのif文と同じようにif文をテンプレート内に埋め込むことができます。
 * {{{php|
 * //挿入側
 * $View->count = 5;
 *
 * //テンプレート側
 * {if $count > 10}
 *    $countは10以上です。
 * {elseif $count > 4}
 *    $countは5以上です。
 * {else}
 *    $countは{{＄count}}です。
 * {/if}
 * }}}
 *
 * ***foreachを実行する***
 * phpの条件文とほぼ同じ記載方法で、foreachを使用することができます。
 * {{{php|
 * //挿入側
 * $View->list = ['みかん','りんご','ごりら','らっぱ'];
 *
 * //テンプレート側
 * {foreach $list as $value}
 *     <li>{$value}</li>
 * {/foreach}
 *
 * //出力
 * <li>みかん</li>
 * <li>りんご</li>
 * <li>ごりら</li>
 * <li>らっぱ</li>
 * }}}
 * {{{php|
 * //挿入側
 * $View->list = ['みかん','りんご','ごりら','らっぱ'];
 *
 * //テンプレート側
 * {foreach $list as $key => $value}
 *     <li>({$key}){$value}</li>
 * {/foreach}
 *
 * //出力
 * <li>(0)みかん</li>
 * <li>(1)りんご</li>
 * <li>(2)ごりら</li>
 * <li>(3)らっぱ</li>
 * }}}
 *
 * ***foreachの条件内で指定した変数が空の場合***
 *{{{php|
 * //挿入側
 * $View->list = ['','','',''];
 *
 * //テンプレート側
 * {foreach $list as $value}
 *     <li>｛$value｝</li>
 * {foreachelse}
 *     変数は空っぽです！
 * {/foreach}
 *
 * //出力
 * 変数は空っぽです！
 * }}}
 *
 * ***forを実行する***
 * phpの条件文とほぼ同じ記載方法で、forを使用することができます。
 * {{{php|
 * //挿入側
 * $View->list = ['みかん','りんご','ごりら','らっぱ'];
 *
 * //テンプレート側
 * {for $i = 0; $i < 4 ;$i++}
 *     <li>{$value.$i}</li>
 * {/for}
 *
 * //出力
 * <li>みかん</li>
 * <li>りんご</li>
 * <li>ごりら</li>
 * <li>らっぱ</li>
 * }}}
 *
 *
 * ***他のテンプレートを読み込む***
 * includeを使用することで他のテンプレートを読み込むことができます。
 * ファイル名に変数を指定することもできますが、その際は必ず値を割り当てる必要があります。
 * {{{php|
 * //rootDir()で設定したあとのファイルパスを指定します。
 * {include file='/tmp/sidebar.tmp'}
 *
 * //変数でも指定できます
 * {include file='/tmp/$side_path'}
 * }}}
 *
 * ***他のテンプレートを読み込む(キャッシュを行わない)***
 * insertを使用することで他のテンプレートを読み込むことができます。
 * ファイル名に変数を指定することもできますが、その際は必ず値を割り当てる必要があります。
 *
 * キャッシュを行わないこと以外、{include}をほとんど変わりはありません。
 * ですが、キャッシュ使用時でも一部コンパイルが走ることになるので、必要以上に使用するとパフォーマンスに影響が出ます。
 * ログインユーザー名、カート情報など必要最低限の範囲内で使用してください。
 *
 * {{{php|
 * //rootDir()で設定したあとのファイルパスを指定します。
 * {insert file='/tmp/sidebar.tmp'}
 *
 * //変数でも指定できます
 * {insert file='/tmp/$side_path'}
 * }}}
 *
 * ***キャッシュを無効にする***
 * {nocache}～～{/nocache}は、指定した範囲についてキャッシュを無効にすることができます。
 * {{{php|
 * {nocache}
 *     最終ログイン {$time} <br>
 *     ユーザー名 {$user.name}
 * {/nocache}
 * }}}
 * >>dispay()を行う前に変数を割り当てる必要があります。
 * >>また、{include}は{nocache}の対象外です。{insert}を使用する必要があります。
 *
 * ***phpを実行する***
 * phpを直接実行したい場合は、{php}{/php}又は、｛｛｛｝｝｝で囲むと実行することができます。
 * {{{php|
 * {php}
 * echo 'PHPコードを自由に使用できます';
 * {/php}
 * }}}
 *
 * {{{php|
 * {{{
 *  echo 'PHPコードを自由に使用できます';
 * }}｝
 * }}}
 *
 * ***wiki文法を適用する***
 * FekePHPのWikiParserがwiki文法をHTMLへ変換します。
 * {{{php|
 * {wiki}
 * |1月|2月|3月|
 * |31 |29 |31 |
 * {/wiki}
 * }}}
 *
 * ***MarkDown文法を適用する***
 * MarkDown文法をHTMLへ変換します。
 * {{{php|
 * {MarkDown}
 * |1月|2月|3月|
 * |31 |29 |31 |
 * {/MarkDown}
 * }}}
 *
 * ***コメントを記載する***
 * {{{php|
 * {* コメントを自由にかけます *}
 *
 * {*
 * 改行も
 * 可能です。
 * *}
 * }}}
 *
 * ***外部ファイルを読み込む***
 * fileにURLを指定することで、そのサイトを読み込みテンプレートへ埋め込みます。
 * 内部のファイルパスを指定することで、テンプレート・cssなども読み込めますが、解析は行われません。
 * なお、内部ファイルのパスの指定は、絶対パスか、rootDir()で設定したパスからの絶対パスから読み込みます。
 * {{{php|
 * //外部サイトを読み込む
 * {fetch file='http://yahoo.co.jp'}
 *
 * //内部ファイルを読み込む
 * {fetch file='/var/webroot/js/menu.js'}
 * }}}
 *
 * @package    feke
 * @subpackage util
 */

class FekeParser
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * 基準ディレクトリ
	 * @var string
	 */
	protected $_rootDir;

	/**
	 * コンパイルキャッシュ保存ディレクトリ
	 * @var string
	 */
	protected $_compileDir;

	/**
	 * ページキャッシュ保存ディレクトリ
	 * @var string
	 */
	protected $_cacheDir;

	/**
	 * ベースに使用するテンプレートのパス
	 * @var string
	 */
	protected  $_baseTemplate_path;
	
	/**
	 * ベースに使用するテンプレートの名前（フルパス補完前、キャッシュ保存に使用）
	 * @var string
	 */
	protected  $_baseTemplate_name;

	/**
	 * テンプレートのパス
	 * @var string
	 */
	protected  $_template_path;

	/**
	 * 編集用スペース
	 * @var string
	 */
	protected $_temp_data;

	/**
	 * 編集している行数
	 * @var string
	 */
	protected $_count = 0;

	/**
	 * 編集している行のデータ
	 * @var string
	 */
	protected $_line;

	/**
	 * 条件に書かれているphp
	 * @var string
	 */
	protected $_roop_valiable;

	/**
	 * コンパイルキャッシュの保存時間
	 * @var numeric
	 */
	protected $_comp_cache_time = -1;

	/**
	 * ページキャッシュの保存時間
	 * @var numeric
	 */
	protected $_page_cache_time = -1;

	/**
	 * キャッシュ用のフラグ
	 * @var boolen
	 */
	protected $_page_cache_flag = false;

	/**
	 * コンパイルの更新日時チェックを行うか設定する
	 * @var boolen
	 */
	protected $_compileCheck = true;

	/**
	 * 強制的にコンパイルとキャッシュの更新を設定する
	 * @var boolen
	 */
	protected $_forceCompile = false;


	/**
	 * キャッシュを固有値ごとに行う
	 * @var string
	 */
	protected $_cacheId = false;


	/**
	 * キャッシュを保存するディテクトリパス
	 * @var string
	 */
	protected $_saveCacheDir;

	/**
	 * キャッシュファイルのパス
	 * @var string
	 */
	protected $_cache_path;

	/**
	 * コンパイルを保存するディテクトリパス
	 * @var string
	 */
	protected $_saveCompileDir;

	/**
	 * コンパイルファイルのパス
	 * @var string
	 */
	protected $_compile_path;

	/**
	 * テンプレートの更新時間
	 * @var string
	 */
	protected $_template_up_time;

	/**
	 * キャッシュを行わない部分のテキスト用配列
	 */
	protected $_nocache_text = array();
	
	/**
	 * プラグインの配列
	 */
	protected $_plugins = array();

	/**
	 * コンストラクター
	 */
	public function __construct ()
	{
		$this->_Config =\feke::loadConfig('/util/fekeparser');
	}
	
	/**
	 * テンプレートへ挿入する値をセットする
	 * @param string  $filed
	 * @param mixed   $value
	 * @class hide
	 */
	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	/**
	 * 変数を割り当てを行います。
	 *
	 * テンプレート内で使用する変数をセットします。
	 * 第一引数には、ドット記法を使用して配列を表現できます。
	 *
	 * なお、オブジェクトは内部で配列へ変換されるためメソッド等は消滅します。
	 * メソッドや関数を渡したい場合は、setObject()を使用してください。
	 *
	 * @param string  $filed 割り当てたい変数名
	 * @param mixed   $value 変数の値
	 *
	 * @example $View->set('name', 'Feke');
	 */
	public function set ($name, $value)
	{
		if (strpos($name, '_') === 0) $this->throwError ('最初がアンダーバーの変数名は使用できません。');

		//クラスの場合はそのまま

		//オブジェクトの場合は配列へ
		if (!is_scalar($value)) {
			$value = to_array($value);
		};

		if (strpos($name, '.') === false) {
			//ドット記法がない場合
			$this->_data[$name] = $value;
		} else {
			//ドット記法がある場合
			$params = explode('.', $name);

			$count = count($params);

			$add_value = $value;
			for ($i = $count - 1; $i >= 0; $i--) {
				$add_array = array();
				$add_array[$params[$i]] = $add_value;
				$add_value = $add_array;
			}

			$this->_data = array_merge_recursive($add_value, $this->_data);
		}
		return true;
	}

	/**
	 * オブジェクトを変数に割り当てます。
	 *
	 * テンプレート内で使用する変数をセットします。
	 * 第一引数には、ドット記法を使用して配列を表現できます。
	 *
	 * このメソッドを使用した場合は、set()とは違い内部ではオブジェクトとして扱われます。
	 * {$item.name}のようには使用できず、{$item->name}となります。
	 *
	 * @param string  $filed 割り当てたい変数名
	 * @param mixed   $value 変数の値
	 *
	 * @example $View->set('name', 'Feke');
	 */
	public function setObject ($name, $value)
	{
		if (strpos($name, '_') === 0) $this->throwError ('最初がアンダーバーの変数名は使用できません。');

		if (strpos($name, '.') === false) {
			//ドット記法がない場合
			$this->_data[$name] = $value;
		} else {
			//ドット記法がある場合
			$params = explode('.', $name);

			$count = count($params);

			$add_value = $value;
			for ($i = $count - 1; $i >= 0; $i--) {
				$add_array = array();
				$add_array[$params[$i]] = $add_value;
				$add_value = $add_array;
			}

			$this->_data = array_merge_recursive($add_value, $this->_data);
		}
		return true;
	}
	
	/**
	 * プラグインを登録します。
	 *
	 * @param string $type 登録するプラグインの型を定義します。変数に使用する修飾子の場合は"modifier"です。
	 * @param string $name 登録する関数名
	 * @param mixed  $callback 登録したいphpの関数名、無名関数、または['クラスorオブジェクト' => 'メソッド名']
	 *
	 * @example //phpのstrtoupper関数をupperとして登録
	 *          $View->setPlugin('modifier', 'upper', 'strtoupper');
	 *
	 *          //無名関数を登録
	 *          //$word+現在の時刻を返す
	 *          $View->setPlugin('modifier', 'now_time', function($word){return $word.date( "Y/m/d (D) H:i:s", time());});
	 *
	 *          //Urlクラスから現在のURLを返す修飾子を登録
	 *          $View->setPlugin('modifier', 'now_url', ['\feke\util\Url', 'now']);
	 */
	public function setPlugin ($type, $name, $callback)
	{
		if (!isset($this->_plugins[$type])) {
			$this->_plugins[$type] = array();
		}
		$this->_plugins[$type][$name] = $callback;
		
		return true;
	}

	/**
	 * テンプレートのディレクトリを登録します。
	 *
	 * テンプレートの読み込みは、includeを含めてこの基準を元に検索します。
	 *
	 * @param string  $dir  基準ディレクトリのパス
	 * @example $View->rootDir(app_path().'/template');
	 */
	public function rootDir ($dir)
	{
		$dir = rtrim($dir, '/');
		if (false !== ($dir = realpath($dir))) {
			$this->_rootDir = $dir;
		} else {
			$this->throwError("「{$dir}」は存在しないディレクトリです。");
		}
		return true;
	}

	/**
	 * コンパイルキャッシュを保存するディレクトリを登録します
	 *
	 * @param string  $dir  キャッシュを保存するのディレクトリパス
	 *
	 * @example $View->compileDir (cache_path().'/parser_compile');
	 */
	public function compileDir ($dir)
	{
		$dir = rtrim($dir, '/');
		if (false !== ($new_dir = realpath($dir))) {
			$this->_compileDir = $new_dir;
		} else {
			$this->throwError("「{$dir}」は存在しないファイルです。");
		}
	}


	/**
	 * ページキャッシュを保存するディレクトリを登録します
	 *
	 * @param string  $dir  キャッシュを保存するのディレクトリパス
	 * @example $View->cacheDir (cache_path().'/parser_page');
	 */
	public function cacheDir ($dir)
	{
		$dir = rtrim($dir, '/');
		if (false !== ($new_dir = realpath($dir))) {
			$this->_cacheDir = $new_dir;
		} else {
			$this->throwError("「{$dir}」は存在しないファイルです。");
		}
	}

	/**
	 * テンプレートを登録します。
	 *
	 * @param string  $dir  表示するテンプレートのパス
	 * @example $View->template_dir ('/class/index.tpl');
	 */
	public function mainTemplate ($dir)
	{
		if (strpos($dir, '/') !== 0) $dir = '/'.$dir;

		$this->_template_path = $this->_rootDir.$dir;
		//メインテンプレートとして登録しておく
		$this->{$this->_Config->main_template_name} = $dir;

		return true;
	}


	/**
	 * テンプレートのベースを登録します。
	 *
	 * template_dir()で設定したテンプレートファイルでなく、他のテンプレートファイルからコンパイルw開始したい場合に使用します。
	 * 複数のページで共通のひな形を使用している時に便利です。
	 *
	 * >>コンパイル・ページキャッシュはtemplate_dir()設定したファイル名を利用して作成されます。
	 * >>また、更新の確認基準がtemplate_dir()設定したファイルとなるため、base_dir ()で設定したファイルを更新しても、自動で再コンパイルされません。
	 * >>必要に応じて、手動でコンパイルキャッシュとページキャシュを削除してください。
	 * @param string  $dir  ベーステンプレートファイルのパス
	 * @example $View->baseTemplate ('/class/base.tpl');
	 */
	public function baseTemplate ($dir)
	{
		if ($dir) {
			if (strpos($dir, '/') !== 0) $dir = '/'.$dir;
			if (false !== ($new_dir = realpath($this->_rootDir.$dir))) {
				$this->_baseTemplate_name = $dir;
				$this->_baseTemplate_path = $new_dir;
			} else {
				$this->throwError("「{$dir}」は存在しないファイルです。");
			}
		} else {
			$this->_baseTemplate_path = null;
		}
		return true;
	}


	/**
	 * テンプレートの更新確認の有無を設定します。
	 *
	 * テンプレートファイルを更新しない場合のみfalseを設定してください。
	 * 更新の確認を行わなくなるので、ほんの少しの高速化が期待できます。
	 *
	 * @param boolen $flag trueの場合はテンプレートの更新確認を行います。
	 *
	 * @example //更新日時の確認をゃンセルする
	 *          $View->compileCheck(false);
	 */
	public function compileCheck ($flag)
	{
		if (is_bool($flag)) {
			$this->_compileCheck = $flag;
		} else {
			return $this->throwError("[boolen:flag:{$flag}]");
		}
	}

	/**
	 * 強制的にテンプレートをコンパイルします。
	 *
	 * いかなる設定でも強制的のコンパイルを実行します。
	 * 開発時に使用してください。
	 *
	 * @param boolen $flag trueの場合は強制的にテンプレートをコンパイルします。
	 * @example //テンプレートを強制的にコンパイルする。
	 *          $View->forceCompile(true);
	 */
	public function forceCompile ($flag)
	{
		if (is_bool($flag)) {
			$this->_forceCompile = $flag;
		} else {
			return $this->throwError("[boolen:flag:{$flag}]");
		}
	}

	/**
	 * キャッシュの使用を設定します
	 *
	 *  第二引数には、ページキャシュ保存期間を秒数で設定できます。
	 * ''-1''を指定した場合は、テンプレートに変更がない限り無限にキャシュを保持し続けます。
	 *
	 * @param boolen  $flag trueの場合は強制的にページキャッシュを行います。
	 * @param numeric $time ページキャッシュの保存期間
	 * @example //ページキャッシュを300秒保存
	 *          $View->caching (true, 300);
	 */
	public function caching ($flag, $time = null)
	{
		if (is_bool($flag)) {
			$this->_page_cache_flag = $flag;
		} else {
			return $this->throwError("[boolen:flag:{$flag}]");
		}

		if (is_numeric($time)) {
			$this->_page_cache_time = $time;
		} else {
			$this->throwError('キャッシュの保存期間は秒数で指定してください。');
		}
	}

	/**
	 * ページキャッシュの保存期間の設定をします
	 *
	 * ''-1''を指定した場合は、テンプレートに変更がない限り無限にキャシュを保持し続けます
	 *
	 * @param numeric $time ページキャッシュの保存期間
	 * @example //ページキャッシュを300秒保存
	 *          $View->caching (true, 300);
	 */
	public function cacheLifetime ($time)
	{
		if (is_numeric($time)) {
			$this->_page_cache_time = $time;
		} else {
			$this->throwError('キャッシュの保存期間は秒数で指定してください。');
		}
	}

	/**
	 * ユニークなキャシュを作成します。
	 *
	 * @param string $name キャッシュ名
	 * @example //セッションIDごとにページキャッシュを作成
	 *          $View->cacheId (session_id());
	 */
	public function cacheId ($name)
	{
		$this->_cacheId = $name;
	}


	/**
	 * キャシュが存在するか確認します
	 *
	 * @param string $template_path テンプレート名
	 * @param string $uniq ユニークなキャッシュ名
	 *
	 * @return ページキャッシュが存在する場合はtrueを,存在しない場合はfalseを返します。
	 * @example //ページキャシュがあったら、表示して終了する
	 *          if ($View->isCache ('/index.tpl')) {
	 *              $View->display();
	 *              return;
	 *          }
	 */
	public function isCache ($template_path = null, $uniq = null)
	{
		//引数からセット
		if ($template_path) $this->template_dir ($template_path);
		if ($uniq) $this->cacheId ($uniq);

		//キャッシュ関連のパスを取得
		$this->_getChachPath();

		//コンパイル関連のパスを取得
		$this->_getCompilePath();

		//コンパイルを使用ファイルの確認
		if ($this->_compileDir) {
			if (false === $this->_checkCompile ()) {
				return false;
			}
		}

		return $this->_checkCache();
	}

	/**
	 * ページキャシュの取得を行います。
	 *
	 * @param string $template_path テンプレート名
	 * @param string $uniq ユニークなキャッシュ名
	 *
	 * @return ページキャッシュが存在する場合はキャッシュの内容を,存在しない場合はfalseを返します。
	 * @example //ページキャッシュを表示する
	 *          echo $View->getCache ('/index.tpl');
	 */
	public function getCache ($template_path = null, $uniq = null)
	{
		//引数からセット
		if ($template_path) $this->mainTemplate ($template_path);
		if ($uniq) $this->cacheId ($uniq);

		//キャッシュ関連のパスを取得
		$this->_getChachPath();

		if (true === $this->_checkCache()) {
			return file_get_contents($this->_cache_path);
		}

		return false;
	}

	/**
	 * 指定したページキャッシュを削除します。
	 *
	 * 第一引数に指定がない場合は、template_dir()で設定したパスが使用されます。
	 *
	 * @param string $path 削除したいテンプレートのパス
	 * @return 成功した場合にtrueを、失敗した場合にfalseを返します。
	 *
	 * @example //'/index.tpl'のキャッシュが削除されます。
	 *          $View->template_dir ('/index.tpl');
	 *          $View->clearCache ();
	 */
	public function clearCache ($path = null)
	{
		//引数からセット
		if ($path) $this->template_dir ($path);

		//キャッシュ関連のパスを取得
		$this->_getChachPath();

		if (is_file($this->_cache_path)) {
			if (true === unlink($this->_cache_path)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * ページキャッシュをすべて削除します。
	 *
	 * @return 成功した場合にtrueを、失敗した場合にfalseを返します。
	 * @example //ページキャッシュがすべて削除されます。
	 *          $View->clearCacheAll ();
	 */
	public function clearCacheAll ()
	{
		//キャッシュ関連のパスを取得
		$this->_getChachPath();

		if (clear_directory($this->_cacheDir)) {
			return true;
		}
		return false;
	}

	/**
	 * 指定したコンパイルファイルを削除します。
	 *
	 * 第一引数に指定がない場合は、template_dir()で設定したパスが使用されます。
	 *
	 * @param string $path 削除したいテンプレートのパス
	 * @return 成功した場合にtrueを、失敗した場合にfalseを返します。
	 *
	 * @example //'/index.tpl'のキャッシュが削除されます。
	 *          $View->template_dir ('/index.tpl');
	 *          $View->clearCompile ();
	 */
	public function clearCompile ($path)
	{
		//キャッシュ関連のパスを取得
		$this->_getCompilePath();

		if (!$path) $path = $this->_compileDir;

		if (clear_directory($this->_compileDir)) {
			return true;
		}
		return false;
	}

	/**
	 * コンパイルを全て削除します。
	 *
	 * @return 成功した場合にtrueを、失敗した場合にfalseを返します。
	 * @example //コンパイルキャッシュがすべて削除されます。
	 *          $View->clearCompile_all ();
	 */
	public function clearCompileAll ()
{
		//キャッシュ関連のパスを取得
		$this->_getCompilePath();

		if (clear_directory($this->_compileDir)) {
			return true;
		}
		return false;
	}

	/**
	 * 指定した割り当てられた変数を削除します。
	 *
	 * @return 成功した場合にtrueを、失敗した場合にfalseを返します。
	 * @param string  $name 削除したい割り当てた変数名
	 *
	 * @example $View->clearAssign ('user.name');
	 */
	public function clearAssign ($name)
	{
		return $this->set($name, null);
	}

	/**
	 * 割り当てられた変数を全て削除します。
	 *
	 * @return 成功した場合にtrueを、失敗した場合にfalseを返します。
	 * @example $View->clear_assign_all ();
	 */
	public function clearAssignAll ()
	{
		$this->_data = array();
		return true;
	}

	/**
	 * テンプレートを表示します。
	 *
	 * 設定された内容を元に、コンパイル、キャッシュの生成などを行います。
	 *
	 * @param string $template_path テンプレート名
	 * @param string $uniq ユニークなキャッシュ名
	 *
	 * @example $View->display();
	 */
	public function display ($template_path = null, $uniq = null)
	{
		//各作業用変数を初期化
		$this->_nocache_text = array();
		$this->_count = 0;
		$this->_temp_data = null;


		//引数からセット
		//テンプレート名
		if ($template_path) {
			$this->mainTemplate ($template_path);
		}

		//テンプレートの有無の確認
		if (false === ($new_dir = realpath($this->_template_path))) {
			$this->throwError("「{$this->_template_path}」は存在しないファイルです。");
		}

		//ユニークな値
		if ($uniq !== null) {
			$this->cacheId ($uniq);
		}

		//キャッシュ関連のパスを取得
		$this->_getChachPath();

		//コンパイル関連のパスを取得
		$this->_getCompilePath();

		//キャッシュファイルの確認
		$live_cache = false;
		if ($this->_page_cache_flag === true) {
			$live_cache = $this->_checkCache();
		}

		//コンパイルを使用ファイルの確認
		$live_compile = false;
		if ($this->_compileDir) {
			$live_compile = $this->_checkCompile ();
		}

		//ファイル作成時間が取得できなかった場合と,
		//キャッシュをしない場合は、
		//テンプレートをコンパイル
		$compile_flag = false;
		if ($live_compile === true and $live_cache === true and $this->_forceCompile === false) {
			//キャッシュが生きている場合
			$this->_temp_data = file_get_contents($this->_cache_path);

		} elseif ($live_compile === true and $this->_forceCompile === false) {
			//コンパイルが存在している場合
			$this->_temp_data = file_get_contents($this->_compile_path);

		} else {
			//テンプレートをコンパイル
			$this->_compileTemplate ();
			$compile_flag = true;
			//キャッシュを行う場合は
			//コンパイルしたテンプレートを保存
			if ($this->_compileDir) {
				$this->_saveCompile();
			}
		}

		//ページを出力する
		//キャッシュが有効な場合は、キャッシュを保存する
		if ($this->_page_cache_flag === true and ($compile_flag === true or $live_cache === false)) {
			//キャシュの保存
			$this->_saveCache ();

			//すべて展開する
			$this->_includeFile(true);

			//{nocache}タグを削除
			$this->_replaceNocacheTag();

			//出力
			eval ('?>'.$this->_temp_data);
		} else {
			//すべて展開する
			$this->_includeFile(true);

			//{nocache}タグを削除
			$this->_replaceNocacheTag();

			//出力
			eval ('?>'.$this->_temp_data);
		}
	}

	/**
	 * テンプレートの出力内容を返します。
	 *
	 * display()とは、テンプレートを表示せずに値として返す以外は動作内容に差はありません。
	 *
	 * @param string $template_path テンプレート名
	 * @param string $uniq ユニークなキャッシュ名
	 *
	 * @example $View->fetch();
	 */
	public function fetch ($template_path = null, $uniq = null)
	{
		//出力のバッファリングの開始
		ob_start();

		//通常通りテンプレートを出力
		$this->diplay ($template_path, $uniq);

		//出力のバッファリングの取得
		$page_text = ob_get_contents();

		//出力バッファをクリア
		ob_end_clean();

		//返す
		return $page_text;
	}

	/**
	 * コンパイルファイルを取得します
	 */
	public function getCompile ($template_path = null)
	{
		//各作業用変数を初期化
		$this->_nocache_text = array();
		$this->_count = 0;
		$this->_temp_data = null;


		//引数からセット
		//テンプレート名
		if ($template_path) {
			//ループ防止
			//$this->mainTemplate ($template_path);
			$this->_template_path = $template_path;
		}

		//テンプレートの有無の確認
		if (false === ($new_dir = realpath($this->_template_path))) {
			$this->throwError("「{$this->_template_path}」は存在しないファイルです。");
		}

		//コンパイル関連のパスを取得
		$this->_getCompilePath();

		//コンパイルを使用ファイルの確認
		$live_compile = false;
		if ($this->_compileDir) {
			$live_compile = $this->_checkCompile ();
		}

		//ファイル作成時間が取得できなかった場合と,
		//キャッシュをしない場合は、
		//テンプレートをコンパイル
		$compile_flag = false;
		if ($live_compile === true and $this->_forceCompile === false) {
			//コンパイルが存在している場合
			$this->_temp_data = file_get_contents($this->_compile_path);

		} else {
			//テンプレートをコンパイル
			$this->_compileTemplate ();
			$compile_flag = true;
			//キャッシュを行う場合は
			//コンパイルしたテンプレートを保存
			if ($this->_compileDir) {
				$this->_saveCompile();
			}
		}

		return $this->_temp_data;
	}
	/**
	 * キャッシュの有無を確認します。
	 *
	 * @return キャッシュが有効な場合はtrue,無効な場合はfalseを返します。
	 */
	protected function _checkCache ()
	{
		//キャッシュの有無を返す
		if (is_file($this->_cache_path)) {
			$page_filemtime = filemtime($this->_cache_path);

			//設定されたキャッシュ時間を超えた場合は、再コンパイル
			//キャッシュ保存時間が-1の場合は無限キャッシュ
			if (time() - $page_filemtime < $this->_page_cache_time or $this->_page_cache_time === - 1) {
				return true;
			}
		}
		return false;
	}

	/**
	 * コンパイルの有無を確認します。
	 *
	 * @return コンパイルが有効な場合はtrue,無効な場合はfalseを返します。
	 */
	protected function _checkCompile ()
	{
		//テンプレートの最終更新時間
		$tmp_up_time = filemtime($this->_template_path);
		$this->_template_up_time = $tmp_up_time;
		//コンパイルがあったら
		if (is_file($this->_compile_path)) {
			//更新日時の確認を行わない場合
			if ($this->_compileCheck === false) {
				return true;
			} else {
				if (is_file($this->_compile_path)) {
					//コンパイルしたテンプレートの最終更新時間
					$comp_up_time = filemtime($this->_compile_path);
					if ($tmp_up_time === $comp_up_time) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * キャッシュを保存します。
	 *
	 * @return 成功した場合は、出力内容を返す
	 */
	protected function _saveCache ()
	{
		//nocache部を除く
		$this->_replaceNocache ();

		//出力のバッファリングの開始
		ob_start();

		//バッファへ出力
		eval ('?>'.$this->_temp_data);

		//出力のバッファリングの取得
		$page_text = ob_get_contents();

		//出力バッファをクリア
		ob_end_clean();

		//nocache部を戻す
		foreach ($this->_nocache_text as $key => $text) {
			$page_text = str_replace("{nocache}{$key}{/nocache}", $text, $page_text);
			$this->_temp_data = str_replace("{nocache}{$key}{/nocache}", $text, $this->_temp_data);
		}

		//キャッシュを行う場合は
		//コンパイルしたテンプレートを保存
		//ディレクトリがなかった場合は作成
		if (!file_exists($this->_saveCacheDir)) {
			if (false === mkdir ($this->_saveCacheDir, 0777, true)) {
				$this->throwError ("ディレクトリ「{$this->_saveCacheDir}」の生成に失敗しました。");
			}
		}
		//キャッシュを保存
		if (false === file_put_contents($this->_cache_path, $page_text)) {
			$this->throwError ("テンプレート名「{$this->_template_path}」の<br>ページキャッシュの生成に失敗しました。");
		}
	}

	/**
	 * コンパイルファイルを保存します
	 */
	protected function _saveCompile()
	{
		//ディレクトリがなかった場合は作成
		if (!file_exists($this->_saveCompileDir)) {
			if (false === mkdir ($this->_saveCompileDir, 0777, true)) {
				$this->throwError ("ディレクトリ「{$this->_saveCompileDir}」の生成に失敗しました。");
			}
		}
		//コンパイルを保存
		if (false === file_put_contents($this->_compile_path, $this->_temp_data)) {
			$this->throwError ("テンプレート名「{$this->_template_path}」の<br>コンパイルキャッシュの生成に失敗しました。");
		}

		//最終更新日をテンプレートと合わせる
		if (false === touch($this->_compile_path, $this->_template_up_time)) {
			$this->throwError ("テンプレート名「{$this->_template_path}」の<br>最終更新日変更に失敗しました。");
		}
		return true;
	}

	/**
	 * キャッシュファイルのパスを取得します。
	 *
	 * 使用するキャッシュディレクトリと、
	 * キャッシュファイルの名前を準備してプロパティにセットします。
	 */
	protected function _getChachPath()
	{
		if (! $this->_cacheDir) {
			$this->throwError('キャッシュ保存用のディレクトリが設定されていません。');
		}

		//キャッシュ保存先ディレクトリ
		$this->_saveCacheDir = $this->_cacheDir.''.rtrim(str_replace($this->_rootDir,'',$this->_template_path), basename($this->_template_path));

		//キャシュのファイル名
		if ($this->_cacheId) {
			$cacheId = urlencode($this->_cacheId).'.';
		} else {
			$cacheId = null;
		}
		$cache_name = ''.basename($this->_template_path,'.tpl').'.tmp.'.$cacheId.'html';

		//キャッシュ保存先ファイルパス

		$this->_cache_path = $this->_saveCacheDir.$cache_name;
	}

	/**
	 * コンパイルファイルのパスを取得します。
	 *
	 * 使用するコンパイルディレクトリと、
	 * コンパイルファイルの名前を準備してプロパティにセットします。
	 */
	protected function _getCompilePath ()
	{
		if (! $this->_compileDir) {
			$this->throwError('コンパイル保存用のディレクトリが設定されていません。');
		}
		//コンパイル保存先ディレクトリ
		$this->_saveCompileDir = $this->_compileDir.''.rtrim(str_replace($this->_rootDir,'',$this->_template_path), basename($this->_template_path));

		//コンパイルのファイル名
		$basetpm_plus = null;
		if ($this->_baseTemplate_name) {
			$basetpm_plus = "_base_".str_replace(['/','\\',':','?','*','|','"'],'_',$this->_baseTemplate_name);
		}
		$cache_name = ''.basename($this->_template_path,'.tpl').$basetpm_plus.'.tmp.php';

		//コンパイル保存先ファイルパス
		$this->_compile_path = $this->_saveCompileDir.$cache_name;
	}


	/**
	 * テンプレートをコンパイルします
	 */
	protected function _compileTemplate ($load_skip = null)
	{
		if ($load_skip !== true) {
			//テンプレートを取得
			if ($this->_baseTemplate_path) {
				$this->_temp_data = file_get_contents($this->_baseTemplate_path);
			} else {
				$this->_temp_data = file_get_contents($this->_template_path);
			}
		}

		//すべて展開する
		//$this->_includeFile2();

		//コメントを消去
		$this->_replaceComment();

		//余計なスペースを削除する
		$this->_replaceSpace();

		//phpコード部分を設定
		$this->_replacePhp();

		// 改行タグを統一
		$order = array("\r\n","\r");
		$this->_temp_data = str_replace($order, "\n", $this->_temp_data);

		//余計なインデントを削除
		$this->_temp_data = preg_replace("/\n(\s{4})+{[A-Za-z0-9_$(){]]|\n\t+{[A-Za-z0-9_$(){]]/u", "\n{", $this->_temp_data);
		
		//改行ごとに分割
		$this->_temp_data = explode("\n", $this->_temp_data);
		$this->_count = 0;
		
		//行ごとに処理
		while ($this->_count < count($this->_temp_data)) {
			$this->_compileWhile();
			$this->_count++;
		}

		//文字列に戻す
		$this->_temp_data = implode("\n", $this->_temp_data);
		
		//コンパイル済のincludeを取り込む
		//すべて展開する
		$this->_includeFile();
		
		//変数を置換
		$this->_replaceVariable();

		//wiki文法部分を設定
		$this->_replaceWiki ();
		
		//MarkDown文法部分を設定
		$this->_replaceMarkDown ();

		//外部ファイルを挿入
		$this->_replaceFetch ();
	}

	/**
	 * コンパイルのループ部分
	 */
	protected function _compileWhile ($first_variable = null)
	{
		$this->_line = $this->_temp_data[$this->_count];
		//編集している行
		$old_count = $this->_count;

		//if文を処理
		$this->_replaceIf();

		//foreach文を処理
		$this->_replaceForeach ($first_variable);

		//for文を処理
		$this->_replaceFor ();

		//while文を処理
		$this->_replaceWhile ();

		//カウントが動いていない場合は置き換え
		if ($old_count === $this->_count) {
			$this->_temp_data[$this->_count] = $this->_line;
		}

	}

	/**
	 * テンプレートを読み込みます
	 */
	protected function _includeFile ($flag = null)
	{
		//マッチ対象
		if (is_true($flag)) {
			$match_word = 'insert';
		} else {
			$match_word = 'include';
		}
		
		//操作中のファイル名
		$now_file_name = $this->_template_path;

		$function = function ($match) use (&$now_file_name){
			//ファイル名を取得
			$file_name = $match[1];
			
			if (strpos($file_name, '$') !== false) {
				if (false === ($load_file_name = $this->_toPropertyValue($file_name))) {
					$this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>{$match_word}内の変数「{$file_name}」は必ずセットする必要があります。");
				}
			} else {
				$load_file_name = $file_name;
			}

			if (is_file(realpath ($this->_rootDir.$load_file_name))) {
				$clone = clone $this;
				$clone->baseTemplate(null);
				$file_data = $clone->getCompile($this->_rootDir.$load_file_name);
				unset($clone);
			} else {
				return $this->throwError("テンプレートエンジンエラー Path : {$this->_rootDir}{$load_file_name}<br>ファイル「{$load_file_name}」は存在しないため、{include}が行えませんでした。");
			}
			//操作中のファイル名を変更
			$now_file_name = $load_file_name;
			return $file_data;
		};

		do {
			if (strpos($this->_temp_data, "{{$match_word}") !== false) {
				//読み込み前の文字列の長さ
				$strlen = strlen($this->_temp_data);
				$this->_temp_data = preg_replace_callback ("/{".$match_word."[ ]+file[ ]*=[ ]*[\'\"](.*?)[\'\"]}/s", $function, $this->_temp_data);
			} else {
				break;
			}
		} while ($strlen != strlen($this->_temp_data));
	}
	
	/**
	 * テンプレートを読み込みます(旧var)
	 */
	protected function _includeFile2 ()
	{
		//操作中のファイル名
		$now_file_name = $this->_template_path;

		$function = function ($match) use (&$now_file_name){

			$function2 = function ($match)  use ($now_file_name){
				if (isset($this->_data[$match[1]])) {
					return $this->_data[$match[1]];
				} else {
					$this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>{inclde}内の変数「{$match[1]}」は必ずセットする必要があります。");
				}
			};

			//ファイル名を取得
			$file_name = $match[1];

			//変数を展開
			if (strpos($file_name, '$') !== false) {
				$file_name = preg_replace_callback ('/\$([A-Za-z0-9_]+)/',$function2, $file_name);
			}

			if (is_file($new_file_name = realpath ($this->_rootDir.$file_name))) {
				$file_data = file_get_contents($new_file_name);
			} else {
				return $this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>ファイル「{$this->_rootDir}{$file_name}」は存在しないため、{include}が行えませんでした。");
			}
			//操作中のファイル名を変更
			$now_file_name = $new_file_name;

			return $file_data;
		};

		do {
			if (strpos($this->_temp_data, '{include') !== false) {
				//読み込み前の文字列の長さ
				$strlen = strlen($this->_temp_data);

				$this->_temp_data = preg_replace_callback ("/{include[ ]+file[ ]*=[ ]*[\'\"](.*?)[\'\"]}/s", $function, $this->_temp_data);
			} else {
				break;
			}
		} while ($strlen != strlen($this->_temp_data));
	}

	
	/**
	 * テンプレートを読み込みます(旧ver キャッシュを行わない)
	 */
	protected function _insertFile2 ()
	{
		//操作中のファイル名
		$now_file_name = $this->_template_path;

		$function = function ($match) use (&$now_file_name){

			$function2 = function ($match)  use ($now_file_name){
				if (isset($this->_data[$match[1]])) {
					return $this->_data[$match[1]];
				} else {
					$this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>{insert}内の変数は必ずセットする必要があります。");
				}
			};

			//ファイル名を取得
			$file_name = $match[1];

			//変数を展開
			if (strpos($file_name, '$') !== false) {
				$file_name = preg_replace_callback ('/\$([A-Za-z0-9_]+)/',$function2, $file_name);
			}

			if (is_file($new_file_name = realpath ($this->_rootDir.$file_name))) {
				$file_data = file_get_contents($new_file_name);
			} else {
				return $this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>ファイル「{$this->_rootDir}{$file_name}」は存在しないため、{insert}が行えませんでした。");
			}
			//操作中のファイル名を変更
			$now_file_name = $new_file_name;

			return $file_data;
		};

		$insert_flag = false;

		do {
			if (strpos($this->_temp_data, '{insert') !== false) {
				//読み込み前の文字列の長さ
				$strlen = strlen($this->_temp_data);

				$this->_temp_data = preg_replace_callback ("/{insert[ ]+file[ ]*=[ ]*[\'\"](.*?)[\'\"]}/s", $function, $this->_temp_data);

				if ($strlen != strlen($this->_temp_data)) {
					$insert_flag = true;
					continue;
				} else {
					break;
				}
			} else {
				break;
			}
		} while (1);

		//insertが行われていた場合
		if ($insert_flag) {
			$this->_compileTemplate (true);
		}
	}

	/**
	 * 外部ファイルを挿入します
	 */
	protected function _replaceFetch ()
	{
		$function = function ($match) use (&$now_file_name){

			$function2 = function ($match)  use ($now_file_name){
				if (isset($this->_data[$match[1]])) {
					return $this->_data[$match[1]];
				} else {
					$this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>{fetch}内の変数は必ずセットする必要があります。");
				}
			};

			//ファイル名を取得
			$file_name = $match[1];

			//変数を展開
			if (strpos($file_name, '$') !== false) {
				$file_name = preg_replace_callback ('/\$([A-Za-z0-9_]+)/',$function2, $file_name);
			}

			if (strpos($file_name, 'http://') === 0) {
				if (false !== ($loadFile = mb_convert_encoding(file_get_contents($file_name), 'UTF-8', 'auto'))) {
					return $loadFile;
				} else {
					return;
				}

			} else {
				if (!($new_file_name = realpath ($file_name))) {
					$new_file_name = realpath ($this->_rootDir.$file_name);
				}

				if (is_file($new_file_name)) {
					$file_data = file_get_contents($new_file_name);
				} else {
					return $this->throwError("テンプレートエンジンエラー Path : {$now_file_name}<br>ファイル「{$this->_rootDir}{$file_name}」は存在しないため、{fetch}が行えませんでした。");
				}
			}

			return $file_data;
		};

		do {
			//読み込み前の文字列の長さ
			$strlen = strlen($this->_temp_data);

			$this->_temp_data = preg_replace_callback ("/{fetch[ ]+file[ ]*=[ ]*[\'\"](.*?)[\'\"]}/s", $function, $this->_temp_data);
		} while ($strlen != strlen($this->_temp_data));
	}


	/**
	 * キャッシュを行わない部分を切り分けます
	 */
	protected function _replaceNocache ()
	{
		//操作中のファイル名
		$now_file_name = $this->_template_path;

		$function = function ($match) use (&$now_file_name, &$count){
			//対象部分を取得
			$this->_nocache_text[] = $match[1];

			$new_text = "{nocache}".$count."{/nocache}";
			$count++;
			return $new_text;
		};

		if (strpos($this->_temp_data, '{nocache}') !== false) {
			$count = 0;
			$this->_temp_data = preg_replace_callback ("/{nocache}(.*?){\/nocache}/s", $function, $this->_temp_data);
		}
	}


	/**
	 * {nochache}タグを削除します。
	 */
	protected function _replaceNocacheTag ()
	{
		if (strpos($this->_temp_data, '{nocache}') !== false) {
			$this->_temp_data = preg_replace("/{nocache}(.*?){\/nocache}/s", '$1', $this->_temp_data);
		}
	}


	/**
	 * コメントを消去します。
	 */
	protected function _replaceComment ()
	{
		$this->_temp_data = preg_replace ("/{\*(.*?)\*}/s", '', $this->_temp_data);
	}

	/**
	 * 右側の余計なスペースを消去します。
	 */
	protected function _replaceSpace ()
	{
		$this->_temp_data = preg_replace ("/\s+$/m", '', $this->_temp_data);
	}

	/**
	 * phpコード部を置換します。
	 */
	protected function _replacePhp ()
	{
		if (strpos($this->_temp_data, '{php}') !== false) {
			$this->_temp_data = preg_replace ("/{php}(.*?){\/php}/s", "<?php\n$1\n?>", $this->_temp_data);
		}
		$this->_temp_data = preg_replace ("/{{{(.*?)}}}}/s", "<?php\n$1\n?>", $this->_temp_data);
	}

	/**
	 * wiki部を置換します。
	 */
	protected function _replaceWiki ()
	{
		if (strpos($this->_temp_data, '{wiki}') !== false) {
			$function = function ($match) {
				return mold_wiki($match[1] ,true);
			};
			$this->_temp_data = preg_replace_callback ("/{wiki}(.*?){\/wiki}/s", $function, $this->_temp_data);
		}
	}
	
	/**
	 * markdown部を置換します。
	 */
	protected function _replaceMarkDown ()
	{
		if (strpos($this->_temp_data, '{wiki}') !== false) {
			$function = function ($match) {
				return mold_markdown($match[1] ,true);
			};
			$this->_temp_data = preg_replace_callback ("/{markdown}(.*?){\/markdown}/s", $function, $this->_temp_data);
		}
	}


	/**
	 * ifを置換します。
	 */
	protected function _replaceIf ()
	{
		//if部
		if (strpos($this->_line, '{if ') === 0) {
			$list = substr($this->_line, 4, -1);

			//条件文を展開
			$list = $this->_moldConditions3 ($list);

			$this->_line = "<?php if ({$list}) { ?>";
		}

		//else部
		if (strpos($this->_line, '{elseif ') === 0) {
			$list = substr($this->_line, 7, -1);

			//条件文を展開
			$list = $this->_moldConditions3 ($list);

			$this->_line = "<?php } elseif ({$list}) { ?>";
		}

		//else部
		if (strpos($this->_line, '{else}') === 0) {

			$this->_line = "<?php } else { ?>";
		}

		//end部
		if ($this->_line === '{/if}') {
			$this->_line = '<?php } ?>';
		}
	}

	/**
	 * foreachを置換します。
	 */
	protected function _replaceForeach ($oya_variable)
	{
	//else部
		if (strpos($this->_line, '{foreach ') === 0) {
			//foreachの先頭が小文字だと何故かなくなる！！
			//要検証
			$list = substr($this->_line, 8, -1);

			//条件リスト
			$this->_roop_valiable = explode(' ', str_replace(['(',')'], '',$list));

			//先頭の変数を取得
			preg_match_all ('/\$([A-Za-z0-9_.()\[\]\'\"$->]+)/',$list, $m);
			$first_variable = $m[1][0];
			$first_variable_text = "\${$m[1][0]}";

			//その他
			array_shift($m[1]);
			$other_variable_array = $m[1];

			//上色ループ内の変数を引き継いでいるかどうか
			$entend_variable = false;
			if (is_array($oya_variable) and in_array ($first_variable, $oya_variable)) {
				$entend_variable = true;
			}
			//統一
			if (strpos($first_variable, '[') !== false) {
				$first_variable = preg_replace('/\[[\"\']?(.*?)[\"\']?\]/','.$1', $first_variable);
			}

			//分割
			$params = explode('.', $first_variable);
			if (!$entend_variable) {
				$first_variable = '$this->_data["'.implode("\"][\"",$params).'"]';
			} else {
				$first_variable = '$'.$params[0];
				array_shift($params);
				if (count($params) > 0) {
					$first_variable .= '["'.implode("\"][\"",$params).'"]';
				}
			}

			//関数部を作成
			if (strpos($first_variable, '->') !== false) {
				$first_variable = preg_replace('/->([A-Za-z0-9_]+)\((.*?)\)"]/','"]->$1($2)', $first_variable);
			}

			//条件文を作成
			$list = $first_variable.str_replace ($first_variable_text, '', $list);

			//出力する条件文
			$this->_temp_data[$this->_count] = "<?php if (isset({$first_variable}) and (is_array({$first_variable}) or is_object({$first_variable})) and $first_variable) { ?>\n";
			$this->_temp_data[$this->_count] .= '<?php Foreach ('.$list.') { ?>';

			$else_flag = false;

			do {
				$this->_count++;
				$this->_compileWhile($other_variable_array);

				//end部
				if (!isset($this->_temp_data[$this->_count]) or $this->_temp_data[$this->_count] === '{/foreach}') break;

				//foreachelse部
				if ($this->_temp_data[$this->_count] === "{foreachelse}") {
					$this->_temp_data[$this->_count] = "<?php } ?>\n<?php } else { ?>";
					$else_flag = true;
				} else {
					$this->_temp_data[$this->_count] = preg_replace_callback ("/{{[ ]*[$](.*?)[ ]*}}/s", [$this, '_moldRoopVariable'], $this->_temp_data[$this->_count]);
					$this->_temp_data[$this->_count] = preg_replace_callback ("/{[ ]*[$](.*?)[ ]*}/s", [$this, '_moldRoopVariableToEscape'], $this->_temp_data[$this->_count]);
				}
			} while (1);

			$this->_temp_data[$this->_count] = '<?php } ?>';
			if ($else_flag === false) $this->_temp_data[$this->_count] .= '<?php } ?>';
		}
	}

	/**
	 * forを置換します。
	 */
	protected function _replaceFor ()
	{
		//else部
		if (strpos($this->_line, '{for ') === 0) {
			//foreachの先頭が小文字だと何故かなくなる！！
			//要検証
			$list = substr($this->_line, 4, -1);

			//変数リスト
			$this->_roop_valiable = explode(' ', str_replace(['(',')'], '',$list));

			//条件文を展開
			$list = $this->_moldConditions ($this->_roop_valiable, $list);

			//出力する条件文
			$this->_temp_data[$this->_count] = '<?php for ('.$list.") { ?>";

			do {
				$this->_count++;
				//end部
				if (!isset($this->_temp_data[$this->_count]) or $this->_temp_data[$this->_count] === '{/for}') break;

				$this->_temp_data[$this->_count] = preg_replace_callback ("/{{[ ]*[$](.*?)[ ]*}}/s", [$this, '_moldRoopVariable'], $this->_temp_data[$this->_count]);
				$this->_temp_data[$this->_count] = preg_replace_callback ("/{[ ]*[$](.*?)[ ]*}/s", [$this, '_moldRoopVariableToEscape'], $this->_temp_data[$this->_count]);
			} while (1);

			$this->_temp_data[$this->_count] = '<?php } ?>';
		}
	}

	/**
	 * whileを置換します。
	 */
	protected function _replaceWhile ()
	{
		//else部
		if (strpos($this->_line, '{while ') === 0) {
			//foreachの先頭が小文字だと何故かなくなる！！
			//要検証
			$list = substr($this->_line, 6, -1);

			//変数リスト
			$this->_roop_valiable = explode(' ', str_replace(['(',')'], '',$list));

			//条件文を展開
			$list = $this->_moldConditions ($this->_roop_valiable, $list);

			//出力する条件文
			$this->_temp_data[$this->_count] = '<?php while ('.$list.") { ?>";

			do {
				$this->_count++;
				//end部
				if (!isset($this->_temp_data[$this->_count]) or $this->_temp_data[$this->_count] === '{/while}') break;

				$this->_temp_data[$this->_count] = preg_replace_callback ("/{{[ ]*[$](.*?)[ ]*}}/s", [$this, '_moldRoopVariable'], $this->_temp_data[$this->_count]);
				$this->_temp_data[$this->_count] = preg_replace_callback ("/{[ ]*[$](.*?)[ ]*}/s", [$this, '_moldRoopVariableToEscape'], $this->_temp_data[$this->_count]);
			} while (1);

			$this->_temp_data[$this->_count] = '<?php } ?>';
		}
	}

	/**
	 * 変数を置換します。
	 */
	protected function _replaceVariable ()
	{
		$this->_temp_data = preg_replace_callback ("/{{[$](.*?)}}/s", [$this, '_moldVariable'], $this->_temp_data);
		$this->_temp_data = preg_replace_callback ("/{[$](.*?)}/s", [$this, '_moldVariableToEscape'], $this->_temp_data);

	}
	
	/**
	 * 変数名を、プロパティ表記に変換する
	 */
	protected function _toProperty ($name)
	{
		if (strpos($name, '$') === 0) {
			//配列表記をドット記法へ統一
			if (strpos($name, '[') !== false) {
				$name = preg_replace('/\[[\"\']?(.*?)[\"\']?\]/','.$1', $name);
			}
			$name = mb_substr($name, 1);
			$param = explode('.', $name);
		}

		$name = "\$this->_data[\"".implode("\"][\"", $param)."\"]";
		$name = str_replace("\"][\"\"]", "[]\"]", $name);
		return $name;
	}
	
	/**
	 * 変数名を、プロパティ表記に変換し、存在するかチェックする
	 *
	 * @return 存在した場合は値を、しない場合はfalseを返します。
	 */
	protected function _toPropertyValue ($name)
	{
		if (strpos($name, '$') === 0) {
			//配列表記をドット記法へ統一
			if (strpos($name, '[') !== false) {
				$name = preg_replace('/\[[\"\']?(.*?)[\"\']?\]/','.$1', $name);
			}
			$name = mb_substr($name, 1);
			$param = explode('.', $name);
		}
		
		$value = $this->_data;
		foreach ($param as $field) {
			if (isset($value[$field])) {
				$value = $value[$field];
			} else {
				return false;
			}
		}
		
		return $value;
	}

	/**
	 * ドット表記の変数名を配列表記に変換します。
	 */
	protected function _moldVariable ($match)
	{
		return "<?php echo ".$this->_getData($match[1])."; ?>";
	}

	/**
	 * ドット表記の変数名を配列表記に変換し、エスケープします。
	 */
	protected function _moldVariableToEscape ($match)
	{
		return "<?php echo h(".$this->_getData($match[1])."); ?>";
		//$param = explode('.', $match[1]);
		/*return "<?php echo h(\$this->_data[\"".implode("\"][\"", $param)."\"]); ?>";*/
	}

	/**
	 * ドット表記の変数名を配列表記に変換します。(ループ用)
	 */
	protected function _moldRoopVariable ($match)
	{
		$param = explode('.', $match[1]);

		//変数名
		$valiable_name = $param[0];
		if (in_array("\${$valiable_name}", $this->_roop_valiable) === false) {
			//条件文にない場合は、$this->_dataから取得
			return $this->_moldVariable ($match);

		} else {
			//ある場合
			array_shift ($param);
			$array_text = null;
			if (!empty($param)) {
				$array_text = "[\"".implode("\"][\"", $param)."\"]";
				$array_text = str_replace("\"][\"\"]", "[]\"]", $array_text);
			}
			return "<?php echo \${$valiable_name}{$array_text}; ?>";
		}
	}

	/**
	 * ドット表記の変数名を配列表記に変換し、エスケープします。(ループ用)
	 */
	protected function _moldRoopVariableToEscape ($match)
	{
		$param = explode('.', $match[1]);

		//変数名
		$valiable_name = $param[0];
		if (in_array("\${$valiable_name}", $this->_roop_valiable) === false) {
			//条件文にない場合は、$this->_dataから取得
			return $this->_moldVariable ($match);

		} else {
			//ある場合
			array_shift ($param);
			$array_text = null;
			if (!empty($param)) {
				$array_text = "[\"".implode("\"][\"", $param)."\"]";
				$array_text = str_replace("\"][\"\"]", "[]\"]", $array_text);
			}
			return "<?php echo h(\${$valiable_name}{$array_text}); ?>";
		}
	}

	/**
	 * 各条件文内の変数を置き換えます。
	 */
	protected function _moldConditions ($validables, $list)
	{
		//置き換え
		foreach ($validables as $key1 => $name) {
			$put_array = false;

			if (strpos($name, '$') === 0) {
				//配列表記をドット記法へ統一
				if (strpos($name, '[') !== false) {
					$name = preg_replace('/\[[\"\']?(.*?)[\"\']?\]/','.$1', $name);
				}

				$name = mb_substr($name, 1);
				$param = explode('.', $name);
				$valiable_name = $param[0];

				if (isset($this->_data[$valiable_name])) {
					$put_array = $this->_data[$valiable_name];
					foreach ($param as $key => $value) {
						if ($key === 0) continue;

						if (isset($put_array[$value])) {
							$put_array = $put_array[$value];
						} else {
							$put_array = false;
						}
					}
				}else {
					$put_array = false;
				}
			}

			if ($put_array !== false) {
				$list = str_replace ("{$name}","\$this->_data[\"".implode("\"][\"", $param)."\"]",$list);
				$list = str_replace("\"][\"\"]", "[]\"]", $list);
			}
		}

		//関数部を作成
		if (strpos($list, '->') !== false) {
			$list = preg_replace('/->([A-Za-z0-9_]+)\((.*?)\)"]/','"]->$1($2)', $list);
		}

		return $list;
	}


	/**
	 * 各条件文内の変数を置き換えます。
	 *
	 * 主にif系統が使用
	 */
	protected function _moldConditions3 ($list)
	{
		//urlエンコード
		$new_list = preg_replace_callback("/\[(.*?)\]/u",function($match){return '['.urlencode($match[1]).']';},$list);
		
		preg_match_all("/[$][a-zA-Z0-9_\%\[\].]+/",$new_list, $validables);
		
		$isset_text = null;
		
		//置き換え
		foreach ($validables[0] as $name) {
			$param = array();
			
			//配列表記をドット記法へ統一
			$new_name = $name;
			if (strpos($new_name, '[') !== false) {
				$new_name = preg_replace('/\[[\"\']?(.*?)[\"\']?\]/u','.$1', $new_name);
			}

			$new_name = mb_substr($new_name, 1);
			$param = explode('.', $new_name);
				
			if ($new_list !== $list) {
				foreach ($param as $key => $value) {
					$param[$key] = urldecode($value);
				}
			}
			
			$new_text = "\$this->_data[\"".implode("\"][\"", $param)."\"]";
			
			$new_list = str_replace("{$name}",$new_text,$new_list);
			
			//issetを添付
			if ($isset_text) $isset_text .= ' and ';
			$isset_text .= "isset({$new_text})";
			
			$new_list = str_replace("\${$name}","\$this->_data[\"".implode("\"][\"", $param)."\"]",$new_list);
			$new_list = str_replace("\"][\"\"]", "[]\"]", $new_list);
			
		}
		$new_list = "({$isset_text}) and ({$new_list})";
		

		//関数部を作成
		if (strpos($new_list, '->') !== false) {
			$new_list = preg_replace('/->([A-Za-z0-9_]+)\((.*?)\)"]/','"]->$1($2)', $new_list);
		}

		return $new_list;
	}


	/**
	 * テンプレートの変数を展開します。
	 * @param unknown $name
	 */
	protected function _getData ($name)
	{
		//OR文の処理
		$deff_value = null;
		if (strpos($name, ' or ') !== false) {
			$deff_value = preg_replace("/^.*or /",'',$name);
			$name = preg_replace("/ or.*/",'',$name);
		}
		
		//オプション関数の処理
		$function_list = array();
		if (strpos($name, '|') !== false) {
			preg_match_all ("/\|([\w$:.\[\]]+)/",$name, $function_list);
			$function_list = $function_list[1];
			$name = preg_replace('/\|(.*)/', '', $name);
		}
		
		//統一
		if (strpos($name, '[') !== false) {
			$name = preg_replace('/\[[\"\']?(.*?)[\"\']?\]/','.$1', $name);
		}

		//分割
		$params = explode('.', $name);
		
		//変数が入っていた場合は展開
		foreach ($params as $key => $value) {
			if (strpos($value, '$') === 0) $params[$key] = '{'.$this->_toProperty($value).'}';
		}

		//関数部を作成
		//デフォルト値の設定はなし！
		if (strpos($name, '->') !== false) {
			$valiable_text = "\$this->_data[\"".implode("\"][\"", $params)."\"]";
			if (strpos($name, '(') !== false) {
				$new_valiable_text = preg_replace('/->([A-Za-z0-9_]+)(\(.*?\))?"]/','"]->$1$2', $valiable_text);
			} else {
				$new_valiable_text = preg_replace('/->([A-Za-z0-9_]+)"]/','"]->$1', $valiable_text);
			}
			if ($new_valiable_text !== $valiable_text) {
				return $new_valiable_text;
			}
		}
		
		// 変数の修飾子
		$function_front = null;
		$function_back  = null;
		
		foreach ($function_list as $fnc) {
			$func_param = null;
			$user_func = false;
			if (strpos($fnc, ':') !== false) {
				preg_match_all ("/:([\w\.$]+)/",$fnc, $param_list);
				
				$fnc = preg_replace ("/\:(.*)/", '', $fnc);
				
				foreach ($param_list[1] as $key => $param) {
					if (strpos($param, '$') === 0) {
						$param = $this->_toProperty($param);
					}
					$func_param = ', '.$param;
				}
				
			}
			
			if (isset($this->_plugins['modifier'][$fnc])) {
				if (is_string($this->_plugins['modifier'][$fnc])) {
					$function_front .= "\$this->_plugins['modifier']['{$fnc}'] (";
				} elseif (is_array($this->_plugins['modifier'][$fnc])) {
					$user_func = true;
					$function_front .= "call_user_func_array (array(\$this->_plugins['modifier']['{$fnc}'][0], \$this->_plugins['modifier']['{$fnc}'][1]), array(";
				} elseif (is_callable($this->_plugins['modifier'][$fnc])) {
					$user_func = true;
					$function_front .= "call_user_func_array (\$this->_plugins['modifier']['{$fnc}'], array(";
				}
				
			} else {
				$function_front .= "{$fnc}(";
			}
			if($user_func) $function_back = $func_param.'))'.$function_back;
			else $function_back = $func_param.')'.$function_back;
		}
		
		//オプションによって変更
		if ($deff_value === null) {
			$valiable_text = "{$function_front}(isset(\$this->_data[\"".implode("\"][\"", $params)."\"])) ? \$this->_data[\"".implode("\"][\"", $params)."\"] : ''{$function_back}";
		} else {
			$valiable_text = "(isset(\$this->_data[\"".implode("\"][\"", $params)."\"])) ? {$function_front}\$this->_data[\"".implode("\"][\"", $params)."\"]{$function_back} : $deff_value";
		}
		$valiable_text = str_replace("\"][\"\"]", "[]\"]", $valiable_text);
		//dump($valiable_text);
		return $valiable_text;

	}

}


