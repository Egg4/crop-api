<?php
namespace App\Resource\Farm;

use App\FactoryTest;

class ChooseTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->container = FactoryTest::getContainer();
        $this->client = FactoryTest::getClient();
        $this->container['database']->beginTransaction();

        $this->user = $this->container['factory']['user']->create([
            'password'  => 'Password123',
        ]);
        $this->farm = $this->container['factory']['farm']->create();
        $this->container['factory']['farm_x_user']->create([
            'farm_id'   =>  $this->farm->id,
            'user_id'   =>  $this->user->id,
        ]);

        FactoryTest::login([
            'email'     => $this->user->email,
            'password'  => 'Password123',
        ]);
    }

    public function tearDown(): void
    {
        $this->container['database']->rollback();
    }

    public function testShouldRaiseExceptionNotFound()
    {
        $response = FactoryTest::chooseFarm([
            'id'     => $this->farm->id + 1, //Undefined farm id 
        ]);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('not_found', $response[0]['name']);
    }

    public function testShouldSucceed()
    {
        $response = FactoryTest::chooseFarm([
            'id'     => $this->farm->id,
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(true, strlen($response['token']) > 0);
    }
}