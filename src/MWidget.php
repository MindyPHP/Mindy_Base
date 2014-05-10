<?php

use Mindy\Utils\RenderTrait;

/**
 *
 * CoreWidget class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.components
 * @since 1.1.1
 * @version 1.0
 *
 */
class MWidget extends MBaseWidget
{
    use RenderTrait;

    public function render($view, array $data = [])
    {
        return $this->renderTemplate($view, $data);
    }
}
