<?php

namespace App\Resource\Farm;

class Controller extends \Egg\Controller\Generic
{
    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        $sql = 'SELECT f.*
                FROM `farm` as f
                LEFT JOIN `farm_x_user` as fu ON f.`id` = fu.`farm_id` 
                WHERE fu.`user_id` = ?';
        $statement = $this->container['database']->execute($sql, [$authentication['user_id']]);
        $farms = $statement->fetchEntitySet();

        return $farms;
    }

    public function choose(array $params)
    {
        $authData = $this->container['request']->getAttribute('authentication');
        $authData = array_merge($authData, [
            'farm_id' => $params['id'],
        ]);
        $timeout = $this->container['config']['authentication']['timeout'];
        $token = $this->container['authenticator']->create($authData, $timeout);

        return [
            'token'       => $token,
            'timeout'   => $timeout,
        ];
    }
}