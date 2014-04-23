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
 * @date 09/04/14.04.2014 18:49
 */
class MUrlManager extends CUrlManager
{
    public $keepSlashes = false;

    public $urlRuleClass = 'MUrlRule';

    public $rulesCsrfExcluded = array();
}
