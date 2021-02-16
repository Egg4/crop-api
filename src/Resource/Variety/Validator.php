<?php

namespace App\Resource\Variety;

class Validator extends \Egg\Validator\Generic
{
    public function create(array $params)
    {
        $this->requireParams(['plant_id', 'name'], $params);
    }

    public function update($id, array $params)
    {
        $this->requireParams(['plant_id', 'name'], $params);
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
}