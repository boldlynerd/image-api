<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

//todo validate whether return data is json and has expected values
class ImageControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\ImageController::getUrl
     */
    public function testGetUrl()
    {
        $client = static::createClient();

        $client->request('GET', '/api/images/urls/test/test.jpg');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @covers \App\Controller\ImageController::getByUser
     */
    public function testGetImageListByUser()
    {
        $client = static::createClient();

        $client->request('GET', '/api/images/user/test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @covers \App\Controller\ImageController::create
     */
    public function testCreateImage()
    {
        $client = static::createClient();

        $client->request('POST', '/api/images/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}