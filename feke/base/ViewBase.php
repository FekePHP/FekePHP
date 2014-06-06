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

namespace feke\base;

use \Feke as Feke;
use feke\config\Config as C;
use feke\config\coreConfig as CC;

/**
 * view base
 *
 * @package    Feke
 * @subpackage base
 */
class ViewBase
{
	/**
	 * pluginの読み込み
	 */
	use \plugin\Plugin;

	use \plugin\View;

}


