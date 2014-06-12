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
 * @date 10/06/14.06.2014 18:48
 */

namespace Mindy\Locale;

use Mindy\Base\Event;


/**
 * CMissingTranslationEvent represents the parameter for the {@link CMessageSource::onMissingTranslation onMissingTranslation} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
class MissingTranslationEvent extends Event
{
    /**
     * @var string the message to be translated
     */
    public $message;
    /**
     * @var string the category that the message belongs to
     */
    public $category;
    /**
     * @var string the ID of the language that the message is to be translated to
     */
    public $language;

    /**
     * Constructor.
     * @param mixed $sender sender of this event
     * @param string $category the category that the message belongs to
     * @param string $message the message to be translated
     * @param string $language the ID of the language that the message is to be translated to
     */
    public function __construct($sender, $category, $message, $language)
    {
        parent::__construct($sender);
        $this->message = $message;
        $this->category = $category;
        $this->language = $language;
    }
}
