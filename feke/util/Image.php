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

namespace feke\util;

/**
 * 画像を加工するクラスです。
 *
 * GDライブラリを使用して，画像を加工するクラスです．
 * 加工できるファイルは，jpeg,png,gifのみです．
 *
 * 使用できる機能は，リサイズ、トリミング、画像形式の変換、フィルター等です。
 *
 * **使用方法**
 * 読み込む画像の指定、トリミング・クロップ、リサイズ、フィルタリング、保存・出力の順にメソッドを呼び出します。
 * {{{php|
 * //インスタンス作成
 * $Image = \Feke::loadUtil('Image');
 *
 * //基準ディレクトリの設定
 * $Image->rootDir (storage_path());
 *
 * //加工する画像の設定
 * $Image->load ('/image/sky.png');
 *
 * //リサイズの設定を行う（横500px,縦:300px,アスペクト比の維持,設定サイズの維持）
 * $Image->resize (500, 300 , true ,true);
 *
 * //設定を元に画像を保存
 * $Image->save ('/image/sky_2.png');
 * }}}
 * {{{php|
 * //メソッドチェーンでも加工を行えます。
 * $Image->setRoot (storage_path())
 *       ->load ('/image/sky.png')
 *       ->grayScale ()
 *       ->bright (30)
 *       ->resize (500, 300)
 *       ->save ('/image/sky_2.png');
 * }}}
 * {{{php|
 * //オプション用の引数を指定して編集することもできます
 * $Image->setRoot (storage_path())
 *       ->load ('/image/sky.png')
 *       ->save ('/image/sky_2.png','grayScale|bright:30|resize:500:300');
 * }}}
 *
 * **サンプル（正方形角丸サムネイル作成）**
 * 200ｘ200のサムネイルの作成サンプルです。
 * {{{php|
 * //加工する画像の設定
 * $Image->load ('/image/sky.png')
 *     //リサイズの設定を行う（横200px,縦:200px の正方形）
 *     ->resize (200, 200, false)
 *     //トリムを行う
 *     ->trim ('100%', 'auto')
 *     //角丸20px
 *     ->round (20, 2)
 *     //出力
 *     ->output();
 * }}}
 *
 * **加工メソッド**
 * |~メソッド名|備考|
 * |resize|リサイズ|
 * |trim|トリミング|
 * |crop|クロップ|
 * |limit|最大・最小画像サイズを設定|
 *
 * **オプションメソッド**
 * |~メソッド名|備考|
 * |bgColor|背景色の設定 |
 * |jpegQuality|jepgの品質設定|
 * |pngQuality|pngの品質設定|
 *
 * **フィルターメソッド**
 * 画像をリサイズ後にかける、フィルターを使用できます。
 * これらは、呼び出された順に使用され、同じフィルターを複数回呼ぶこともできます。
 * |~フィルタ-名|備考|
 * |round|角丸|
 * |grayScale|グレースケール|
 * |bright|輝度|
 * |contrast|コントラスト|
 * |colorBalance|カラーバランス|
 * |edge|エッジの強調|
 * |emboss|エンボス加工|
 * |gauss|ガウスぼかし|
 * |blur|ぼかし|
 * |sketch|スケッチ風|
 * |smooth|滑らかにする|
 * |mosaic|モザイク|
 *
 *
 * **編集オプションの指定方法**
 * 編集オプションは「|」で区切ることによりいくつでも指定することができます。
 * resize、trim、brightなどメソッドに引数が有る場合は、「:」で区切って指定してください。
 * {{{php|
 * //画像編集のオプション
 * //200pxの角丸正方形の画像が保存されます。
 * $image_option = 'trim:100%:auto|resize:200:200:false|round:30:2';
 * }}}
 * @package    Feke
 * @subpackage util
 *
 */
class Image
{
	/**
	 * クラスベース読み込み
	 */
	use \feke\base\ClassBase;

	/**
	 * 入力ファイル名
	 * @var unknown
	 */
	protected $_in_name;

	/**
	 * 出力ファイル名
	 * @var unknown
	 */
	protected $_out_name;

	/**
	 * 画像読み込み用パレット
	 * @var unknown
	 */
	private $_in_image;

	/**
	 * 画像加工用パレット
	 * @var unknown
	 */
	private $_out_image;

	/**
	 * 画像加工パラメータ
	 * @var array
	 */
	private $_parm = array();

	/**
	 * 基準ディレクトリ
	 * @var string
	 */
	private $_rootDir = FEKE_ROOT_PATH;

	/**
	 * コンストラクタ
	 *
	 * @class hide
	 */
	public function __construct()
	{
	}

	/**
	 * 加工する画像パス・URLの設定を行います。
	 *
	 * @param string $name 加工する画像ファイルのパス、またはURL
	 * @return $this
	 * @example $Image->load ('/image/sky.png');
	 *          $Image->load ('https://www.google.co.jp/images/nav_logo170_hr.png');
	 */
	public function load ($name)
	{
		$this->_in_name = $name;

		return $this;
	}


	/**
	 * 基準操作ディレクトリの設定をします。
	 *
	 * デフォルトでは、''FEKE_ROOT_PATH''が基準となっています。
	 *
	 * @param string $dir_name 各引数の前に加えたいディレクトリ名
	 * @example $Image->rootDir (storage_path());
	 */
	public function rootDir ($dir_name)
	{
		$dir_name = rtrim($dir_name, '/');
		$this->_rootDir = $dir_name;

		return $this;
	}

	/**
	 * 背景色の設定
	 *
	 * キャンパスの背景色です．
	 * RGB又は、16進数形式(#FFFFFF)で設定してください．
	 *
	 * デフォルトは，白:RGB(255,255,255) がセットされています．
	 * @param numeric $red 赤の要素または、16進表記（#FFFFFF）のカラーコード
	 * @param numeric $blue 緑の要素
	 * @param numeric $green 青の要素
	 * @return $this
	 * @example $Image->bgColor (100, 100 ,100);
	 */
	public function bgColor ($red, $bule = null, $green = null)
	{
		if (strpos($red, '#') === 0) {
			$color = sscanf($red, '#%2x%2x%2x');
			$red = $color[0];
			$bule = $color[1];
			$green = $color[2];
		}

		if (false === ($im = imagecreatetruecolor(100, 100))) {
			$this->throwError('下地用画像画像が生成できませんでした。');
		}
		if (false === ($this->_parm['bgParet'] = imagecolorallocate ($im, $red, $bule, $green))) {
			$this->throwError('背景色の指定が不正な可能性があります。');
		} else {
			$this->_parm['bgColor'] = true;
		}
		return $this;
	}


	/**
	 * 最大・最小画像サイズを設定します。
	 *
	 * 一括ですべての制限値をセットできます．
	 *
	 * ※resize(,,$ratio = true)の場合は動作しません
	 *
	 * @param numric $max_width  縦のピクセル数
	 * @param numric $max_height 横のピクセル数
	 * @param numric $min_width  縦のピクセル数
	 * @param numric $min_height 横のピクセル数
	 *
	 * @return $this
	 * @example $Image->limit (500, 300, 400, 200);
	 */
	public function limit ($max_width, $max_height, $min_width = 0, $min_height = 0)
	{
		// サイズ変更後の横サイズ
		$this->_parm['max_x'] = $max_width;

		// サイズ変更後の縦サイズ
		$this->_parm['max_y'] = $max_height;

		// サイズ変更後の横サイズ
		$this->_parm['min_x'] = $min_width;

		// サイズ変更後の縦サイズ
		$this->_parm['min_y'] = $min_height;

		return $this;
	}

	/**
	 * 画像をクロップします。
	 *
	 * 座標を指定して画像を切り抜きます。
	 * 引数で-数値を指定した場合は、画像右下からの座標を指定できます。
	 * また、%での指定も可能です。
	 *
	 * @param numeric $cps_x x軸上のトリミング開始位置
	 * @param numeric $cps_y y軸上のトリミング開始位置
	 * @param numeric $cpe_x x軸上のトリミング終了位置
	 * @param numeric $cpe_y y軸上のトリミング終了位置
	 *
	 * @return $this
	 * @example $Image->crop (20, 20%, -20, -20%);
	 */
	public function crop ($cps_x, $cps_y, $cpe_x, $cpe_y){
		// サイズ変更後の横サイズ
		$this->_parm['cps_x'] = $cps_x;

		// サイズ変更後の縦サイズ
		$this->_parm['cps_y'] = $cps_y;

		// サイズ変更後の横サイズ
		$this->_parm['cpe_x'] = $cpe_x;

		// サイズ変更後の縦サイズ
		$this->_parm['cpe_y'] = $cpe_y;

		return $this;
	}

	/**
	 * 画像をトリミングします。
	 *
	 * 画像の中心から、指定されたサイズ分をトリミングします。
	 * 引数は、ピクセル指定、パーセント指定が可能です。
	 *
	 * 第二引数に''auto''を指定した場合は、第一引数に指定されたサイズを正方形にトリミングします。
	 *
	 * @param numeric $tr_x 画像中央からの幅
	 * @param numeric $tr_y 画像中央からの高さ
	 * @return $this
	 * @example $Image->trim ('50%', '50%');
	 */
	public function trim ($tr_x, $tr_y){
		// サイズ変更後の横サイズ
		$this->_parm['tr_x'] = $tr_x;

		// サイズ変更後の縦サイズ
		$this->_parm['tr_y'] = $tr_y;

		return $this;
	}

	/**
	 * 画像を角丸にします。
	 *
	 * 第一引数には各丸の半径をピクセル表記で指定し、
	 * 第二引数の''$level''は、角丸作成時の元画像の倍率を指定できます。
	 * 大きいほど、高品質な画像が作成できますが、リソースを大量に消費します。最高で2000px程度（1000pxの画像で2倍）に収まる倍率に設定してください。
	 *
	 * @param numeric $size  角丸の半径(px)
	 * @param numeric $level 角丸作成時のの倍率
	 * @return $this
	 * @example $Image->round (10);
	 */
	public function round ($size, $level = 1)
	{
		if (!is_numeric($level)) {
			$this->throwError("[numeric:level:{$level}]");
		}
		$this->_parm['filter'][] = ['round' ,$size, $level];
		return $this;
	}

	/**
	 * グレースケールで保存します。
	 *
	 * @return $this
	 * @example $Image->smooth (100);
	 */
	public function grayScale ()
	{
		$this->_parm['filter'][] = ['grayscale'];
		return $this;
	}

	/**
	 * 画像の輝度を変更します。
	 *
	 * @param numeric $level 輝度レベル
	 * @return $this
	 * @example $Image->bright (100);
	 */
	public function bright ($level)
	{
		$this->_parm['brightness'] = true;
		if (!is_numeric($level)) {
			$this->throwError("[numeric:level:{$level}]");
		}
		$this->_parm['filter'][] = ['brightness' ,$level];
		return $this;
	}

	/**
	 * 画像のコントラストを変更します。
	 *
	 * @param numeric $level コントラストレベル
	 * @return $this
	 * @example $Image->contrast (10);
	 */
	public function contrast ($level)
	{
		if (!is_numeric($level)) {
			$this->throwError("[numeric:level:{$level}]");
		}
		$this->_parm['filter'][] = ['contrast' ,$level];
		return $this;
	}

	/**
	 * カラーバランスを変更します。
	 *
	 * @param numeric $red 赤コンポーネントの値
	 * @param numeric $blue 緑コンポーネントの値
	 * @param numeric $green 青コンポーネントの値
	 * @param numeric $alfa アルファチャネル。 0 から 127 までの値で、0 は完全な不透明、127 は完全な透明を表す。
	 * @return $this
	 * @example $Image->colorBalance (100, 100, 100, 0);
	 */
	public function colorBalance ($red, $bule, $green, $alfa)
	{
		if (!is_numeric($red)) $this->throwError("[numeric:red:{$red}]");

		if (!is_numeric($bule)) $this->throwError("[numeric:blue:{$blue}]");

		if (!is_numeric($green)) $this->throwError("[numeric:green:{$green}]");

		if (!is_numeric($alfa)) $this->throwError("[numeric:alfa:{$alfa}]");
		$this->_parm['filter'][] = ['colorBalance', $red, $blue, $green, $alfa];
		return $this;
	}

	/**
	 * 画像のエッジを強調します。
	 *
	 * @return $this
	 * @example $Image->edge ();
	 */
	public function edge ()
	{
		$this->_parm['filter'][] = ['edge'];
		return $this;
	}

	/**
	 * 画像をエンボス加工します。
	 *
	 * @return $this
	 * @example $Image->emboss ();
	 */
	public function emboss ()
	{
		$this->_parm['filter'][] = ['emboss'];
		return $this;
	}

	/**
	 * 画像にガウスぼかしをかけます。
	 *
	 * @return $this
	 * @example $Image->gauss ();
	 */
	public function gauss ()
	{
		$this->_parm['filter'][] = ['gauss'];
		return $this;
	}

	/**
	 * 画像にぼかしをかけます。
	 *
	 * @return $this
	 * @example $Image->blur ();
	 */
	public function blur ()
	{
		$this->_parm['filter'][] = ['blur'];
		return $this;
	}

	/**
	 * 画像をスケッチ風にします。
	 *
	 * @return $this
	 * @example $Image->sketch ();
	 */
	public function sketch ()
	{
		$this->_parm['filter'][] = ['sketch'];
		return $this;
	}

	/**
	 * 画像を滑らかにします。
	 *
	 * @param numeric $level 平滑度レベル
	 * @return $this
	 * @example $Image->smooth (100);
	 */
	public function smooth ($level)
	{
		if (!is_numeric($level)) {
			$this->throwError("[numeric:level:{$level}]");
		}
		$this->_parm['filter'][] = ['smooth' ,$level];
		return $this;
	}

	/**
	 * 画像にモザイクを掛けます。
	 *
	 * モザイク効果を画像に適用します。 第一引数でブロックの大きさを、第二引数でモザイクの品質を指定します。
	 *
	 * @param numeric $level モザイクのブロックのサイズ（ピクセル）
	 * @param boolen  $mode  trueの場合は高品質なモザイクを掛けます。
	 * @return $this
	 * @example $Image->mosaic ();
	 */
	public function mosaic ($level, $mode = false)
	{
		if (!is_numeric($level)) {
			$this->throwError("[numeric:level:{$level}]");
		}

		$this->_parm['filter'][] = ['mosaic' ,$level, is_true($mode)];
		return $this;
	}


	/**
	 * jepgの品質設定を行います。
	 *
	 * JEPG画像ファイルの画質設定です．
	 * （低）1～100（高）の間で調整できます．
	 *
	 * @param numeric $quality JPEG画像の保存品質設定。
	 * @return $this
	 * @example $Image->jpegQuality (500);
	 */
	public function jpegQuality ($quality)
	{
		if ($quality > 0 and $quality <=100) {
			$this->_parm['jpegQuality'] = $quality;
		}
		return $this;
	}



	/**
	 * リサイズの設定を行います。
	 *
	 * @param numric $width       新しい画像の幅。
	 * @param numric $height      新しい画像の高さ。
	 * @param boolen $keepRatio   trueの場合は、アスペクト比の維持。
	 * @param boolen $keepSize    $keepRatioと$keepSizeがtrueの場合、アスペクト比の調整で余った部分の背景を描画します。
	 *
	 * @return $this
	 * @example $Image->resize (500, 300 ,true, true);
	 */
	public function resize ($width, $height = null, $keepRatio = true, $keepSize = false)
	{
		//アスペクト比を維持
		$this->_parm['keepRatio'] = is_true($keepRatio);

		//サイズを維持
		$this->_parm['keepSize'] = is_true($keepSize);

		if ($this->_parm['keepRatio']) {
			// サイズ変更後の横サイズ
			if ($width) {
				$this->_parm['size_x'] = $width;
			} else {
				$this->_parm['size_x'] = 0;
			}

			// サイズ変更後の縦サイズ
			if ($height) {
				$this->_parm['size_y'] = $height;
			} else {
				$this->_parm['size_y'] = 0;
			}
		} else {
			// サイズ変更後の横サイズ
			if (is_numeric($width) and $width > 0) {
				$this->_parm['max_x'] = $width;
			} else {
				$this->_parm['max_x'] = 0;
			}

			// サイズ変更後の縦サイズ
			if (is_numeric($height) and $height > 0) {
				$this->_parm['max_y'] = $height;
			} else {
				$this->_parm['max_y'] = 0;
			}
		}
		return $this;
	}

	/**
	 * 画像を保存します。
	 *
	 * @param string $output_path 保存先のパス
	 * @param string $option 画像編集オプション
	 * @param string $permission  パーミッションの設定をします。必ず4桁で指定してください。
	 *
	 * @return 保存に成功した場合は、trueを返します。
	 * @example $Image->save ('/sky2.png');
	 */
	public function save ($output_path, $option = null, $permission = null)
	{
		try {
			//編集オプションの実行
			if ($option) $this->_doOption ($option);

			//出力ファイル名
			//$new_path = dirname($output_path);
			$this->_out_name = $this->_rootDir.$output_path;
			if ($this->_out_name == '' or $this->_out_name == $this->_rootDir) {
				$this->_out_name = $this->_in_name;
			}

			//出力形式
			$this->_parm['output_type'] = substr(strrchr($this->_out_name, '.'), 1);
			$target = ['jpg','png', 'gif'];
			if (!in_array($this->_parm['output_type'], $target)) throw new \Exception ('対応していない保存形式です。');

			//画像を編集
			$this->_editImage();

			//画像を保存
			$this->_saveImage ();

		} catch (\Exception $e) {
			//メモリー解放
			if ($this->_out_image) imagedestroy ($this->_out_image);
			if ($this->_in_image)  imagedestroy ($this->_in_image);
			$this->_parm = array();
			$this->throwError($e->getMessage());
		}

		//メモリー解放
		if ($this->_out_image) imagedestroy ($this->_out_image);
		if ($this->_in_image)  imagedestroy ($this->_in_image);
		$this->_parm = array();

		//パーミッションの設定
		if ($permission) {
			if (false === chmod ($this->_out_name ,$permission)) {
				$this->throwError ('パーミッションの設定に失敗しました。');
			}
		}

		return true;
	}

	/**
	 * 画像をブラウザへ出力します。
	 *
	 * ヘッダをこのクラス内で出力して、表示させます。
	 *
	 * 画像の出力形式は、png,jpg,gifから選択してください．
	 * 設定がない場合は，ものと画像形式で出力します．
	 *
	 * @param string $image_type 出力する形式
	 * @param string $option 画像編集オプション
	 * @return 出力に成功した場合は、trueを返します。
	 * @example $Image->save ('/sky2.png');
	 */
	public function output ($image_type = null, $option = null)
	{

		try {
			//編集オプションの実行
			if ($option) $this->_doOption ($option);

			//出力形式
			if ($image_type) {
				$this->_parm['output_type'] = $image_type;
			} else {
				$this->_parm['output_type'] = substr(strrchr($this->_in_name, '.'), 1);
			}
			$target = ['jpg','png', 'gif'];
			if (!in_array($this->_parm['output_type'], $target)) throw new \Exception ('対応していない出力形式です。');

			//画像を編集
			$this->_editImage();

			//画像を保存
			$this->_outputImage ();

		} catch (\Exception $e) {
			//メモリー解放
			if ($this->_out_image) imagedestroy ($this->_out_image);
			if ($this->_in_image)  imagedestroy ($this->_in_image);
			$this->_parm = array();
			$this->throwError($e->getMessage());
		}

		//メモリー解放
		if ($this->_out_image) imagedestroy ($this->_out_image);
		if ($this->_in_image)  imagedestroy ($this->_in_image);
		$this->_parm = array();

		return true;
	}

	/**
	 * オプションパラメータの実行
	 */
	protected function _doOption ($option)
	{
		//オプション文字列の分解
		//ルールの分解
		$option_array = explode ('|', $option);
		foreach ($option_array as $unit) {
			$param = explode (':', $unit);
			//オプション名
			$option_name = $param[0];
			array_shift($param);
			//オプションのパラメータ
			$option_params = $param;

			//オプションの実行
			if (method_exists ($this, $option_name)) {
				call_user_func_array ([$this, $option_name], $option_params);
			} else {
				$this->throwError("編集オプション「{$option_name}」は存在しません。", true);
			}
		}
	}

	/**
	 * 画像の読み込み
	 *
	 * jepg,png,gifのみ読み込み可能です．
	 */
	private function _loadImage ()
	{
		//ファイル名の調整
		if (strpos($this->_in_name,'http') === 0) {
			if (false !== ($header = get_headers($this->_in_name, 1))) {
				if (stripos($header[0], 'OK') === false) {
					throw new \Exception ("指定されたURL「".h($this->_in_name)."」<br>は存在しません。");
				} elseif (stripos($header['Content-Type'], 'image') === false) {
					throw new \Exception ("指定されたURL「".h($this->_in_name)."」<br>は画像形式のコンテンツではありません。");
				}
			} else {
				throw new \Exception ('指定されたURLの画像ファイルが存在しません．');
			}
		} elseif (is_file($this->_in_name)) {

		} else {
			if (strpos($this->_in_name,'/') !== 0) {
				$this->_in_name = '/'.$this->_in_name;
			}
			$this->_in_name = $this->_rootDir.$this->_in_name;
		}

		//読み込む画像の確認
		if(!is_file($this->_in_name) and strpos($this->_in_name,'http') !== 0) {
			throw new \Exception ('画像ファイルが存在しません．');
		}

		//拡張子を判定
		$this->_parm['in_type'] = exif_imagetype($this->_in_name);


		// JPEG画像を読み込む
		if ($this->_parm['in_type'] == 2) {
			$this->_in_image = ImageCreateFromJPEG($this->_in_name);
		}
		//PNG画像を読み込む
		elseif ($this->_parm['in_type'] == 3) {
			$this->_in_image = ImageCreateFromPNG($this->_in_name);
		}
		//GIF画像を読み込む
		elseif ($this->_parm['in_type'] == 1) {
			$this->_in_image = ImageCreateFromGIF($this->_in_name);
		}
		//例外
		else {
			throw new \Exception ('対応していない読み込みファイルです．');
		}

		if (!$this->_in_image) {
			throw new \Exception ('画像ファイルが読み込めませんでした．');
		}

		//サイズの取得
		$this->_parm['ix'] = ImageSX($this->_in_image);	// 読み込んだ画像の横サイズを取得
		$this->_parm['iy'] = ImageSY($this->_in_image);	// 読み込んだ画像の縦サイズを取得
	}

	/**
	 * 画像を編集します
	 */
	protected function _editImage ()
	{
		//画像の読み込み
		$this->_loadImage();

		//初期の背景（白）
		if (!isset($this->_parm['bgColor']) or $this->_parm['bgColor'] !== true) {
			$this->bgColor (255, 255, 255);
		}

		//リサイズの設定
		$this->_doResize ();

		//画像サイズと空白の設定
		$new_size = $this->_doSize();

		//下地用画像の作成
		if (false === ($this->_out_image = ImageCreateTrueColor($new_size[0], $new_size[1]))) {
			throw new \Exception ('下地用画像画像が生成できませんでした．');
		}

		//背景を塗りつぶす
		//デフォルトは白
		if (!imagefill($this->_out_image, 0 ,0 ,$this->_parm['bgParet'])) {
			throw new \Exception ('下地用画像の背景色の塗りつぶしに失敗しました。');
		}

		//クロップ・トリミング
		$this->_doCropTrim ();

		//元の画像を貼り付ける
		if (!imagecopyresampled($this->_out_image, $this->_in_image, $this->_parm['spx'], $this->_parm['spy'], 0, 0, $this->_parm['ox'], $this->_parm['oy'], $this->_parm['ix'], $this->_parm['iy'])) {
			throw new \Exception ('編集元画像の貼付けに失敗しました。');
		}

		//フィルタをかける
		if (isset($this->_parm['filter'])) {
			foreach ($this->_parm['filter'] as $param) {
				//グレースケールで保存
				if ($param[0] == 'grayscale') {
					if (!imagefilter($this->_out_image, IMG_FILTER_GRAYSCALE)) {
						throw new \Exception ('編集元画像の貼付けに失敗しました。');
					}
				}

				//光度を変更
				elseif ($param[0] === 'brightness') {
					if (!imagefilter($this->_out_image, IMG_FILTER_BRIGHTNESS, $param[1])) {
						throw new \Exception ('画像の輝度の変更に失敗しました。');
					}
				}

				//コントラストを変更
				elseif ($param[0] === 'contrast') {
					if (!imagefilter($this->_out_image, IMG_FILTER_CONTRAST, $param[1])) {
						throw new \Exception ('画像のコントラストの変更に失敗しました。');
					}
				}

				//カラーバランスを変更
				elseif ($param[0] === 'colorBalance') {
					if (!imagefilter($this->_out_image, IMG_FILTER_COLORIZE, $param[1], $param[2], $param[3], $param[4])) {
						throw new \Exception ('画像のカラーバランスの変更に失敗しました。');
					}
				}

				//エッジを強調させる
				elseif ($param[0] === 'edge') {
					if (!imagefilter($this->_out_image, IMG_FILTER_EDGEDETECT)) {
						throw new \Exception ('画像のエッジをの強調に失敗しました。');
					}
				}

				//エンボス処理
				elseif ($param[0] === 'emboss') {
					if (!imagefilter($this->_out_image, IMG_FILTER_EMBOSS)) {
						throw new \Exception ('画像のエンボス処理に失敗しました。');
					}
				}

				//ガウスぼかしを使用します。
				elseif ($param[0] === 'gauss') {
					if (!imagefilter($this->_out_image, IMG_FILTER_GAUSSIAN_BLUR)) {
						throw new \Exception ('画像のガウスぼかしに失敗しました。');
					}
				}

				//ぼかしを使用します。
				elseif ($param[0] === 'blur') {
					if (!imagefilter($this->_out_image, IMG_FILTER_SELECTIVE_BLUR)) {
						throw new \Exception ('ぼかしに失敗しました。');
					}
				}

				//スケッチ風にします
				elseif ($param[0] === 'sketch') {
					if (!imagefilter($this->_out_image, IMG_FILTER_MEAN_REMOVAL)) {
						throw new \Exception ('画像のスケッチ風に失敗しました。');
					}
				}

				//滑らかにする
				elseif ($param[0] === 'smooth') {
					if (!imagefilter($this->_out_image, IMG_FILTER_SMOOTH, $param[1])) {
						throw new \Exception ('画像を滑らかにできませんでした。');
					}
				}

				//モザイクをかける
				elseif ($param[0] === 'mosaic') {
					if (!imagefilter($this->_out_image, IMG_FILTER_PIXELATE, $param[1], $param[2])) {
						throw new \Exception ('画像のモザイクに失敗しました。');
					}
				}

				//モザイクをかける
				elseif ($param[0] === 'round') {
					if (!$this->_roundImage($param[1], $param[2])) {
						throw new \Exception ('画像の各丸に失敗しました。');
					}
				}
			}
		}
	}

	/**
	 * 画像を保存します。
	 */
	protected function _saveImage ()
	{
		if (strtolower($this->_parm['output_type']) === 'jpg') {
			//画像品質初期値のセット
			if (empty($this->_parm['jpegQuality'])) $this->_parm['jpegQuality'] = 80;

			if (!ImageJPEG($this->_out_image, $this->_out_name, $this->_parm['jpegQuality'])) {
				throw new \Exception ('画像の生成に失敗しました。');
			}

		} elseif (strtolower($this->_parm['output_type']) === 'png') {
			if (!ImagePNG($this->_out_image, $this->_out_name)) {
				throw new \Exception ('画像の生成に失敗しました。');
			}

		} elseif (strtolower($this->_parm['output_type']) === 'gif') {
			if (!ImageGIF($this->_out_image, $this->_out_name)) {
				throw new \Exception ('画像の生成に失敗しました。');
			}
		} else {
			throw new \Exception ('対応していない保存形式です。');
		}
	}

	/**
	 * 画像を出力します。
	 */
	protected function _outputImage ()
	{
		if (strtolower($this->_parm['output_type']) === 'jpg') {
			//画像品質初期値のセット
			if (empty($this->_parm['jpegQuality'])) $this->_parm['jpegQuality'] = 80;
			header('Content-Type: image/jpeg');
			if (!ImageJPEG($this->_out_image, null, $this->_parm['jpegQuality'])) {
				throw new \Exception ('画像の出力に失敗しました。');
			}

		} elseif (strtolower($this->_parm['output_type']) === 'png') {
			header('Content-Type: image/png');
			if (!ImagePNG($this->_out_image)) {
				throw new \Exception ('画像の出力に失敗しました。');
			}

		} elseif (strtolower($this->_parm['output_type']) === 'gif') {
			header('Content-Type: image/gif');
			if (!ImageGIF($this->_out_image)) {
				throw new \Exception ('画像の出力に失敗しました。');
			}
		} else {
			throw new \Exception ('対応していない保存形式です。');
		}
	}

	/**
	 * 画像のクロップ・トリミングをします。
	 */
	protected function _doCropTrim ()
	{
		if (isset($this->_parm['cps_x']) or isset($this->_parm['tr_x'])) {
			//下地用画像の作成
			if (false === ($image = ImageCreateTrueColor($this->_parm['ix'], $this->_parm['iy']))) {
				throw new \Exception ('下地用画像画像が生成できませんでした。');
			}
			//コピー
			if (false === imagecopyresampled($image, $this->_in_image, 0, 0, 0, 0, $this->_parm['ix'], $this->_parm['iy'], $this->_parm['ix'], $this->_parm['iy'])) {
				throw new \Exception ('画像のコピーに失敗しました。');
			}
			//クロップ
			if (isset($this->_parm['cps_x'])) {
				//％指定
				if (strpos($this->_parm['cps_x'], '%') !== false) {
					$this->_parm['cps_x'] = $this->_parm['ix'] * (int)($this->_parm['cps_x'] / 100);
				}
				if (strpos($this->_parm['cps_y'], '%') !== false) {
					$this->_parm['cps_y'] = $this->_parm['iy'] * (int)($this->_parm['cps_y'] / 100);
				}
				if (strpos($this->_parm['cpe_x'], '%') !== false) {
					$this->_parm['cpe_x'] = $this->_parm['ix'] * (int)($this->_parm['cpe_x'] / 100);
				}
				if (strpos($this->_parm['cpe_y'], '%') !== false) {
					$this->_parm['cpe_y'] = $this->_parm['iy'] * (int)($this->_parm['cpe_y'] / 100);
				}

				//マイナス指定
				if ($this->_parm['cpe_x'] < 0) $this->_parm['cpe_x'] = $this->_parm['ix'] + $this->_parm['cpe_x'];
				if ($this->_parm['cpe_y'] < 0) $this->_parm['cpe_y'] = $this->_parm['iy'] + $this->_parm['cpe_y'];

				$trim_width  = $this->_parm['cpe_x'] - $this->_parm['cps_x'];
				$trim_height = $this->_parm['cpe_y'] - $this->_parm['cps_y'];
				if (!imagecopyresampled($this->_in_image, $image, 0, 0, $this->_parm['cps_x'], $this->_parm['cps_y'], $this->_parm['ix'], $this->_parm['iy'], $trim_width ,$trim_height)) {
					throw new \Exception ('編集元画像の貼付けに失敗しました。');
				}
			} elseif (isset($this->_parm['tr_x'])) {
				//％指定
				if (strpos($this->_parm['tr_x'], '%') !== false) {
					$this->_parm['tr_x'] = $this->_parm['ix'] * ((int)$this->_parm['tr_x'] / 100);
				}
				if (strpos($this->_parm['tr_y'], '%') !== false) {
					$this->_parm['tr_y'] = $this->_parm['iy'] * ((int)$this->_parm['tr_y'] / 100);
				}

				if (strtolower($this->_parm['tr_y']) === 'auto') {
					//正方形にトリミング
					if ($this->_parm['ix'] > $this->_parm['iy']) {
						if ($this->_parm['tr_x'] < $this->_parm['iy']) $length = $this->_parm['tr_x'];
						else $length = $this->_parm['iy'];
						$this->_parm['tr_x'] = $length;
						$this->_parm['tr_y'] = $length;
					} else {
						if ($this->_parm['tr_x'] < $this->_parm['ix']) $length = $this->_parm['tr_x'];
						else $length = $this->_parm['ix'];
						$this->_parm['tr_x'] = $length;
						$this->_parm['tr_y'] = $length;
					}
				}

				//x座標計算
				if (($this->_parm['ix'] - $this->_parm['tr_x']) > 0) {
					$x_point = ($this->_parm['ix'] - $this->_parm['tr_x']) / 2;
				} else {
					$x_point = 0;
				}
				//y座標計算
				if (($this->_parm['iy'] - $this->_parm['tr_y']) > 0) {
					$y_point = ($this->_parm['iy'] - $this->_parm['tr_y']) / 2;
				} else {
					$y_point = 0;
				}

				$trim_width  = $this->_parm['tr_x'];
				$trim_height = $this->_parm['tr_y'];

				if (!imagecopyresampled($this->_in_image, $image, 0, 0, (int)$x_point, (int)$y_point, $this->_parm['ix'], $this->_parm['iy'], (int)$trim_width ,(int)$trim_height)) {
					throw new \Exception ('編集元画像の貼付けに失敗しました。');
				}
			}
		}
	}


	/**
	 * 画像のリサイズをします。
	 */
	protected function _doResize ()
	{
		if (isset($this->_parm['size_x'])) {
			//％指定
			if (isset($this->_parm['size_x']) and strpos($this->_parm['size_x'], '%') !== false) {
				$this->_parm['size_x'] = $this->_parm['ix'] * ((int)$this->_parm['size_x'] / 100);
			}
			if (isset($this->_parm['size_y']) and strpos($this->_parm['size_y'], '%') !== false) {
				$this->_parm['size_y'] = $this->_parm['iy'] * ((int)$this->_parm['size_y'] / 100);
			}
			//強制アスペクト比維持
			if (!$this->_parm['size_x'] or !$this->_parm['size_y']) {
				$this->_parm['keepRatio'] = true;
				if (!$this->_parm['size_x']) $this->_parm['size_x'] = $this->_parm['ix'];
				if (!$this->_parm['size_y']) $this->_parm['size_y'] = $this->_parm['iy'];
			}
		}

		//アスペクト比を維持する場合
		if (isset($this->_parm['keepRatio']) and $this->_parm['keepRatio'] === true) {
			$width = (int)(($this->_parm['size_y'] * $this->_parm['ix']) / $this->_parm['iy']);
			$height = (int)(($this->_parm['size_x'] * $this->_parm['iy']) / $this->_parm['ix']);

			if ($width < $this->_parm['size_x']) {
				$new_width = $width;
				$new_height = $this->_parm['size_y'];
			} else {
				$new_width = $this->_parm['size_x'];
				$new_height = $height;
			}
		} else {
			//最大縦サイズの適用
			if (isset($this->_parm['max_y']) and is_numeric($this->_parm['max_y']) and $this->_parm['max_y'] > 0) {
				if ($this->_parm['max_y'] < $this->_parm['iy']) {
					$new_height = $this->_parm['max_y'];
				} else {
					$new_height = $this->_parm['iy'];
				}
			} else {
				//元の画像の高さ
				$new_height = $this->_parm['iy'];
			}
			//最大横サイズの適用
			if (isset($this->_parm['max_x']) and is_numeric($this->_parm['max_x']) and $this->_parm['max_x'] > 0) {
				if ($this->_parm['max_x'] < $this->_parm['ix']) {
					$new_width = $this->_parm['max_x'];
				} else {
					$new_width = $this->_parm['ix'];
				}
			} else {
				//元の画像の幅
				$new_width = $this->_parm['ix'];
			}
		}
		$this->_parm['ox'] = $new_width;
		$this->_parm['oy'] = $new_height;
	}

	/**
	 * 画像サイズと空白の設定
	 */
	protected function _doSize () {
		//設定サイズの維持
		if (isset($this->_parm['keepSize']) and $this->_parm['keepSize']) {
			//各空白部
			$this->_parm['spx'] = (int)(($this->_parm['size_x'] - $this->_parm['ox'])/2);
			$this->_parm['spy'] = (int)(($this->_parm['size_y'] - $this->_parm['oy'])/2);

			$this->_parm['ox'] = $this->_parm['size_x'] - ($this->_parm['size_x'] - $this->_parm['ox']);
			$this->_parm['oy'] = $this->_parm['size_y'] - ($this->_parm['size_y'] - $this->_parm['oy']);
			$this->_parm['ix'] = $this->_parm['ix'];
			$this->_parm['iy'] = $this->_parm['iy'];

			return [$this->_parm['size_x'],$this->_parm['size_y']];
		}
		//各空白部
		$this->_parm['spx'] = 0;
		$this->_parm['spy'] = 0;

		return [$this->_parm['ox'],$this->_parm['oy']];
	}

	/**
	 * 画像を角丸にする
	 *
	 * @param numeric $size  角丸の半径(px)
	 * @param numeric $level 角丸の倍率
	 */
	protected function _roundImage ($pixcel, $level)
	{
		try {
			$fix = $level;
			$pixcel *= $fix;

			//下地用画像の作成
			if (false === ($image = ImageCreateTrueColor($pixcel * 2, $pixcel * 2))) {
				throw new \Exception ('下地用画像画像が生成できませんでした．');
			}

			//アンチエイリアス
			imageantialias($image ,true);

			//背景を塗りつぶす
			//デフォルトは白
			if (!imagefill($image, 0 ,0 ,$this->_parm['bgParet'])) {
				throw new \Exception ('下地用画像の背景色の塗りつぶしに失敗しました。');
			}

			//背景色を反転させる
			if (!ImageFilter($image, IMG_FILTER_NEGATE)) throw new \Exception ('背景色の反転に失敗しました。');

			//円作成
			if (!imagefilledellipse($image, $pixcel ,$pixcel ,$pixcel * 2,$pixcel * 2,$this->_parm['bgParet'])) {
				throw new \Exception ('下地用画像の背景色の塗りつぶしに失敗しました。');
			}

			//もう一回背景色を反転させる
			ImageFilter($this->_out_image, IMG_FILTER_NEGATE);

			//透過色設定
			imagecolortransparent($image,$this->_parm['bgParet']);

			$spx = $this->_parm['spx'] * $fix;
			$spy = $this->_parm['spy'] * $fix;

			$all_height = $this->_parm['oy'] + $this->_parm['spy'] * 2;
			$all_width = $this->_parm['ox'] + $this->_parm['spx'] * 2;

			$fix_height = $all_height * $fix;
			$fix_width = $all_width * $fix;

			//倍率がかかっている場合
			if ($level != 1) {
				//拡大画像作成
				if (false === ($out_image = ImageCreateTrueColor($fix_width, $fix_height))) {
					throw new \Exception ('拡大用の下地画像が生成できませんでした．');
				}
				imagecopyresampled($out_image, $this->_out_image, 0 ,0 ,0 ,0 , $fix_width, $fix_height, $all_width, $all_height);

				imagecopymerge($out_image, $image, $spx, $spy, 0, 0, $pixcel ,$pixcel, 100);
				//右上
				imagecopymerge($out_image, $image, $spx + $this->_parm['ox'] * $fix - $pixcel, $spy, $pixcel, 0, $pixcel, $pixcel, 100);
				//左下
				imagecopymerge($out_image, $image, $spx, $this->_parm['oy'] * $fix - $pixcel + $spy, 0, $pixcel, $pixcel, $pixcel, 100);
				//右下
				imagecopymerge($out_image, $image, $spx + $this->_parm['ox'] * $fix - $pixcel , $this->_parm['oy'] * $fix - $pixcel + $spy, $pixcel, $pixcel, $pixcel, $pixcel, 100);

				//縮小
				imagecopyresampled($this->_out_image, $out_image, 0 ,0 ,0 ,0 , $all_width, $all_height, $fix_width, $fix_height);
			} else {
				//左上
				imagecopymerge($this->_out_image, $image, $spx, $spy, 0, 0, $pixcel, $pixcel, 100);
				//右上
				imagecopymerge($this->_out_image, $image, $spx + $this->_parm['ox'] - $pixcel, $spy, $pixcel, 0, $pixcel,$pixcel, 100);
				//左下
				imagecopymerge($this->_out_image, $image, $spx, $this->_parm['oy'] - $pixcel + $spy, 0, $pixcel, $pixcel, $pixcel, 100);
				//右下
				imagecopymerge($this->_out_image, $image, $spx + $this->_parm['ox'] - $pixcel, $this->_parm['oy'] - $pixcel + $spy, $pixcel, $pixcel, $pixcel, $pixcel, 100);
			}

			//もう一回背景色を反転させる
			ImageFilter($this->_out_image, IMG_FILTER_NEGATE);

		} catch (\Exception $e) {
			//メモリー解放
			if (isset($image)) imagedestroy ($image);
			if (isset($out_image)) imagedestroy ($out_image);
			$this->throwErrro($e->getMessage());
		}
		//メモリー解放
		if (isset($image)) imagedestroy ($image);
		if (isset($out_image)) imagedestroy ($out_image);
		return true;
	}
}