<?php

namespace App\Resource\User;

use \Egg\Yolk\Rand;

class Factory extends \Egg\Orm\Factory\Generic
{
    protected function createEmail($value)
    {
        return empty($value) ? Rand::alphaLower(8) . '@' . Rand::alphaLower(5) . '.' . Rand::alphaLower(2) : $value;
    }

    protected function createPassword($value)
    {
        $value = empty($value) ? Rand::alphaUpper(4) . Rand::alphaLower(4) . Rand::numeric(4) : $value;
        return $this->container['password']['hash']($value);
    }
}