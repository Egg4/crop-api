<?php

namespace App\Resource\Task;

class Validator extends \Egg\Validator\Generic
{
    public function create(array $params)
    {
        $this->requireParams(['crop_id', 'type', 'date', 'time', 'done'], $params);
    }

    public function update($id, array $params)
    {
        $this->requireParams(['type', 'date', 'time', 'done'], $params);
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