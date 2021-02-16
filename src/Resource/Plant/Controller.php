<?php

namespace App\Resource\Plant;

class Controller extends \Egg\Controller\Generic
{
    public function select(array $filterParams, array $sortParams, array $rangeParams)
    {
        $sql = 'SELECT p.id, p.name AS `name`, s.name AS `species`, g.name AS `genus`, f.name AS `family`, p.color
                FROM `plant` as p
                LEFT JOIN `species` AS s ON s.`id` = p.`species_id`
                LEFT JOIN `genus` AS g ON g.`id` = s.`genus_id`
                LEFT JOIN `family` AS f ON f.`id` = g.`family_id`
                ORDER BY p.name ASC';
        $statement = $this->container['database']->execute($sql);
        $plants = $statement->fetchEntitySet();

        return $plants;
    }

    public function read($id)
    {
        $sql = 'SELECT p.id, p.name AS `name`, s.name AS `species`, g.name AS `genus`, f.name AS `family`, p.color
                FROM `plant` AS p
                LEFT JOIN `species` AS s ON s.`id` = p.`species_id`
                LEFT JOIN `genus` AS g ON g.`id` = s.`genus_id`
                LEFT JOIN `family` AS f ON f.`id` = g.`family_id`
                WHERE p.`id` = ?';
        $statement = $this->container['database']->execute($sql, [$id]);
        $plant = $statement->fetchEntity();

        return $plant;
    }
}