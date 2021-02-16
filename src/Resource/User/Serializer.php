<?php

namespace App\Resource\User;

class Serializer extends \Egg\Serializer\AbstractSerializer
{
    public function toArray($input) {
        $array = $input->toArray();
        unset($array['password']);

        return $array;
    }
}