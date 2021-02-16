<?php

namespace App\Resource\User;

class Controller extends \Egg\Controller\Generic
{
    public function login(array $params)
    {
        $user = $this->repository->selectOneByEmail($params['email']);

        $authData = [
            'user_id' => $user->id,
        ];
        $timeout = $this->container['config']['authentication']['timeout'];
        $token = $this->container['authenticator']->create($authData, $timeout);

        return [
            'token'       => $token,
            'timeout'   => $timeout,
        ];
    }
}