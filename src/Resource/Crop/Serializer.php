<?php

namespace App\Resource\Crop;

class Serializer extends \Egg\Serializer\AbstractSerializer
{
    public function toArray($input) {
        $array = $input->toArray();
        unset($array['farm_id']);

        return $array;
    }
}