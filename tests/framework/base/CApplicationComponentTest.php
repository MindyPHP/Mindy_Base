<?php

class CApplicationComponentTest extends CTestCase
{
    public function testInitialization()
    {
        $c = $this->getMockForAbstractClass('\Mindy\Base\ApplicationComponent', array('init', 'getIsInitialized'), '', NULL);
        $c->expects($this->any())
            ->method('getIsInitialized')
            ->will($this->returnValue(FALSE));
        $this->assertFalse($c->getIsInitialized());
        $c->init();
        $this->assertTrue($c->getIsInitialized());
    }
}
