<?php
use Mindy\Base\Mindy;

/**
 * Tests for various usages of Mindy::t
 *
 * http://code.google.com/p/yii/issues/detail?id=1875
 * http://code.google.com/p/yii/issues/detail?id=1987
 *
 * http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
 */
class YiiTTest extends CTestCase
{
    function setUp()
    {
        $config = array(
            'sourceLanguage' => 'es',
            'components' => array(
                'messages' => array(
                    'class' => '\Mindy\Locale\PhpMessageSource',
                    'basePath' => dirname(__FILE__) . '/data',
                    //'forceTranslation' => true,
                ),
            ),
        );

        new TestApplication($config);
        Mindy::app()->configure($config);
    }

    function tearDown()
    {
        Mindy::app()->sourceLanguage = 'en_us';
    }

    // Simple: 'msg'
    function testSimple()
    {
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('апельсины', Mindy::t('test', 'oranges'));
    }

    function testSimpleSameLanguage()
    {
        Mindy::app()->setLanguage('es');
        $this->assertEquals('no_changes', Mindy::t('test', 'no_changes'));
    }

    function testSimplePlaceholders()
    {
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('сумочки caviar', Mindy::t('test', '{brand} bags', array('{brand}' => 'caviar')));
        $this->assertEquals('в корзине: 10', Mindy::t('test', 'in the cart: {n}', 10));
    }

    function testSimplePlaceholdersSameLanguage()
    {
        Mindy::app()->setLanguage('es');
        $this->assertEquals('10 changes', Mindy::t('test', '{n} changes', 10));
    }

    // Plural: 'msg1|msg2|msg3'
    function testPlural()
    {
        // CLDR
        Mindy::app()->setLanguage('ru');

        // array notation
        $this->assertEquals('огурец', Mindy::t('test', 'cucumber|cucumbers', array(1)));

        //ru
        $this->assertEquals('огурец', Mindy::t('test', 'cucumber|cucumbers', 1));
        $this->assertEquals('огурец', Mindy::t('test', 'cucumber|cucumbers', 101));
        $this->assertEquals('огурец', Mindy::t('test', 'cucumber|cucumbers', 51));
        $this->assertEquals('огурца', Mindy::t('test', 'cucumber|cucumbers', 2));
        $this->assertEquals('огурца', Mindy::t('test', 'cucumber|cucumbers', 62));
        $this->assertEquals('огурца', Mindy::t('test', 'cucumber|cucumbers', 104));
        $this->assertEquals('огурцов', Mindy::t('test', 'cucumber|cucumbers', 5));
        $this->assertEquals('огурцов', Mindy::t('test', 'cucumber|cucumbers', 78));
        $this->assertEquals('огурцов', Mindy::t('test', 'cucumber|cucumbers', 320));
        $this->assertEquals('огурцов', Mindy::t('test', 'cucumber|cucumbers', 0));

        // fractions (you should specify fourh variant to use these in Russian)
        $this->assertEquals('огурца', Mindy::t('test', 'cucumber|cucumbers', 1.5));

        // en
        Mindy::app()->setLanguage('en');

        $this->assertEquals('cucumber', Mindy::t('test', 'cucumber|cucumbers', 1));
        $this->assertEquals('cucumbers', Mindy::t('test', 'cucumber|cucumbers', 2));
        $this->assertEquals('cucumbers', Mindy::t('test', 'cucumber|cucumbers', 0));

        // short forms
        Mindy::app()->setLanguage('ru');

        $this->assertEquals('огурец', Mindy::t('test', 'cucumber|cucumbers', 1));

        // explicit params
        $this->assertEquals('огурец', Mindy::t('test', 'cucumber|cucumbers', array(0 => 1)));
    }

    function testPluralPlaceholders()
    {
        Mindy::app()->setLanguage('ru');

        $this->assertEquals('1 огурец', Mindy::t('test', '{n} cucumber|{n} cucumbers', 1));
        $this->assertEquals('2 огурца', Mindy::t('test', '{n} cucumber|{n} cucumbers', 2));
        $this->assertEquals('5 огурцов', Mindy::t('test', '{n} cucumber|{n} cucumbers', 5));

        // more placeholders
        $this->assertEquals('+ 5 огурцов', Mindy::t('test', '{sign} {n} cucumber|{sign} {n} cucumbers', array(5, '{sign}' => '+')));

        // placeholder swapping
        $this->assertEquals('один огурец', Mindy::t('test', '{n} cucumber|{n} cucumbers', array(1, '{n}' => 'один')));
    }

    /**
     * If there are useless params in translation just ignore them.
     */
    function testPluralMoreVariants()
    {
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('шляпы', Mindy::t('test', 'hat|hats', array(2)));
    }

    /**
     * If there are less variants in translation like
     * 'zombie|zombies' => 'зомби' (CLDR requires 3 variants for Russian
     * but zombie is too special to be plural)
     *
     * Same for Chinese but there are no plurals at all.
     */
    function testPluralLessVariants()
    {
        // three variants are required and only one specified (still valid for
        // Russian in some special cases)
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('зомби', Mindy::t('test', 'zombie|zombies', 10));
        $this->assertEquals('зомби', Mindy::t('test', 'zombie|zombies', 1));

        // language with no plurals
        Mindy::app()->setLanguage('zh_cn');
        $this->assertEquals('k-s', Mindy::t('test', 'kiss|kisses', 1));

        // 3 variants are required while only 2 specified
        // this one is synthetic but still good to know it at least does not
        // produce error
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('син1', Mindy::t('test', 'syn1|syn2|syn3', 1));
        $this->assertEquals('син2', Mindy::t('test', 'syn1|syn2|syn3', 2));
        $this->assertEquals('син2', Mindy::t('test', 'syn1|syn2|syn3', 5));
    }

    function pluralLessVariantsInSource()
    {
        // new doesn't have two forms in English
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('новости', Mindy::t('test', 'news', 2));
    }

    function testPluralSameLanguage()
    {
        Mindy::app()->setLanguage('es');

        $this->assertEquals('cucumbez', Mindy::t('test', 'cucumbez|cucumberz', 1));
        $this->assertEquals('cucumberz', Mindy::t('test', 'cucumbez|cucumberz', 2));
        $this->assertEquals('cucumberz', Mindy::t('test', 'cucumbez|cucumberz', 0));
    }

    function testPluralPlaceholdersSameLanguage()
    {
        Mindy::app()->setLanguage('es');

        $this->assertEquals('1 cucumbez', Mindy::t('test', '{n} cucumbez|{n} cucumberz', 1));
        $this->assertEquals('2 cucumberz', Mindy::t('test', '{n} cucumbez|{n} cucumberz', 2));
        $this->assertEquals('5 cucumberz', Mindy::t('test', '{n} cucumbez|{n} cucumberz', 5));
    }

    // Choice: 'expr1#msg1|expr2#msg2|expr3#msg3'
    function testChoice()
    {
        Mindy::app()->setLanguage('ru');
        $this->assertEquals('ru', Mindy::app()->getLanguage());

        // simple choices
        $this->assertEquals('одна книга', Mindy::t('test', 'n==1#one book|n>1#many books', 1));
        $this->assertEquals('много книг', Mindy::t('test', 'n==1#one book|n>1#many books', 10));
        $this->assertEquals('одна книга', Mindy::t('test', '1#one book|n>1#many books', 1));
        $this->assertEquals('много книг', Mindy::t('test', '1#one book|n>1#many books', 10));
    }

    function testChoiceSameLanguage()
    {
        Mindy::app()->setLanguage('es');

        $this->assertEquals('one book', Mindy::t('test', 'n==1#one book|n>1#many books', 1));
        $this->assertEquals('many books', Mindy::t('test', 'n==1#one book|n>1#many books', 10));
    }

    function testChoicePlaceholders()
    {
        //$this->assertEquals('51 apples', Mindy::t('app', '1#1apple|n>1|{n} apples', array(51, 'n'=>51)));
    }

    function testChoicePlaceholdersSameLanguage()
    {

    }
}
