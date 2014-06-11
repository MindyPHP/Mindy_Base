<?php


use Mindy\Base\Mindy;

class CHttpSessionTest extends CTestCase
{
    protected function checkProb($gcProb)
    {
        Mindy::app()->session->gCProbability = $gcProb;
        $value = Mindy::app()->session->gCProbability;
        $this->assertInternalType('float', $value);
        $this->assertLessThanOrEqual(1, $value);
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(1 / 21474836.47, abs($gcProb - $value));
    }

    /**
     * @covers CHttpSession::getGCProbability
     * @covers CHttpSession::setGCProbability
     */
    public function testSetGet()
    {
        Mindy::app()->setComponents(array('session' => array(
            'class' => '\Mindy\Base\HttpSession',
            'cookieMode' => 'none',
            'savePath' => sys_get_temp_dir(),
            'sessionName' => 'CHttpSessionTest',
            'timeout' => 5,
        )));
        /** @var $sm CHttpSession */

        $this->checkProb(1);
        $this->checkProb(0);
        $gcProb = 1.0;
        while ($gcProb > 1 / 2147483647) {
            $this->checkProb($gcProb);
            $gcProb = $gcProb / 9;
        }
    }
}
