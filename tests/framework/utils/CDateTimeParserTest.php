<?php

use Mindy\Locale\DateTimeParser;
use Mindy\Base\Mindy;

class CDateTimeParserTest extends CTestCase
{
    public function testAllPatterns()
    {
        $this->assertEquals(
            '02 Aug, 2010, 05:09:07',
            date('d M, Y, H:i:s', DateTimeParser::parse('XX, 2.8.10, 5:9:7 AM', '??, d.M.yy, h:m:s a'))
        );
        $this->assertEquals(
            '02 Aug, 2010, 05:09:07',
            date('d M, Y, H:i:s', DateTimeParser::parse('02/08/2010, yyy, 05:9:7', 'dd/MM/yyyy, ???, hh:m:s'))
        );
        $this->assertEquals(
            '02 Aug, 2010, 05:09:07',
            date('d M, Y, H:i:s', DateTimeParser::parse('2\AUG\2010, 5:09:07 Am, ZzZ', 'd\MMM\yyyy, H:mm:ss a, ???'))
        );
        $this->assertEquals(
            '02 Aug, 2010, 05:09:07',
            date('d M, Y, H:i:s', DateTimeParser::parse('02_augUST|10, W, 05-09-07', 'dd_MMMM|yy, ?, HH-mm-ss'))
        );
    }

    public function testParseDefaults()
    {
        $this->assertEquals(
            '31-12-2011 23:59:59',
            date('d-m-Y H:i:s', DateTimeParser::parse('2011-12-31', 'yyyy-MM-dd', array('hour' => 23, 'minute' => 59, 'second' => 59)))
        );
        // test matching with wildcards, this example is mssql timestamp
        $this->assertEquals(
            '2011-02-10 23:43:04',
            date('Y-m-d H:i:s', DateTimeParser::parse('2011-02-10 23:43:04.973', 'yyyy-MM-dd hh:mm:ss.???'))
        );
        $this->assertEquals(
            '2011-01-10 23:43:04',
            date('Y-m-d H:i:s', DateTimeParser::parse('2011-01-10 23:43:04.973', 'yyyy?MM?dd?hh?mm?ss????'))
        );
    }

    public function testShortMonthTitle()
    {
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 SEP, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, mar', 'dd, yyyy, HH:mm, MMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('Dec 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
        );
    }

    public function testMonthTitle()
    {
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 September, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, march', 'dd, yyyy, HH:mm, MMMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('DECEMBER 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
        );
    }

    public function testLocaleShortMonthTitle()
    {
        // remember active application language and charset
        $oldLanguage = Mindy::app()->getLanguage();
        $oldCharset = Mindy::app()->charset;

        // ru_RU.UTF-8
        Mindy::app()->setLanguage('ru_RU');
        Mindy::app()->charset = 'UTF-8';
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 СЕНТ, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, март', 'dd, yyyy, HH:mm, MMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('Дек 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
        );

        // de_DE.UTF-8
        Mindy::app()->setLanguage('de_DE');
        Mindy::app()->charset = 'UTF-8';
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 sep, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, mär', 'dd, yyyy, HH:mm, MMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('Dez 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
        );

        // zh_CN.UTF-8
        Mindy::app()->setLanguage('zh_CN');
        Mindy::app()->charset = 'UTF-8';
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 九月, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, 三月', 'dd, yyyy, HH:mm, MMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('十二月 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
        );

        // reestablish old active language and charset
        Mindy::app()->setLanguage($oldLanguage);
        Mindy::app()->charset = $oldCharset;
    }

    public function testLocaleMonthTitle()
    {
        // remember active application language and charset
        $oldLanguage = Mindy::app()->getLanguage();
        $oldCharset = Mindy::app()->charset;

        // ru_RU.UTF-8
        Mindy::app()->setLanguage('ru_RU');
        Mindy::app()->charset = 'UTF-8';
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 СЕНТЯБРЯ, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, март', 'dd, yyyy, HH:mm, MMMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('Декабря 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
        );

        // de_DE.UTF-8
        Mindy::app()->setLanguage('de_DE');
        Mindy::app()->charset = 'UTF-8';
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 SEPTEMBER, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, März', 'dd, yyyy, HH:mm, MMMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('dezember 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
        );

        // zh_CN.UTF-8
        Mindy::app()->setLanguage('zh_CN');
        Mindy::app()->charset = 'UTF-8';
        $this->assertEquals(
            '21 Sep, 2011, 13:37',
            date('d M, Y, H:i', DateTimeParser::parse('21 九月, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
        );
        $this->assertEquals(
            '05, 1991, 01:09, Mar',
            date('d, Y, H:i, M', DateTimeParser::parse('05, 1991, 01:09, 三月', 'dd, yyyy, HH:mm, MMMM'))
        );
        $this->assertEquals(
            'Dec 01, 1971, 23:59',
            date('M d, Y, H:i', DateTimeParser::parse('十二月 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
        );

        // reestablish old active language and charset
        Mindy::app()->setLanguage($oldLanguage);
        Mindy::app()->charset = $oldCharset;
    }

    public function testFailing()
    {
        // empty parsed string
        $this->assertFalse(DateTimeParser::parse('', 'dd MMMM, yyyy, HH:mm'));
        $this->assertFalse(DateTimeParser::parse(false, 'dd MMMM, yyyy, HH:mm'));
        $this->assertFalse(DateTimeParser::parse(null, 'dd MMMM, yyyy, HH:mm'));

        // accidently mixed up arguments
        $this->assertFalse(DateTimeParser::parse('dd/MM/yyyy, ???, hh:m:s', '02/08/2010, yyy, 05:9:7'));
        $this->assertFalse(DateTimeParser::parse('yyyy-MM-dd', '2011-12-31', array('hour' => 23, 'minute' => 59, 'second' => 59)));

        // current locale is not ru_RU.UTF-8
        $this->assertFalse(DateTimeParser::parse('21 СЕНТЯБРЯ, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'));

        // current locale is not zh_CN.UTF-8
        $this->assertFalse(DateTimeParser::parse('21 九月, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'));

        // wrong first slash in the parsed string
        $this->assertFalse(DateTimeParser::parse('02\08/2010, yyy, 05:9:7', 'dd/MM/yyyy, ???, hh:m:s'));

        // inexistent patterns
        $this->assertFalse(DateTimeParser::parse('05, 1991, 01:09, mar', 'dd, yyyy, HH:mm, MMMMM')); // illegal month pattern
        $this->assertFalse(DateTimeParser::parse('05, 1991, 01:09, mar', 'ddd, yyyy, HH:mm, MMM')); // illegal day pattern
        $this->assertFalse(DateTimeParser::parse('05, 1991, 01:09, mar', 'dd, yyyyy, HH:mm, MMM')); // illegal year pattern
    }
}
