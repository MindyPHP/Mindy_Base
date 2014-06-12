<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 10/06/14.06.2014 19:31
 */

namespace Mindy\Locale;

/**
 * CGettextFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
use Mindy\Base\Component;

/**
 * CGettextFile is the base class for representing a Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n.gettext
 * @since 1.0
 */
abstract class GettextFile extends Component
{
    /**
     * Loads messages from a file.
     * @param string $file file path
     * @param string $context message context
     * @return array message translations (source message => translated message)
     */
    abstract public function load($file, $context);

    /**
     * Saves messages to a file.
     * @param string $file file path
     * @param array $messages message translations (message id => translated message).
     * Note if the message has a context, the message id must be prefixed with
     * the context with chr(4) as the separator.
     */
    abstract public function save($file, $messages);
}
