<?php
namespace feke\util;

use feke\config\Config as C;
use feke\config\coreConfig as CC;

class Make
{
	//汎用変数
	public $make_type;
	
	public function __construct()
	{
	}
	
//パン屑リスト作成
	public function make_panList ($list,$url)
	{
		if (isset($list)) {
			//配列をソート
			ksort($list->id);
			
			//ノーマルタイプ(リンク付)
			if (empty ($this->make_type)) {
				$pan .= "<a href='{$this->url}'> ホーム </a>";
				foreach ( $list->id as $n => $id) {
					$pan .=  " &gt;";
					$pan .= "<a href='{$url}{$id}/'> {$list->name[$n]} </a>";
				}
			}
			
			else if ($this->make_type == 1) {
				foreach ( $list->id as $n => $id) {
					if ($pan) $pan .= "&gt;";
					$pan .=  " {$list->name[$n]} ";
				}
			}
		}
		return $pan;
	}


//サイドバーメニュ作成
	public function make_sideList ($list,$active)
	{
		$url = $this->url;;
		//配列をソート
		ksort($list->id);
		
		foreach ($list->id as $id) {
			$style = "";
				if ($active == $id) {
					$style .= " active";
				}if (!empty($list->level[$id])) {
					$style .= " level{$list->level[$id]}";
				}if (!empty($style)) {
					$style = " class='{$style}'";
				}
				if ($list->level[$id] == 1) {
					$side_list .= "\n<dt{$style}><a href='{$url}/cate/cid_{$id}/'>{$list->name[$id]}</a></dt>";
				}else{
					$side_list .= "\n<dd{$style}><a href='{$url}/cate/cid_{$id}/'>{$list->name[$id]}</a></dd>";
				}
		}
		return $side_list;
	}
	
	//カテゴリーツリー作成
	public function make_treeList ($list,$active,$type)
	{
		$old_level = 1;
		
		//配列をソート
		ksort($list->id);
		
		foreach ($list->id as $id) {
			$side_list .= "\n";
			$level = $list->level[$id];
			
			//カテゴリーリスト（パン屑タイプ作成用変数）
			$cate_array[$level] = $list->name[$id];
			for ($i=0; $i<$old_level - $level;$i++) {
				$cate_array[$old_level - $i] = "";
			}
			if ($type == 'select'){
				$js_action = " onclick='Category(\"{$id}\");return false;'";
				$style_id = " id='cid_{$id}'";
				$cate_list = "";
				foreach ($cate_array as $n =>$value) {
					if ($value) {
						if ($n != 1) $cate_list .= "＞";
						$cate_list .= "{$value}";
					}
				}
				$link = "<a href='#' {$js_action}{$style_id} title='{$cate_list}'>{$list->name[$id]}</a>";
			}
			
			else $link = "<a href='{$type}/item/cid_{$id}/'>{$level} {$list->name[$id]}</a>";
			
			
			if ($level < $old_level) {
				for ($i=0; $i<$old_level - $level;$i++) {
					$side_list .= "\n</li></ul>";
				}
				$side_list .= "<li>{$link}";
				$old_level = $level;
				continue;
			}
			
			if ($level == $old_level) {
				$side_list .= "</li><li>{$link}";
				$old_level = $level;
				continue;
			}
			
			if ($level > $old_level) {
				$side_list .= "<ul><li>{$link}";
			}
			
			$old_level = $level;
		}
		return "<ul id='cate_select'>".$side_list."</ul>";
	}
	
//テーブル作成
	public function make_table ($list,$que)
	{
		return $table;
	}
	
//アラート作成
	public function make_alert ($ms,$type)
	{
		if ($type == 'info') $style = "blue";
		if ($type == 'error') $style = "red";
		
		$alert = "<div class='alert {$style}'>{$ms}</div>";
		return $alert;
	}
	
//エラーアラート作成
	public function make_errorAlert($list)
	{
		foreach ($list as $ms) {
			$alert .= "<p>{$ms}</p>"; 
		}
		$alert = $this->make_alert($alert,error);
		return $alert;
	}
	
//画像取得
//画像存在確認(画像存在確認2の階層違いバン)
	public function foto_check ($que) {
		/*$que = array 
					(
						"id" => "$order_item[$i]",
						"size" => "S",
						"max_height" => "",
						"max_width" => "80",
						"height" => "",
						"width" => "",
						"float" => "",
						"src" => "",
						"num" => "",
						"class" => ""
					);
		*/
		
		if (!$que[src]) $que[src] = WEB_PATH."/img/item/";
		if (!$que[num]) $que[num] = "1";
		
		$file_name = $que[src].sprintf("%06d",$que[id])."_{$que[num]}_{$que[size]}.jpg";

		if ($que[max_height]) $style .= "max-height:{$que[max_height]}px;";
		if ($que[max_width]) $style .= "max-width:{$que[max_width]}px;";
		if ($que[height]) $style .= "height:{$que[height]};";
		if ($que[width]) $style .= "width:{$que[width]};";
		if ($que[float]) $style .= "float:{$que[float]};";
		if ($style) $style = " style=\"{$style}\"";
		
		if ($que['class']) $class = " class='{$que['class']}'";
		
		
		if (file_exists($file_name)) {
			if ($que[check]) return 1;
			else return "<img src=\"".C::MAIN_URL."/img/item/{$que[id]}_{$que[num]}_{$que[size]}.jpg\" {$class} {$style} title=\"{$que[id]}\">";
		}else {
			if ($que[check]) return -1;
			else return "<img src=\"".C::MAIN_URL."/img/item/no_image_{$que[size]}.jpg\" {$class} {$style} title=\"\">";
		}
	}
}