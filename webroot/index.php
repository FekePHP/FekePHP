<?php
/**
 * Part of the Feke framework.
 *
 * @package Feke
 * @author Shohei Miyazawa
 * @since PHP 5.4
 * @license GNU General Public License (GPL)
 */

// システムのルートディレクトリパス
define(FEKE_ROOT_PATH, realpath(dirname(__FILE__) . '/..'));

//アプリケーションディレクトリパス
define(FEKE_APP_NAME, 'app');

//こののファイルのパス
define(FEKE_INDEX_PATH, realpath(dirname(__FILE__)));


require_once FEKE_ROOT_PATH.'/feke/core/Dispatcher.php';

// リクエスト処理
$dispatcher = new feke\core\Dispatcher();
$dispatcher->dispatch();