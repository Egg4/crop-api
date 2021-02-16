<?php

namespace App;

abstract class FactoryTest
{
    public static function getContainer()
    {
        static $container;

        if (!$container) {
            $container = new \Egg\Container();
            $container['environment'] = \Egg\Http\Environment::create([
                'APP_ENV'   => APP_ENV,
                'APP_DEBUG' => APP_DEBUG,
            ]);
            registerServices($container);
            registerComponents($container);
        }

        return $container;
    }

    public static function getClient()
    {
        static $client;

        if (!$client) {
            $client = new \Egg\Http\Client(self::getContainer(), [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ]);
        }

        return $client;
    }

    public static function login($data)
    {
        $client = self::getClient();
        $response = $client->post('/user/login', [], $data);
        if (isset($response['token'])) {
            $client->setHeader('Auth-Token', $response['token']);
        }

        return $response;
    }

    public static function logout()
    {
        $client = self::getClient();
        $client->removeHeader('Auth-Token');
    }

    public static function chooseFarm($data)
    {
        $client = self::getClient();
        $response = $client->post('/farm/choose', [], $data);
        if (isset($response['token'])) {
            $client->setHeader('Auth-Token', $response['token']);
        }

        return $response;
    }
}