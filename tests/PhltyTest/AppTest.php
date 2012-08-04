<?php

namespace PhlytyTest;

use Phlyty\App;
use Phlyty\Exception;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

class AppTest extends TestCase
{
    public function setUp()
    {
        $this->app = new App();
    }

    public function testLazyLoadsRequest()
    {
        $request = $this->app->request();
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Request', $request);
    }

    public function testLazyLoadsResponse()
    {
        $response = $this->app->response();
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $response);
    }

    public function testRequestIsInjectible()
    {
        $request = new Request();
        $this->app->setRequest($request);
        $this->assertSame($request, $this->app->request());
    }

    public function testResponseIsInjectible()
    {
        $response = new Response();
        $this->app->setResponse($response);
        $this->assertSame($response, $this->app->response());
    }

    public function testHaltShouldRaiseHaltException()
    {
        $this->setExpectedException('Phlyty\Exception\HaltException');
        $this->app->halt(403);
    }

    public function testResponseShouldContainStatusProvidedToHalt()
    {
        try {
            $this->app->halt(403);
            $this->fail('HaltException expected');
        } catch (Exception\HaltException $e) {
        }

        $this->assertEquals(403, $this->app->response()->getStatusCode());
    }

    public function testResponseShouldContainMessageProvidedToHalt()
    {
        try {
            $this->app->halt(500, 'error message');
            $this->fail('HaltException expected');
        } catch (Exception\HaltException $e) {
        }

        $this->assertContains('error message', $this->app->response()->getContent());
    }

    public function testStopShouldRaiseHaltException()
    {
        $this->setExpectedException('Phlyty\Exception\HaltException');
        $this->app->stop();
    }

    public function testResponseShouldRemainUnalteredAfterStop()
    {
        $this->app->response()->setStatusCode(200)
                              ->setContent('foo bar');
        try {
            $this->app->stop();
            $this->fail('HaltException expected');
        } catch (Exception\HaltException $e) {
        }

        $this->assertEquals(200, $this->app->response()->getStatusCode());
        $this->assertContains('foo bar', $this->app->response()->getContent());
    }

    public function testRedirectShouldRaiseHaltException()
    {
        $this->setExpectedException('Phlyty\Exception\HaltException');
        $this->app->redirect('http://github.com');
    }

    public function testRedirectShouldSet302ResponseStatusByDefault()
    {
        try {
            $this->app->redirect('http://github.com');
            $this->fail('HaltException expected');
        } catch (Exception\HaltException $e) {
        }

        $this->assertEquals(302, $this->app->response()->getStatusCode());
    }

    public function testRedirectShouldSetResponseStatusBasedOnProvidedStatusCode()
    {
        try {
            $this->app->redirect('http://github.com', 301);
            $this->fail('HaltException expected');
        } catch (Exception\HaltException $e) {
        }

        $this->assertEquals(301, $this->app->response()->getStatusCode());
    }

    public function testRedirectShouldSetLocationHeader()
    {
        try {
            $this->app->redirect('http://github.com');
            $this->fail('HaltException expected');
        } catch (Exception\HaltException $e) {
        }

        $response = $this->app->response();
        $headers  = $response->getHeaders();
        $this->assertTrue($headers->has('Location'));

        $location = $headers->get('Location');
        $uri      = $location->getUri();
        $this->assertEquals('http://github.com', $uri);
    }
}
