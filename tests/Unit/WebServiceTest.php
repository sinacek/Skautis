<?php

namespace Test\Skautis;

use Skautis;
use Skautis\Wsdl\WebService;


class WebServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $queries = array();

    /**
     * @expectedException Skautis\Exception\AbortException
     */
    public function testWebServiceConstructMissingWsdl()
    {
        $webService = new WebService("", array());
    }

    public function queryCallback($query)
    {
        $this->queries[] = $query;
    }

    public function testCallback()
    {
        $callback = array($this, 'queryCallback');

        $data = array(
            Skautis\Skautis::APP_ID => 123,
            Skautis\Skautis::TOKEN => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        );
        $webService = new WebService("http://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL", $data);
        $webService->subscribe(WebService::EVENT_FAILURE, $callback);

        try {
            $webService->UserDetail();
        } catch (\Exception $e) {
        }

        $this->assertCount(1, $this->queries);
        $this->assertInstanceOf('Skautis\SkautisQuery', $this->queries[0]);
        $this->assertEquals('UserDetail', $this->queries[0]->fname);
        $this->assertGreaterThan(0, strlen($this->queries[0]->getExceptionString()));
        $this->assertEquals('SoapFault', $this->queries[0]->getExceptionClass());
        $this->assertTrue($this->queries[0]->hasFailed());
    }

}