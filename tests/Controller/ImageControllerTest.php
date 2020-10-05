<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImageControllerTest extends WebTestCase
{
    public function testGetUrl()
    {
        $client = static::createClient();

        $client->request('GET', '/api/images/urls/test/test.jpg');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testGetImageListByUser()
    {
        $client = static::createClient();

        $client->request('GET', '/api/images/user/test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCreateImage()
    {
        $client = static::createClient();

        $client->request('POST', '/api/images/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}