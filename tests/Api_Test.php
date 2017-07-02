<?php
namespace PhalApi;

use PhalApi\Exception\InternalServerErrorException;

/**
 * PhpUnderControl_PhalApiApi_Test
 *
 * 针对 \PhalApi\Api 类的PHPUnit单元测试
 *
 * @author: dogstar 20170702
 */

class PhpUnderControl_PhalApiApi_Test extends \PHPUnit_Framework_TestCase
{
    public $api;

    protected function setUp()
    {
        parent::setUp();

        $this->api = new Api();
    }

    protected function tearDown()
    {
        DI()->filter = NULL;
    }

    /**
     * @group testInitialize
     */ 
    public function testInitialize()
    {
        DI()->request = new Request(array('service' => 'Default.Index'));
        $rs = $this->api->init();
    }


    public function testInitializeWithWrongSign()
    {
        $data = array();
        $data['service'] = 'Default.Index';

        DI()->request = new Request($data);
        $rs = $this->api->init();
    }

    public function testInitializeWithRightSign()
    {
        $data = array();
        $data['service'] = 'Default.Index';

        DI()->request = new Request($data);
        $rs = $this->api->init();

    }

    public function testSetterAndGetter()
    {
        $this->api->username = 'phalapi';
        $this->assertEquals('phalapi', $this->api->username);
    }

    /**
     * @expectedException \PhalApi\Exception\InternalServerErrorException
     */
    public function testGetUndefinedProperty()
    {
        $this->api->name = 'PhalApi';
        $rs = $this->api->noThisKey;
    }

    public function testApiImpl()
    {
        $data = array();
        $data['service'] = 'PhalApi.ImplApi.Add';
        $data['version'] = '1.1.0';
        $data['left'] = '6';
        $data['right'] = '1';

        DI()->request = new Request($data);
        DI()->filter = '\PhalApi\ImplFilter';

        $impl = new ImplApi();
        $impl->init();

        $rs = $impl->add();
        $this->assertEquals(7, $rs);
    }

    /**
     * @expectedException \PhalApi\Exception\InternalServerErrorException
     */
    public function testIllegalFilter()
    {
        DI()->filter = 'ImplNotFoundFilter';

        $impl = new ImplApi();
        $impl->init();
    }
}
