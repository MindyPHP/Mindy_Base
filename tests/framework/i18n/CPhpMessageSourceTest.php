<?php

use Mindy\Base\Mindy;

class CPhpMessageSourceTest extends CTestCase
{
    public function testExtensionTranslation()
    {
        Mindy::setPathOfAlias('CPhpMessageSourceTestRoot', dirname(__FILE__));
        Mindy::app()->setLanguage('de_DE');
        Mindy::app()->messages->extensionPaths['MyTestExtension'] = 'CPhpMessageSourceTestRoot.messages';
        $this->assertEquals('Hallo Welt!', Mindy::t('MyTestExtension.testcategory', 'Hello World!'));
    }

    public function testModuleTranslation()
    {
        Mindy::app()->setLanguage('de_DE');
        $this->assertEquals('Hallo Welt!', Mindy::t('CPhpMessageSourceTest.testcategory', 'Hello World!'));
    }
}
