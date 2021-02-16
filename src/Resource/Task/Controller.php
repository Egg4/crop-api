<?php

namespace App\Resource\Task;

class Controller extends \Egg\Controller\Generic
{
    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        $authentication = $this->container['request']->getAttribute('authentication');
        $tasks = $this->repository->selectAll([
            'farm_id' => $authentication['farm_id'],
        ]);

        $workingRepository = $this->container['repository']['working'];
        $seedingRepository = $this->container['repository']['seeding'];
        $plantingRepository = $this->container['repository']['planting'];
        $harvestingRepository = $this->container['repository']['harvesting'];
        foreach ($tasks as $task) {
            $task->working = $workingRepository->selectOne(['task_id' => $task->id]);
            $task->seedings = $seedingRepository->selectAll(['task_id' => $task->id]);
            $task->plantings = $plantingRepository->selectAll(['task_id' => $task->id]);
            $task->harvestings = $harvestingRepository->selectAll(['task_id' => $task->id]);
        }

        return $tasks;
    }

    public function read($id)
    {
        $authentication = $this->container['request']->getAttribute('authentication');
        $task = $this->repository->selectOne([
            'id' => $id,
            'farm_id' => $authentication['farm_id'],
        ]);

        $workingRepository = $this->container['repository']['working'];
        $seedingRepository = $this->container['repository']['seeding'];
        $plantingRepository = $this->container['repository']['planting'];
        $harvestingRepository = $this->container['repository']['harvesting'];
        $task->working = $workingRepository->selectOne(['task_id' => $task->id]);
        $task->seedings = $seedingRepository->selectAll(['task_id' => $task->id]);
        $task->plantings = $plantingRepository->selectAll(['task_id' => $task->id]);
        $task->harvestings = $harvestingRepository->selectAll(['task_id' => $task->id]);

        return $task;
    }

    public function create(array $params)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        try {
            $this->container['database']->beginTransaction();
    
            // Task
            $taskId = $this->repository->insert([
                'farm_id'   => $authentication['farm_id'],
                'crop_id'   => $params['crop_id'],
                'type'      => $params['type'],
                'date'      => $params['date'],
                'time'      => $params['time'],
                'done'      => $params['done'],
            ]);
    
            // Working
            if (isset($params['working']) && !is_null($params['working'])) {
                $working = $params['working'];
                $this->container['repository']['working']->insert([
                    'task_id'   => $taskId,
                    'duration'  => $working['duration'],
                    'mwu'       => $working['mwu'],
                ]);
            }

            // Seeding
            if (isset($params['seedings']) && is_array($params['seedings'])) {
                foreach ($params['seedings'] as $seeding) {
                    $this->container['repository']['seeding']->insert([
                        'task_id'       => $taskId,
                        'variety_id'    => $seeding['variety_id'],
                        'mode'          => $seeding['mode'],
                        'density'       => $seeding['density'],
                        'area'          => $seeding['area'],
                        'unit'          => $seeding['unit'],
                    ]);
                }
            }
            
            // Planting
            if (isset($params['plantings']) && is_array($params['plantings'])) {
                foreach ($params['plantings'] as $planting) {
                    $this->container['repository']['planting']->insert([
                        'task_id'           => $taskId,
                        'variety_id'        => $planting['variety_id'],
                        'intra_row_spacing' => $planting['intra_row_spacing'],
                        'inter_row_spacing' => $planting['inter_row_spacing'],
                        'quantity'          => $planting['quantity'],
                    ]);
                }
            }

            // Harvesting
            if (isset($params['harvestings']) && is_array($params['harvestings'])) {
                foreach ($params['harvestings'] as $harvesting) {
                    $this->container['repository']['harvesting']->insert([
                        'task_id'       => $taskId,
                        'variety_id'    => $harvesting['variety_id'],
                        'quantity'      => $harvesting['quantity'],
                        'unit'          => $harvesting['unit'],
                    ]);
                }
            }

            $this->container['database']->commit();
        }
        catch (\Exception $exception) {
            $this->container['database']->rollback();
            throw $exception;
        }

        return $this->read($taskId);
    }

    public function update($taskId, array $params)
    {
        try {
            $this->container['database']->beginTransaction();

            // Task
            $this->repository->updateById([
                'type'      => $params['type'],
                'date'      => $params['date'],
                'time'      => $params['time'],
                'done'      => $params['done'],
            ], $taskId);

            // Working
            $workingRepository = $this->container['repository']['working'];
            $working = $workingRepository->selectOne(['task_id' => $taskId]);
            if ($working) {
                if (!is_null($params['working'])) {
                    $workingRepository->updateById([
                        'duration'  => $params['working']['duration'],
                        'mwu'       => $params['working']['mwu'],
                    ], $working->id);
                }
                elseif (is_null($params['working'])) {
                    $workingRepository->deleteById($working->id);
                }
            }
            else {
                if (!is_null($params['working'])) {
                    $workingRepository->insert([
                        'task_id'   => $taskId,
                        'duration'  => $params['working']['duration'],
                        'mwu'       => $params['working']['mwu'],
                    ]);
                }
            }
            
            // Seeding
            $seedingRepository = $this->container['repository']['seeding'];
            $seedings = $seedingRepository->selectAll(['task_id' => $taskId]);
            foreach ($seedings as $seeding) {
                $index = array_search($seeding->id, array_column($params['seedings'], 'id'));
                if ($index !== false) {
                    $seedingRepository->updateById([
                        'variety_id'    => $params['seedings'][$index]['variety_id'],
                        'mode'          => $params['seedings'][$index]['mode'],
                        'density'       => $params['seedings'][$index]['density'],
                        'area'          => $params['seedings'][$index]['area'],
                        'unit'          => $params['seedings'][$index]['unit'],
                    ], $seeding->id);
                    unset($params['seedings'][$index]);
                    $params['seedings'] = array_values($params['seedings']);
                }
                else {
                    $seedingRepository->deleteById($seeding->id);
                }
            }
            foreach ($params['seedings'] as $seeding) {
                $seedingRepository->insert([
                    'task_id'       => $taskId,
                    'variety_id'    => $seeding['variety_id'],
                    'mode'          => $seeding['mode'],
                    'density'       => $seeding['density'],
                    'area'          => $seeding['area'],
                    'unit'          => $seeding['unit'],
                ]);
            }

            // Planting
            $plantingRepository = $this->container['repository']['planting'];
            $plantings = $plantingRepository->selectAll(['task_id' => $taskId]);
            foreach ($plantings as $planting) {
                $index = array_search($planting->id, array_column($params['plantings'], 'id'));
                if ($index !== false) {
                    $plantingRepository->updateById([
                        'variety_id'        => $params['plantings'][$index]['variety_id'],
                        'intra_row_spacing' => $params['plantings'][$index]['intra_row_spacing'],
                        'inter_row_spacing' => $params['plantings'][$index]['inter_row_spacing'],
                        'quantity'          => $params['plantings'][$index]['quantity'],
                    ], $planting->id);
                    unset($params['plantings'][$index]);
                    $params['plantings'] = array_values($params['plantings']);
                }
                else {
                    $plantingRepository->deleteById($planting->id);
                }
            }
            foreach ($params['plantings'] as $planting) {
                $plantingRepository->insert([
                    'task_id'           => $taskId,
                    'variety_id'        => $planting['variety_id'],
                    'intra_row_spacing' => $planting['intra_row_spacing'],
                    'inter_row_spacing' => $planting['inter_row_spacing'],
                    'quantity'          => $planting['quantity'],
                ]);
            }

            // Harvesting
            $harvestingRepository = $this->container['repository']['harvesting'];
            $harvestings = $harvestingRepository->selectAll(['task_id' => $taskId]);
            foreach ($harvestings as $harvesting) {
                $index = array_search($harvesting->id, array_column($params['harvestings'], 'id'));
                if ($index !== false) {
                    $harvestingRepository->updateById([
                        'variety_id'    => $params['harvestings'][$index]['variety_id'],
                        'quantity'      => $params['harvestings'][$index]['quantity'],
                        'unit'          => $params['harvestings'][$index]['unit'],
                    ], $harvesting->id);
                    unset($params['harvestings'][$index]);
                    $params['harvestings'] = array_values($params['harvestings']);
                }
                else {
                    $harvestingRepository->deleteById($harvesting->id);
                }
            }
            foreach ($params['harvestings'] as $harvesting) {
                $harvestingRepository->insert([
                    'task_id'       => $taskId,
                    'variety_id'    => $harvesting['variety_id'],
                    'quantity'      => $harvesting['quantity'],
                    'unit'          => $harvesting['unit'],
                ]);
            }

            $this->container['database']->commit();
        }
        catch (\Exception $exception) {
            $this->container['database']->rollback();
            throw $exception;
        }

        return $this->read($taskId);
    }

    public function delete($id)
    {
        $this->repository->deleteById($id);
    }
}