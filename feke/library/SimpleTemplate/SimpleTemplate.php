<?php
/***********************************************************************
* SimpleTemplate.class.php
*
* PHP versions	5.x.x
*
* @class		SimpleTemplate
* @author		Shigeru Kuratani <kuratani@benefiss.com>
* @copyright	2012, Shigeru Kuratani <Kuratani@benefiss.com>
* @license		The BSD License
* @version		1.1.0
* @link			http://st.benefiss.com
* @since		File available since Release 1.0.8
* @disclaimer	THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*				【邦訳】
*				本ソフトウェアは、著作権者およびコントリビューターによって「現状のまま」提供されており、明示黙示を問わず、商業的な使用可能性、および特定の目的に対する適合性に関する暗黙の保証も含め、またそれに限定されない、いかなる保証もありません。著作権者もコントリビューターも、事由のいかんを問わず、 損害発生の原因いかんを問わず、かつ責任の根拠が契約であるか厳格責任であるか（過失その他の）不法行為であるかを問わず、仮にそのような損害が発生する可能性を知らされていたとしても、本ソフトウェアの使用によって発生した（代替品または代用サービスの調達、使用の喪失、データの喪失、利益の喪失、業務の中断も含め、またそれに限定されない）直接損害、間接損害、偶発的な損害、特別損害、懲罰的損害、または結果損害について、一切責任を負わないものとします。
************************************************************************

************************************************************************
*変更箇所
*(0)一部パスを直接割り当て
*(1)使用しない機能(if,foreach等)メソット削除
*(2)includeのファイル名に変数の使用を許可
*(3)テンプレート変数割り当てに配列を使用可能
*(4)上記の変更に伴い処理構文のパターン定義を一部変更
*(5)テンプレート変数の割り当て'assign()'のコピー 'set()'を実装
*(6)変数の割り当ての際にテンプレート変数の先頭に'++'をつけた場合，
*   前方に値を追加
*   後方につけた場合は後方に値を追加
*   するように変更
************************************************************************/

class SimpleTemplate
{
	/*
	 * 処理構文のパターン定義
	 */
	// インクルード処理
	const PATTERN_INCLUDE = '/{include file=[\'|"]([\w\/.-_?&=:]+)[\'|"]}/';

	// コメント処理
	const PATTERN_COMMENT = '/{\*([\s\S]*)\*}/U';
		
	// 変数の設定と展開(assign)
	const PATTERN_ASSIGN = '/{set var=[\'|"]([\w.-_?&=]+)[\'|"] value=[\'|"](.*)[\'|"]}/U';
	
	// 変数の設定と展開(capture)
	const PATTERN_CAPTURE = '/{capture name=[\'|"]([\w.-_?&=]+)[\'|"]}([\s\S]*){\/capture}/U';
	
	// テンプレート変数の展開 
	const PATTERN_VARIABLE = '/{(\$[\w.-_?&=>]+)}/U';

	// アサインされていないテンプレート変数の処理
	const PATTERN_NOASSIGN = '/{\$([\w.-_?&=>]+)}/U';
	
	
	/**
	 * テンプレートディレクトリ
	 * @var string
	 */
	private $_template_dir;
	
	/**
	 * キャッシュディレクトリ
	 * @var string
	 */
	private $_cacheDir;
	
	/**
	 * キャッシュ機構を使用するかのフラグ
	 * @var boolean
	 */
	private $_use_cache;
	
	
	/**
	 * 変数割り当て配列
	 * @var array
	 * 	array[variable_name] = value
	 */
	private $_array_variable;
	
	/**
	 * テンプレート記述エンコーディング
	 * 出力エンコーディングとテンプレート記述エンコーディングが異なる場合の文字コード変換（displayメソッド内）にて使用
	 * @var string 文字エンコーディング
	 */
	private $_tplEncoding;
	
	/**
	 * 出力エンコーディング
	 * @var string　出力文字エンコーディング
	 */
	private $_outputEncoding;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		$this->_template_dir   = '.';     // デフォルトテンプレートディレクトリ
		$this->_cacheDir      = '.';     // デフォルトキャッシュディレクトリ
		$this->_use_cache      = false;   // デフォルトキャッシュ機構使用フラグ
		$this->_array_variable = array(); // デフォルト変数割り当て配列
		$this->_outputEncoding = 'UTF-8'; // デフォルト出力エンコーディング
		$this->_tplEncoding    = 'UTF-8'; // デフォルトテンプレート記述エンコーディング
	}
	
	/**
	 * デストラクタ
	 */
	function __destruct(){}
	
	
	/**
	 * テンプレートディレクトリ設定メソッド
	 *
	 * @access public
	 *
	 * @param string $path テンプレートディレクトリパス
	 * @return boolean
	 * 	       true  テンプレートディレクトリが存在し、テンプレートディレクトリプロパティ―への設定が完了した場合
	 * 	       false テンプレートディレクトリが存在しない場合
	 */
	public function template_dir($path)
	{
		$path = rtrim($path, '/');
		if(is_dir($path)) {
			$this->_template_dir = $path;
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * キャッシュディレクトリ設定メソッド
	 *
	 * @access public
	 *
	 * @param string $path キャッシュディレクトリパス
	 * @return boolean
	 * 	       true  キャッシュディレクトリが存在し、キャッシュディレクトリプロパティ―への設定が完了した場合
	 * 	       false キャッシュディレクトリが存在しない場合
	 */
	public function cacheDir($path)
	{
		$path = rtrim($path, '/');
		if(is_dir($path)) {
			$this->_cacheDir = $path;
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * キャッシュ機構の有効・無効を設定する
	 *
	 * @param unknown_type $use_cache_flag
	 * @return boolean
	 *         true  キャッシュ機構を有効に設定
	 *         false キャッシュ機構の有効化に失敗・キャッシュ機構の無効化に成功
	 */
	public function useCacheSystem($use_cache_flag)
	{
		if(is_bool($use_cache_flag) || is_int($use_cache_flag) || is_string($use_cache_flag)) {
			
			if ($use_cache_flag) {
				$this->_use_cache = true;
				return true;
			} else {
				$this->_use_cache = false;
				return false;
			}
			
		}else{
			
			return false;
			
		}
	}
	
	/**
	 * テンプレート記述エンコーディング指定メソッド
	 *
	 * @access public
	 *
	 * @param string $tplEncording テンプレート記述エンコーディング
	 * @return boolean
	 *         true  文字エンコーディングの設定が成功
	 *         false 文字エンコーディングの設定が失敗（mb_stringにてサポートされているエンコーディング以外が指定された場合）
	 */
	public function setTplEncoding($tplEncoding)
	{
		$mb_suported_encoding = mb_list_encodings();
		if (array_search($tplEncoding, $mb_suported_encoding) !== false) {
			$this->_tplEncoding = $tplEncoding;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 出力エンコーディング設定メソッド
	 *
	 * @access public
	 *
	 * @param string $outputEncording 出力エンコーディング
	 * @return boolean
	 *         true  文字エンコーディングの設定が成功
	 *         false 文字エンコーディングの設定が失敗（mb_stringにてサポートされているエンコーディング以外が指定された場合）
	 */
	public function setOutputEncoding($outputEncoding)
	{
		$mb_suported_encoding = mb_list_encodings();
		if (array_search($outputEncoding, $mb_suported_encoding) !== false) {
			$this->_outputEncoding = $outputEncoding;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 変数割り当てメソッド
	 *
	 * @access public
	 *
	 * @param string $variable 変数名
	 * @param mixed $value 変数値
	 * @return boolean
	 * 	       true   割り当て成功
	 * 	       false 割り当て失敗
	 */
	public function assign($variable, $value)
	{
		if(is_string($variable)) {
			//文字列の頭が"++"なら前に追加
			if (preg_match('/^\+\+/', $variable)) {
				$variable = preg_replace('/^\+\+/', '' ,$variable);
				$this->_array_variable[$variable] = $value.$this->_array_variable[$variable];
			//文字列の後ろが"++"なら後ろに追加
			}else if (preg_match("/\+\+$/", $variable)) { 
				$variable = preg_replace('/\+\+$/', '' , $variable);
				$this->_array_variable[$variable] .= $value;
			//その他は上書き
			}else {
				$this->_array_variable[$variable] = $value;
			}
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * インクルードファイルの読み込みと展開
	 *
	 * @access private
	 *
	 * @param  string テンプレートファイルコンテンツ（文字列）
	 * @return string インクルードファイル展開済みの文字列
	 */
	private function _expandIncludeFile($template_string)
	{
		while($match_count = preg_match_all(self::PATTERN_INCLUDE, $template_string, $matches, PREG_PATTERN_ORDER)) {
			for($i =0; $i < $match_count; $i++) {
				$include_string = file_get_contents(TMP_PATH."/tmp/" . $matches[1][$i]);
				$template_string = str_replace($matches[0][$i], $include_string, $template_string);
			}
		}
		return $template_string;
	}
	/**
	 * インクルードファイルの読み込みと展開2
	 *
	 * @access private
	 *
	 * @param  string テンプレートファイルコンテンツ（文字列）
	 * @return string インクルードファイル展開済みの文字列
	 *追加部分
	 */
	private function _expandIncludeFile2($template_string)
	{
		while($match_count = preg_match_all(self::PATTERN_INCLUDE, $template_string, $matches, PREG_PATTERN_ORDER)) {
			for($i =0; $i < $match_count; $i++) {
				$include_string = file_get_contents( TMP_PATH.$matches[1][$i]);
				$template_string = str_replace($matches[0][$i], $include_string, $template_string);
			}
		}
		return $template_string;
	}

	/**
	 * テンプレート変数の展開
	 *
	 * @access private
	 *
	 * @param  string テンプレートファイルコンテンツ（文字列）
	 * @return string テンプレート変数展開済みの文字列
	 */
	private function _expandVariable($template_string)
	{
		$cnt = 0;
		foreach($this->_array_variable as $key => $value) {
			//配列挿入用（改造部分）
			if (is_array($value)){
				$key1_name = $key;
				foreach ($value as $key => $value){
					//配列用
					$key = "{$key1_name}.{$key}";
					$pattern_variable = '/{(\$' . $key . '[\w.-_?&=>]*)}/U';
					if($match_count = preg_match_all($pattern_variable, $template_string, $matches, PREG_PATTERN_ORDER)) {
						for($i =0; $i < $match_count; $i++) {
							$search = '$' . $key;
							$pos = strpos($matches[1][$i], $search);
							$variable_length = strlen($search);
							$variable_finish_pos = $pos + $variable_length;
							// 変換するテンプレート変数（文字列）が終了したかの判定 ※$value1と$value10を区別するため
							if($variable_length == strlen($matches[1][$i])) {
								$variable_finish = true;
							}else{
								$variable_finish = preg_match('/[^\d\w]+/', $matches[1][$i][$variable_finish_pos]);
							}
							// ifステートメント内にアサインしたテンプレート変数が存在し、かつ、テンプレート変数（文字列）の終了判定が真の場合の処理
							if($pos !== false && $variable_finish) {
								if(is_array($value) || is_object($value) || is_resource($value)) {
									$template_string = str_replace($matches[0][$i], '', $template_string);
								}else{
									$template_string = str_replace($matches[0][$i], $value, $template_string);
								}
							}
						}
					}
				}
			} else {
				//通常変数用
				$pattern_variable = '/{(\$' . $key . '[\w.-_?&=>]*)}/U';
				if($match_count = preg_match_all($pattern_variable, $template_string, $matches, PREG_PATTERN_ORDER)) {
					for($i =0; $i < $match_count; $i++) {
						$search = '$' . $key;
						$pos = strpos($matches[1][$i], $search);
						$variable_length = strlen($search);
						$variable_finish_pos = $pos + $variable_length;
						// 変換するテンプレート変数（文字列）が終了したかの判定 ※$value1と$value10を区別するため
						if($variable_length == strlen($matches[1][$i])) {
							$variable_finish = true;
						}else{
							$variable_finish = preg_match('/[^\d\w]+/', $matches[1][$i][$variable_finish_pos]);
						}
						// ifステートメント内にアサインしたテンプレート変数が存在し、かつ、テンプレート変数（文字列）の終了判定が真の場合の処理
						if($pos !== false && $variable_finish) {
							if(is_array($value) || is_object($value) || is_resource($value)) {
								$template_string = str_replace($matches[0][$i], '', $template_string);
							}else{
								$template_string = str_replace($matches[0][$i], $value, $template_string);
							}
						}
					}
				}
			}
		}
		return $template_string;
	}
	

	
	/**
	 * アサインされていないテンプレート変数の処理
	 *
	 * @access private
	 *
	 * @param  string テンプレートファイルコンテンツ（文字列）
	 * @return string テンプレート変数展開済みの文字列
	 */
	private function _processNoAssignVariable($template_string)
	{
		if($match_count = preg_match_all(self::PATTERN_NOASSIGN, $template_string, $matches, PREG_PATTERN_ORDER)) {
			for($i =0; $i < $match_count; $i++) {
				$template_string = str_replace($matches[0][$i], '', $template_string);
			}
		}
		return $template_string;
	}
	
	/**
	 * テンプレートファイル変換処理全プロセス　※出力処理は除く
	 * 
	 * @access private
	 * 
	 * @param  string $file テンプレートファイル
	 * @return string $template_string 全変換プロセス終了後のHTML
	 */
	private function _doAllProcess($tpl_file)
	{
	
		// テンプレートファイルの読み込み
		$template_string = file_get_contents($tpl_file);
		
		// インクルードファイルの読み込みと展開
		$template_string = $this->_expandIncludeFile($template_string);
		
		// テンプレート変数の展開
		$template_string = $this->_expandVariable($template_string);
		
		// アサインされていないテンプレート変数の処理
		$template_string = $this->_processNoAssignVariable($template_string);
		
		
		// インクルードファイルの読み込みと展開
		$template_string = $this->_expandIncludeFile2($template_string);
		
		// テンプレート変数の展開
		$template_string = $this->_expandVariable($template_string);
		
		// アサインされていないテンプレート変数の処理
		$template_string = $this->_processNoAssignVariable($template_string);
		
		// 出力エンコーディングへの文字コード変換
		//すべてUTF8統一のため廃止
		//$template_string = mb_convert_encoding($template_string, $this->_outputEncoding, $this->_tplEncoding);
		
		return $template_string;
	}
	
	/**
	 * テンプレートファイルとキャッシュファイルの更新日時を比較する
	 * 
	 * @access private
	 * 
	 * @param  string $template_file テンプレートファイル
	 * @param  string $cache_file　キャッシュファイル
	 * @return boolean
	 * 		   true   キャッシュファイル作成後のテンプレートファイルが修正されいる
	 * 		   false  キャッシュファイル作成後のテンプレートファイルが修正されいない
	 */
	private function _isPostModifiedTemplateFile($template_file, $cache_file)
	{
		clearstatcache(); // ファイルステータスのキャッシュをクリア
		
		$template_file_array[] = $template_file;
		$template_string = file_get_contents($template_file);
		while($match_count = preg_match_all(self::PATTERN_INCLUDE, $template_string, $matches, PREG_PATTERN_ORDER)) {
			for($i =0; $i < $match_count; $i++) {
				$template_file_array[] = $this->_template_dir . '/' . $matches[1][$i];
				$include_string = file_get_contents($this->_template_dir . '/' . $matches[1][$i]);
				$template_string = str_replace($matches[0][$i], $include_string, $template_string);
			}
		}
		
		$template_timestamp = filemtime($template_file_array[0]);
		foreach ($template_file_array as $template_file) {
			if ($template_timestamp < filemtime($template_file)) {
				$template_timestamp = filemtime($template_file);
			}
		}
		
		$cache_timestamp = filemtime($cache_file);
		
		return ($template_timestamp > $cache_timestamp) ? true : false;
	}
	
	/**
	 * キャッシュファイルを作成する
	 * 
	 * @access private
	 * 
	 * @param  string $cache_file キャッシュファイル
	 * @param  string $template_string 書き込み文字列
	 * @return void
	 */
	private function _makeCacheFile($cache_file, $template_string)
	{
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}
		
		$variable_string = $this->_makeVariableString();
		$template_string = $variable_string . $template_string;
		file_put_contents($cache_file, $template_string);
	}
	
	/**
	 * アサイン配列をprint_rした文字列を生成し返却する
	 * 
	 * @param  void
	 * @return string $returnString アサイン変数をprint_rした文字列
	 */
	private function _makeVariableString()
	{
		$return_string = '#';
		$return_string .= print_r($this->_array_variable, true);
		$return_string .= '#';
		
		return $return_string;
	}
	
	/**
	 * キャッシュファイルに記録されたアサイン変数と現在のアサイン変数が違うかを確認する
	 * 
	 * @param  string $cache_file キャッシュファイル
	 * @param  string $template_file テンプレートファイル
	 * @return boolean true  キャッシュファイルに記録されたアサイン変数と現在のアサイン配列が違う
	 * 				   false キャッシュファイルに記録されたアサイン変数と現在のアサイン配列が同じ
	 */
	private function _isDifferentVariables($template_file, $cache_file)
	{
		$cache_string = file_get_contents($cache_file);
		
		$pattern = '/^#[\s\S]*#/U';
		preg_match($pattern, $cache_string, $matches);
		$cache_variable_string = $matches[0];
		
		$this->_doAllProcess($template_file);
		$now_variable_string = $this->_makeVariableString();
		return strcmp($cache_variable_string, $now_variable_string) !== 0 ? true : false;
	}
	
	/**
	 * キャッシュファイルからテンプレート文字列を抽出する
	 * 
	 * @param  string $cache_file キャッシュファイル
	 * @return string $template_string キャッシュファイルから抽出したテンプレート文字列
	 */
	private function _extractTemplateString($cache_file)
	{
		
		$cache_string = file_get_contents($cache_file);
		
		$pattern = '/^(#[\s\S]*#)([\s\S]*)$/U';
		preg_match($pattern, $cache_string, $matches);
		$template_string = $matches[2];
		
		return $template_string;
	}
	
	/**
	 * テンプレート表示メソッド（ディスプレイ）
	 *
	 * @access public
	 * 
	 * @param  string $template テンプレートファイル
	 * @return void
	 */
	public function display($template)
	{
		/*
		 * テンプレートファイルが存在する場合にテンプレート変数の置換と出力を行う
		 */
		 /* ファイルパス廃止
		$template_path = $this->_template_dir . '/';
		$template_file = $template_path . $template;
		*/
		
		$template_file =  TMP_PATH.$template;
		
		preg_match('/^([\w.-_?&=]+)\.[a-z]+$/', $template, $matches);
		$cache_file_base = $matches[1];
		$cache_path = $this->_cacheDir . '/';
		$cache_file = $cache_path . $cache_file_base . '.cch';
		
		if ($this->_use_cache) {
			if (file_exists($template_file)) {
				if (file_exists($cache_file)) {
				 	// キャッシュファイルとテンプレートファイルの更新日時比較
					if ($this->_isPostModifiedTemplateFile($template_file, $cache_file)) {
						$template_string = $this->_doAllProcess($template_file);
						$this->_makeCacheFile($cache_file, $template_string);
					} else {
						if ($this->_isDifferentVariables($template_file, $cache_file)) {
							$template_string = $this->_doAllProcess($template_file);
							$this->_makeCacheFile($cache_file, $template_string);
						} else {
							$template_string = $this->_extractTemplateString($cache_file);
						}
					}
				} else {
					$template_string = $this->_doAllProcess($template_file);
					$this->_makeCacheFile($cache_file, $template_string);
				}
			} else {
				if (file_exists($cache_file)) {
					$template_string = file_get_contents($cache_file);
				}
			}
		} else {
			$template_string = $this->_doAllProcess($template_file);
		}
		
		// 出力
		echo $template_string;
	}
}
?>