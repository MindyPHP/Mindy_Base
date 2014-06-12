<?php

use Mindy\Base\HttpCookie;
use Mindy\Base\HttpRequest;

class CCookieCollectionTest extends CTestCase
{
    protected $request;
    protected $cookies;
    protected $testCookies = array(
        'testCookieOne' => 'testValue',
        'someEmptyCookie' => '',
        'IntegerValue' => 1242,
        'cookieWithOptions' => array(
            'value' => 'options',
            'httpOnly' => true,
            'expire' => 12422,
        ),
    );
    protected $cookieBefore;

    public function setUp()
    {
        $this->cookieBefore = $_COOKIE;
        $_COOKIE['testGlobal'] = 'value';
        $this->request = new TestHttpRequest;
        $this->cookies = $this->request->cookies;
    }

    public function tearDown()
    {
        $_COOKIE = $this->cookieBefore;
    }

    /**
     * @covers CCookieCollection::getCookies
     * @covers CCookieCollection::__construct
     */
    public function testConstructorCookieBuilding()
    {
        $this->assertTrue($this->cookies->contains('testGlobal'));
        $this->assertInstanceOf('\Mindy\Base\HttpCookie', $this->cookies['testGlobal']);
        $this->assertEquals($_COOKIE['testGlobal'], $this->cookies['testGlobal']->value);
    }

    /**
     * @runInSeparateProcess
     * @outputBuffering enabled
     */
    public function testAdd()
    {
        $this->cookies['simple_name'] = new HttpCookie('simple_name', 'simple_value');
        $this->assertTrue($this->cookies['simple_name'] instanceof HttpCookie);
        $this->assertEquals('simple_value', $this->cookies['simple_name']->value);
        // test if reference is not destroyed
        $cookie = new HttpCookie('referenceTest', 'someValue');
        $this->cookies[$cookie->name] = $cookie;
        $this->assertTrue($this->cookies[$cookie->name] instanceof HttpCookie);
        $this->assertEquals('someValue', $this->cookies[$cookie->name]->value);
        $cookie->value = 'SomeNewValue';
        $this->assertEquals($cookie->value, $this->cookies[$cookie->name]->value);
        // test if cookies are added to internal array correctly
        $this->assertTrue($this->cookies->contains('simple_name'));
        $this->assertTrue($this->cookies->contains('referenceTest'));
        $this->assertFalse($this->cookies->contains('someNonExistingCookie'));
    }

    /**
     * @runInSeparateProcess
     * @outputBuffering enabled
     */
    public function testRemove()
    {
        // add some cookies to have something to base the tests (remove)
        foreach ($this->testCookies as $name => $options) {
            if (is_array($options)) {
                $value = $options['value'];
                unset($options['value']);
                $cookie = new HttpCookie($name, $value, $options);
            } else
                $cookie = new HttpCookie($name, $options);
            $this->cookies[$cookie->name] = $cookie;
        }
        //check if cookies were added, else fail the test, since it makes no sence to do this, if there are no cookies
        $this->assertTrue($this->cookies->contains('testCookieOne'), 'A default cookie is missing! Check the testcase!');
        $this->assertTrue($this->cookies->contains('someEmptyCookie'), 'A default cookie is missing! Check the testcase!');
        $this->assertTrue($this->cookies->contains('someEmptyCookie'), 'A default cookie is missing! Check the testcase!');
        $this->assertTrue($this->cookies->contains('cookieWithOptions'), 'A default cookie is missing! Check the testcase!');
        // Real tests below:
        foreach ($this->testCookies as $name => $options) {
            if (is_array($options)) {
                $value = $options['value'];
                unset($options['value']);
                $this->assertTrue($this->cookies->remove($name, $options) instanceof HttpCookie);
                $this->assertFalse($this->cookies->contains($name), 'Cookie(with options) has not been removed');
            } else {
                $this->assertTrue($this->cookies->remove($name) instanceof HttpCookie);
                $this->assertFalse($this->cookies->contains($name));
            }
        }
    }

    public function testGetRequest()
    {
        $this->assertTrue($this->cookies->getRequest() instanceof HttpRequest);
        $this->assertEquals($this->request, $this->cookies->getRequest(), 'The collections does not contain the CHttpRequest instance it is stored in.');
    }
}
