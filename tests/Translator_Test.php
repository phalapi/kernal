<?php
namespace PhalApi;

/**
 * PhpUnderControl_PhalApiTranslator_Test
 *
 * 针对 ../PhalApi/Translator.php Translator 类的PHPUnit单元测试
 *
 * @author: dogstar 20150201
 */

class PhpUnderControl_PhalApiTranslator_Test extends \PHPUnit_Framework_TestCase
{
    public $coreTranslator;

    protected function setUp()
    {
        parent::setUp();

        $this->coreTranslator = new Translator();
    }

    protected function tearDown()
    {
    }


    /**
     * @group testGet
     */ 
    public function testGet()
    {
        Translator::setLanguage('zh_cn');

        $this->assertEquals('用户不存在', Translator::get('user not exists'));

        $this->assertEquals('PHPUnit您好，欢迎使用PhalApi！', Translator::get('Hello {name}, Welcome to use PhalApi!', array('name' => 'PHPUnit')));

        $this->assertEquals('PhalApi 我爱你', T('{0} I love you', array('PhalApi')));
        $this->assertEquals('PhalApi 我爱你因为no reasons', T('{0} I love you because {1}', array('PhalApi', 'no reasons')));
    }

    /**
     * @group testSetLanguage
     */ 
    public function testSetLanguage()
    {
        $language = 'en';

        $rs = Translator::setLanguage($language);
    }

    /**
     * @group testFormatVar
     */ 
    public function testFormatVar()
    {
        $name = 'abc';

        $rs = Translator::formatVar($name);

        $this->assertEquals('{abc}', $rs);
    }

    public function testAddMessage() 
    {
        Translator::setLanguage('zh_cn');
        Translator::addMessage(dirname(__FILE__) . '/test_data');

        $this->assertEquals('this is a good way', Translator::get('test'));
    }

    public function testGetWithNoLanguageSet()
    {
        MockTranslator::setLanguageNameSimple(null);

        $rs = T('test');

        Translator::setLanguage('zh_cn');
    }
}

class MockTranslator extends Translator {

    public static function setLanguageNameSimple($lan) {
        Translator::$message = null;
    }
}
