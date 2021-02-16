<?php
namespace App\Resource\User;

use App\FactoryTest;

class LoginTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->container = FactoryTest::getContainer();
        $this->client = FactoryTest::getClient();
        $this->container['database']->beginTransaction();
        
        $this->user = $this->container['factory']['user']->create([
            'password'  => 'Password123',
        ]);
    }

    public function tearDown(): void
    {
        $this->container['database']->rollback();
    }

    public function testShouldRaiseExceptionAuthenticationFailure()
    {
        $response = FactoryTest::login([
            'email'     => $this->user->email,
            'password'  => 'BadPassword123',
        ]);

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('authentication_failure', $response[0]['name']);
    }

    public function testShouldSucceed()
    {
        $response = FactoryTest::login([
            'email'     => $this->user->email,
            'password'  => 'Password123',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(true, strlen($response['token']) > 0);
    }
}