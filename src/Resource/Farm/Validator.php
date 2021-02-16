<?php

namespace App\Resource\Farm;

use Egg\Exception\InvalidContent as InvalidContentException;

class Validator extends \Egg\Validator\Generic
{
    public function choose(array $params)
    {
        $this->requireParams(['id'], $params);

        $authentication = $this->container['request']->getAttribute('authentication');
        $farmUser = $this->container['repository']['farm_x_user']->selectOne([
            'farm_id'   => $params['id'],
            'user_id'   => $authentication['user_id'],
        ]);
        if (!$farmUser) {
            throw new \Egg\Http\Exception($this->container['response'], 404, new \Egg\Http\Error(array(
                'name'          => 'not_found',
                'description'   => sprintf('"Farm %s" not found', $params['id']),
            )));
        }
    }
}