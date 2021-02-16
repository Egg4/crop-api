<?php

namespace App\Resource\Task;

class Serializer extends \Egg\Serializer\AbstractSerializer
{
    public function toArray($input) {
        $array = $input->toArray();
        unset($array['farm_id']);
        
        if (isset($array['working']) && !is_null($array['working'])) {
            $array['working'] = $this->serialize($array['working']);
        }
        if (isset($array['seedings']) && !is_null($array['seedings'])) {
            $array['seedings'] = $this->serialize($array['seedings']);
        }
        if (isset($array['plantings']) && !is_null($array['plantings'])) {
            $array['plantings'] = $this->serialize($array['plantings']);
        }
        if (isset($array['harvestings']) && !is_null($array['harvestings'])) {
            $array['harvestings'] = $this->serialize($array['harvestings']);
        }

        return $array;
    }
}