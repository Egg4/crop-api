<?php

namespace App\Resource\Crop;

class Validator extends \Egg\Validator\Generic
{
    public function create(array $params)
    {
        $this->requireParams(['number', 'mode', 'plant_ids'], $params);
    }

    public function update($id, array $params)
    {
        $this->requireParams(['number', 'mode', 'plant_ids'], $params);
        $authentication = $this->container['request']->getAttribute('authentication');
        $this->checkEntityExists($this->resource, [
            'id' => $id,
            'farm_id' => $authentication['farm_id'],
        ]);
    }

    public function delete($id)
    {
        $authentication = $this->container['request']->getAttribute('authentication');
        $this->checkEntityExists($this->resource, [
            'id' => $id,
            'farm_id' => $authentication['farm_id'],
        ]);
    }

    public function exists(array $params)
    {
        $this->requireParams(['number', 'plant_ids'], $params);
    }
}