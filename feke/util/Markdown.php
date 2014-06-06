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
 * Markdown記法をサポートするクラスです。
 *
 *
 * @package    Feke
 * @subpackage util
 */
class Markdown
{
	public static function run ($text)
	{
		\Feke::loadLibrary('MarkdownExtra_Parser', '/markdown/markdown.php');
		return markdown($text);
	}
}
