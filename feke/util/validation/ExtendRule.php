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

namespace feke\util\validation;

/**
 * ユーザー専用のバリデーションルール拡張用トレイトです。
 *
 * このファイルを、/vender/feke/util/validation設置し、作成したいルール名と同名の静的メソッドを設置するだけでルールの拡張ができます。
 * ルール用のメソッドは、返り値はtrue,falseのみ、引数には$text(検証値)、配列の$option(検証のオプション)の存在が必須です。
 *
 * {{{php|
 * //新ルールの作成例
 * public sutatic like_banana ($text, $option)
 * {
 *     if (isset($option[1])) {
 *         if ($option[1] === 'バナナ大好き') return true;
 *     }
 *     return false;
 * }
 *
 * //ルールを指定するときは、他のデフォルトのルールの指定時と同じです。
 * 'rule' => 'like_banana'
 * }}}
 * @package     Feke
 * @subpackage  util.validation
 */

trait ExtendRule
{
}