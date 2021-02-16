<?php

namespace App\Resource\Crop;

class Controller extends \Egg\Controller\Generic
{
    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        $sql = 'SELECT c.*,
                GROUP_CONCAT(DISTINCT cp.`plant_id`) as plant_ids,
                GROUP_CONCAT(DISTINCT t.`id`) as task_ids
                FROM `crop` as c 
                LEFT JOIN `crop_x_plant` as cp ON c.`id` = cp.`crop_id`
                LEFT JOIN `task` as t ON c.`id` = t.`crop_id` 
                WHERE c.`farm_id` = ?
                GROUP BY c.id';
        $statement = $this->container['database']->execute($sql, [$authentication['farm_id']]);
        $crops = $statement->fetchEntitySet();

        foreach ($crops as $crop) {
            $crop->plant_ids = $crop->plant_ids ? array_map('intval', explode(',', $crop->plant_ids)) : [];
            $crop->task_ids = $crop->task_ids ? array_map('intval', explode(',', $crop->task_ids)) : [];
        }

        return $crops;
    }

    public function read($id)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        $sql = 'SELECT c.*,
                GROUP_CONCAT(DISTINCT cp.`plant_id`) as plant_ids,
                GROUP_CONCAT(DISTINCT t.`id`) as task_ids
                FROM `crop` as c
                LEFT JOIN `crop_x_plant` as cp ON c.`id` = cp.`crop_id`
                LEFT JOIN `task` as t ON c.`id` = t.`crop_id`
                WHERE c.`farm_id` = ?
                AND c.`id` = ?
                GROUP BY c.id';
        $statement = $this->container['database']->execute($sql, [$authentication['farm_id'], $id]);
        $crop = $statement->fetchEntity();
        $crop->plant_ids = $crop->plant_ids ? array_map('intval', explode(',', $crop->plant_ids)) : [];
        $crop->task_ids = $crop->task_ids ? array_map('intval', explode(',', $crop->task_ids)) : [];

        return $crop;
    }

    public function create(array $params)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        $id = $this->repository->insert([
            'farm_id'   => $authentication['farm_id'],
            'number'    => $params['number'],
            'mode'      => $params['mode'],
        ]);
        foreach ($params['plant_ids'] as $plant_id) {
            $this->container['repository']['crop_x_plant']->insert([
                'crop_id'   => $id,
                'plant_id'  => $plant_id,
            ]);
        }
        $crop = $this->read($id);

        return $crop;
    }

    public function update($id, array $params)
    {
        $this->repository->updateById([
            'number'    => $params['number'],
            'mode'      => $params['mode'],
        ], $id);
        $cropPlantRepository = $this->container['repository']['crop_x_plant'];
        $cropPlants = $cropPlantRepository->selectAll(['crop_id' => $id]);
        foreach ($cropPlants as $cropPlant) {
            $found = array_search($cropPlant->plant_id, $params['plant_ids']);
            if ($found === false)
                $cropPlantRepository->deleteById($cropPlant->id);
            else
                unset($params['plant_ids'][$found]);
        }
        foreach ($params['plant_ids'] as $plant_id) {
            $cropPlantRepository->insert([
                'crop_id'   => $id,
                'plant_id'  => $plant_id,
            ]);
        }
        $crop = $this->read($id);

        return $crop;
    }

    public function delete($id)
    {
        $this->repository->deleteById($id);
    }

    public function exists(array $params)
    {
        $cropSeralizer = $this->container['serializer']['crop'];
        $authentication = $this->container['request']->getAttribute('authentication');

        $placeholders = implode(',', array_fill(0, count($params['plant_ids']), '?'));
        $sql = 'SELECT c.*
                FROM `crop` as c
                LEFT JOIN `crop_x_plant` as cp ON c.`id` = cp.`crop_id`
                WHERE c.`farm_id` = ?
                AND c.`number` = ?
                AND cp.`plant_id` IN (' . $placeholders . ')';
        $vars = array_merge([ $authentication['farm_id'], $params['number'] ], $params['plant_ids']);
        $statement = $this->container['database']->execute($sql, $vars);
        $crop = $statement->fetchEntity();

        return $crop ? $cropSeralizer->serialize($this->read($crop->id)) : null;
    }
}