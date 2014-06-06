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
 * ファイルアップロードクラスです。
 *
 * 不正アップロード，拡張子偽装のチェックを行っています．
 *
 * **ファイルのアップロード**
 * フォームの名前属性を指定することで、対象ファイルをアップロードします。
 * {{{php|
 * //インスタンス作成
 * $Upload = \Feke::loadUtil('Upload');
 *
 * //保存先
 * $save_path = storage_path()."/image/image.jpg";
 *
 * //フォームの属性名
 * $field = 'image1';
 *
 * //保存
 * $Upload->save($save_path, $field);
 * }}}
 *
 * **画像のアップロードと編集**
 * 画像のアップロードと同時に、画像の編集を行います。
 * サンプルでは、200pxの角丸正方形にリサイズされた画像が保存されます。
 *
 * 画像編集オプションの指定方法は、\feke\util\Imageクラスを参照してください。
 * {{{php|
 * //アップロードのオプション
 * //jpg,png拡張子のみアップロードを許可し、ナンバリングを行わない
 * $option = ['extension' => 'jpg|png','numbering' => false];
 *
 * //画像編集のオプション
 * //200pxの角丸正方形の画像が保存されます。
 * $image_option = 'trim:100%:auto|resize:200:200:false|round:30:2';
 *
 * //保存
 * $Upload->saveImage($save_path, $field, $option, $image_option);
 * }}}
 *
 * **オプションに指定できるパラメータ**
 * これらのオプションを配列、又はオブジェクトにて指定可能です。
 * 指定がなかった場合は、設定ファイルの値が使用されます。
 * |~パラメータ名|必須|タイプ|内容|
 * |put_name||string|設置時のファイル名|
 * |max_size||numeric|ファイルの最大サイズ|
 * |extension||string|拡張子の制限('｜'区切り)|
 * |mime_type||string|MimeTypeの制限('｜'区切り)|
 * |overwrite||boolean|上書きの許可|
 * |numbering||boolean|重複したときのナンバリング|
 * |numbering_format||string|ナンバリングのフォーマット|
 * |numbering_start||numeric|ナンバリングの開始番号|
 * |numbering_must||boolean|ナンバリングの必須|
 * |use_copy||boolean|コピーをアップロードする|
 * |||||
 * >>>※saveAllの場合は無視されます。
 *
 *
 * @package    Feke
 * @subpackage util
 * @config /util/upload
 *
 */
class Upload extends filer\Filer
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * クラス内で使用する配列
	 */
	protected $_params;

	/**
	 * コンストラクタ
	 * @class hide
	 */
	public function __construct ($obj = null)
	{
		$this->_Config = \Feke::loadConfig ('/util/upload');
	}

	/**
	 * アップロードされたファイルの保存を行います。
	 *
	 * -同じファイルを複数回のアップロードする場合は，use_copy = trueを設定する必要があります．
	 * -子のメソッドは1つのファイルのみアップロードできます。
	 * -$optionの指定は上部を確認して下さい。
	 *
	 * @param string $save_path 設置先のディレクトリ名/ファイル名
	 * @param string $field アップロードしたいフォームのフィールド名
	 * @param mixied $option 各種設定
	 */
	public function save ($save_path, $field, $option = null)
	{
		//オプションをセット
		$this->_setOption($save_path, $field, $option);

		//ファイル内容を確認
		$this->_checkUpFile ();

		//ファイル書き込みのための準備
		//書き込めない場合はエラー
		if(true !== ($msg = $this->_checkWritable($this->_params->put_path))) $this->throwError($msg);

		//拡張子の書き換え
		if ($this->_params->extnsion_rewrite) $this->_params->put_extension = $this->_params->up_extension;

		// 移動/保存先のファイル名
		$this->_params->new_filepath = "{$this->_params->put_path}/{$this->_params->put_name}.{$this->_params->put_extension}";

		//ナンバリング
		$this->_numberingFileName ();

		//コピーをアップロードする
		if ($this->_params->use_copy) {
			if (!copy ($this->_params->tmp_name, $this->_params->new_filepath)) {
				$this->throwError('アップロードされたファイルの保存に失敗しました。', true);
			}
		// 一時ファイルを実際に移動
		} else {
			if (!move_uploaded_file($this->_params->tmp_name, $this->_params->new_filepath)) {
				$this->throwError('アップロードされたファイルの保存に失敗しました。', true);
			}
		}
		//アップロードしたファイルのデータを記録する
		$this->_setUpData();
		return true;
	}


	/**
	 * アップロードされた画像の保存をします。
	 *
	 * 画像加工用オプションを指定することで簡単な編集を行うことができます。
	 *
	 * アップロード用パラメータ,$optionは、save()メソッドと同じです．
	 *
	 * 画像加工用パラメータ「$image_option」の記載方法は、Imageクラスを確認して下さい。
	 *
	 * @param string $save_path 設置先のディレクトリ名/ファイル名
	 * @param string $field アップロードしたいフォームのフィールド名
	 * @param mixied $option 各種設定
	 * @param mixied $image_option 画像の編集設定
	 * @param string $permission  パーミッションの設定をします。必ず4桁で指定してください。
	 */
	public function saveImage($save_path, $field, $option, $image_option = null, $permission = null)
	{
		//オプションをセット
		$this->_setOption($save_path, $field, $option);

		//ファイル内容を確認
		$this->_checkUpFile ();

		// 移動/保存先のファイル名
		$this->_params->new_filepath = "{$this->_params->put_path}/{$this->_params->put_name}.{$this->_params->put_extension}";

		//ナンバリング
		$this->_numberingFileName ();

		//ファイル書き込みのための準備
		//書き込めない場合はエラー
		if(true !== ($msg = $this->_checkWritable($this->_params->put_path))) $this->throwError($msg);

		//画像加工クラス
		$Image = new \feke\util\Image();

		//画像を保存
		$Image->rootDir(null)
		      ->load($this->_params->tmp_name)
		      ->save($this->_params->new_filepath, $image_option);

		return true;
	}

	/**
	 * 設定値の確認
	 */
	protected function _setOption ($save_path, $field, $option)
	{
		$this->_params = new \stdClass();

		//アップロードするフォーム名
		$field = (string)$field;
		$this->_params->form_name = $field;

		//ファイルの情報を取得
		//一時ファイル名
		if (isset($_FILES[$field]['tmp_name'])) {
			$this->_params->tmp_name = $_FILES[$field]['tmp_name'];
		} else {
			$this->throwError('指定されたフォーム名のアップロードファイルが見つかりません。');
		}

		//保存作のファイルパス
		$save_path = (string)$save_path;
		if ($save_path) {
			//指定されたファイル名からファイル名と拡張子を取得
			$save_path = str_replace('\\','/',$save_path);
			$array = explode('/',$save_path);

			//ファイル名が含まれていた場合
			$file_name = $array[count($array) - 1];
			if (strpos($file_name, '.') > 0) {
				$name_array = explode('.',$file_name);
				$this->_params->put_fullname = $file_name;
				$this->_params->put_name = $name_array[0];
				$this->_params->put_extension = $name_array[1];
				$this->_params->put_path = str_replace("/{$file_name}", '', $save_path);
			} else {
				$this->_params->put_fullname = null;
				$this->_params->put_name = null;
				$this->_params->put_extension = null;
				$this->_params->put_path = rtrim($save_path, '/');
			}
		}
		if (!$this->_params->put_name) {
			if (isset($option['put_name']) and !$option['put_name'])$this->_params->put_name = $option['put_name'];
			$this->throwError('ファイル名が指定されていません。');
		}

		//最大サイズ
		if (isset($option['max_size']) and !$option['max_size']) {
			$this->_params->max_file_size = (int)$option['max_size'];
		} else {
			$this->_params->max_file_size = (int)$this->_Config->CONFIG->MAX_FILE_SIZE;
		}

		//許可する拡張子のセット
		if (isset($option['extension'])) {
			$this->_params->extension_list = explode('|',$option['extension']);
		} else {
			$this->_params->extension_list = null;
		}

		//許可するMimeTypeのセット
		if (isset($option['mime_type'])) {
			$this->_params->mime_type_list = explode('|',$option['mime_type']);
		} else {
			$this->_params->mime_type_list = null;
		}

		//上書き設定
		if (isset($option['overwrite'])) {
			$this->_params->overwrite = (bool)$option['overwrite'];
		} else {
			$this->_params->overwrite = (bool)$this->_Config->CONFIG->OVER_WRITE;
		}

		//ナンバリング設定
		if (isset($option['numbering'])) {
			$this->_params->numbering = (bool)$option['numbering'];
		} else {
			$this->_params->numbering = (bool)$this->_Config->CONFIG->NUMBERING;
		}

		//ナンバリングの書式設定
		if (isset($option['numbering_format'])) {
			$this->_params->numbering_format = (string)$option['numbering_format'];
		} else {
			$this->_params->numbering_format = (string)$this->_Config->CONFIG->NUMBERING_FORMAT;
		}

		//ナンバリングのスタート番号
		if (isset($option['numbering_start'])) {
			$this->_params->numbering_start = (int)$option['numbering_start'];
		} else {
			$this->_params->numbering_start = 1;
		}

		//ナンバリングの必須
		if (isset($option['numbering_must'])) {
			$this->_params->numbering_must = (bool)$option['numbering_must'];
		} else {
			$this->_params->numbering_must = (bool)$this->_Config->CONFIG->NUMBERING_MUST;
		}

		//拡張子一致の確認
		if (isset($option['extnsion_match'])) {
			$this->_params->extnsion_match = (bool)$option['extnsion_match'];
		} else {
			$this->_params->extnsion_match = (bool)$this->_Config->CONFIG->EXTENSION_MATCH;
		}

		//拡張子の書き換え
		if (isset($option['extnsion_rewrite'])) {
			$this->_params->extnsion_rewrite = (bool)$option['extnsion_rewrite'];
		} else {
			$this->_params->extnsion_rewrite = (bool)$this->_Config->CONFIG->EXTENSION_REWITE;
		}

		//コピーを使用してアップロードする
		if (isset($option['use_copy'])) {
			$this->_params->use_copy = (bool)$option['use_copy'];
		} else {
			$this->_params->use_copy = (bool)$this->_Config->CONFIG->USE_COPY;
		}
	}

	/**
	 * 設定に準じたアップロードファイルのチェック
	 */
	protected function _checkUpFile ()
	{
		//ここからアップロードされてファイル内容のチェック
		//ファイルサイズ
		$this->_params->up_size = filesize($this->_params->tmp_name);

		//ファイルの有無を確認
		if (!isset($_FILES[$this->_params->form_name])) {
			$this->throwError('アップロードファイルが見つかりません。');
		}

		//エラーがある場合
		if ($_FILES[$this->_params->form_name]['error'] > 0) {
			$this->throwError('アップロードファイルに何らかの不具合があります。', true);
		}

		//ファイルサイズ確認
		if ($this->_params->up_size > $this->_params->max_file_size) {
			$this->throwError("ファイルサイズは{$this->_params->max_file_size}Bまでです．");
		}

		//MimeTypeの確認
		//拡張子偽造チェック
		$fo = new \finfo(FILEINFO_MIME_TYPE);
		if (false === ($this->_params->up_mine_type = $fo->file($this->_params->tmp_name))) {
			$this->throwError('MimeTypeを取得できませんでした．',true);
		}

		//MimeTypeから拡張子を取得
		$this->_params->up_extension = $this->_mimeToExtension($this->_params->up_mine_type);

		//拡張子の制限
		if(is_array($this->_params->extension_list)) {
			if ((array_search($this->_params->up_extension, $this->_params->extension_list)) === false) {
				$this->throwError ("許可されていないファイル形式です．");
			}
		}

		//MineTypeの制限
		if (is_array($this->_params->mime_type_list)) {
			if ((array_search($this->_params->up_mine_type, $this->_params->mime_type_list)) === false) {
				$this->throwError ("許可されていないファイル形式です．");
			}
		}

		//正常にアップロードされたファイルか確認
		if (!is_uploaded_file($this->_params->tmp_name)) {
			$this->throwError ('正常にアップロードされたファイルではない可能性があります．', true);
		}
	}

	/**
	 * ファイル名のナンバリング処理
	 */
	protected function _numberingFileName ()
	{
		//ナンバリングが必須な場合
		if($this->_params->numbering_must && $this->_params->numbering === true && $this->_params->overwrite === true) {
			$number = $this->_params->numbering_start;
			$this->_params->new_filepath = "{$this->_params->put_path}/{$this->_params->put_name}".sprintf($this->_params->numbering_format,$number).".{$this->_params->put_extension}";
		}

		//同一ファイル存在の確認
		if ($this->_params->overwrite === false && is_file($this->_params->new_filepath)) {
			$this->throwError ('既にファイルが存在しています．');
			//ナンバリング処理
		} elseif ($this->_params->numbering === true) {
			if (is_file($this->_params->new_filepath)) {
				$number = $this->_params->numbering_start;
				do {
					$search_path = "{$this->_params->put_path}/{$this->_params->put_name}".sprintf($this->_params->numbering_format,$number).".{$this->_params->put_extension}";
					++$number;
				} while (is_file($search_path));
				$this->_params->new_filepath = $search_path;
			}
		}
	}

	/**
	 * アップロードしたファイルのデータを記録する
	 */
	protected function _setUpData ()
	{
		//アップロードしたファイルの情報を保存
		$this->_data[] = array (
				'path'  => $this->_params->new_filepath,
				'size'      => $this->_params->up_size,
				'extension' => $this->_params->put_extension,
				'mimetype'  => $this->_params->up_mine_type,
				'field' => $this->_params->form_name,
		);
	}

}