<?php

namespace App\Resource\Variety;

class Controller extends \Egg\Controller\Generic
{
    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        $authentication = $this->container['request']->getAttribute('authentication');
        $varieties = $this->repository->selectAll([
            'farm_id' => $authentication['farm_id'],
        ]);

        return $varieties;
    }

    public function read($id)
    {
        $authentication = $this->container['request']->getAttribute('authentication');
        $variety = $this->repository->selectOne([
            'id' => $id,
            'farm_id' => $authentication['farm_id'],
        ]);

        return $variety;
    }

    public function create(array $params)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        $id = $this->repository->insert([
            'farm_id'   => $authentication['farm_id'],
            'plant_id'  => $params['plant_id'],
            'name'      => $params['name'],
        ]);

        return $this->read($id);
    }

    public function update($id, array $params)
    {
        $this->repository->updateById([
            'plant_id'  => $params['plant_id'],
            'name'      => $params['name'],
        ], $id);

        return $this->read($id);
    }

    public function delete($id)
    {
        $this->repository->deleteById($id);
    }
}