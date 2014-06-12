<?php

use Mindy\Locale\LocalizedFormatter;
use Mindy\Base\Mindy;

class CLocalizedFormatterTest extends CTestCase
{
    public function tearDown()
    {
        parent::tearDown();
        Mindy::app()->language = null; // reset language to not affect other tests
    }

    /**
     * Test boolean format translation
     */
    public function testBooleanFormat()
    {
        Mindy::app()->setComponent('format', new LocalizedFormatter());

        $this->assertEquals('Yes', Mindy::app()->format->boolean(true));
        $this->assertEquals('No', Mindy::app()->format->boolean(false));

        Mindy::app()->setComponent('format', new LocalizedFormatter());
        Mindy::app()->setLanguage('de');

        $this->assertEquals('Ja', Mindy::app()->format->boolean(true));
        $this->assertEquals('Nein', Mindy::app()->format->boolean(false));

        Mindy::app()->setComponent('format', new LocalizedFormatter());
        Mindy::app()->setLanguage('en_US');

        $this->assertEquals('Yes', Mindy::app()->format->boolean(true));
        $this->assertEquals('No', Mindy::app()->format->boolean(false));
    }
}
