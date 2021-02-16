<?php

namespace App;

class Authorizer extends \Egg\Authorizer\Generic
{
    protected function analyse($action)
    {
        $authentication = $this->container['request']->getAttribute('authentication');

        return $this->isUserLogged($authentication)
            ? $this->analyseUserLogged($authentication, $action)
            : [];
    }

    protected function isUserLogged($authentication)
    {
        return is_array($authentication)
        AND array_key_exists('user_id', $authentication);
    }

    protected function isFarmChosen($authentication)
    {
        return $this->isUserLogged($authentication)
        AND array_key_exists('farm_id', $authentication);
    }

    protected function analyseUserLogged($authentication, $action)
    {
        if ($this->resource == 'plant' AND in_array($action, ['select', 'read'])) {
            return [];
        }

        if ($this->resource == 'farm' AND in_array($action, ['select', 'choose'])) {
            return [];
        }

        if ($this->isFarmChosen($authentication)) {
            return $this->analyseFarmChosen($authentication, $action);
        }

        throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
            'name'          => 'not_allowed',
            'description'   => sprintf('"%s %s" access denied', $this->resource, $action),
        )));
    }

    protected function analyseFarmChosen($authentication, $action)
    {
        if ($this->resource == 'variety' AND in_array($action, ['select', 'read', 'create', 'update', 'delete'])) {
            return [];
        }

        if ($this->resource == 'crop' AND in_array($action, ['select', 'read', 'create', 'update', 'delete', 'exists'])) {
            return [];
        }

        if ($this->resource == 'task' AND in_array($action, ['select', 'read', 'create', 'update', 'delete'])) {
            return [];
        }

        throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
            'name'          => 'not_allowed',
            'description'   => sprintf('"%s %s" access denied', $this->resource, $action),
        )));

        /*
        // Admin user
        if (is_null($authentication['role_id'])) {
            return $this->analyseAdminAccess($authentication, $action);
        }
        // Not admin user
        else {
            return $this->analyseRoleAccess($authentication, $action);
        }
        */
    }

    /*
    protected function analyseAdminAccess($authentication, $action)
    {
        if ($this->resource == 'user' AND $action == 'select') {
            return [];
        }

        return [
            'entity_id' => $authentication['entity_id'],
        ];
    }

    protected function analyseRoleAccess($authentication, $action)
    {
        $roleAccessRepository = $this->container['repository']['role_access'];
        $roleAccess = $roleAccessRepository->selectOne([
            'entity_id' => $authentication['entity_id'],
            'role_id'   => $authentication['role_id'],
            'resource'  => $this->resource,
        ]);

        if ($roleAccess
            AND isset($roleAccess->$action)
            AND $roleAccess->$action === true
        ) {
            return [
                'entity_id' => $authentication['entity_id'],
            ];
        }

        throw new \Egg\Http\Exception($this->container['response'], 403, new \Egg\Http\Error(array(
            'name'          => 'not_allowed',
            'description'   => sprintf('"%s %s" access denied', $this->resource, $action),
        )));
    }
    */
}