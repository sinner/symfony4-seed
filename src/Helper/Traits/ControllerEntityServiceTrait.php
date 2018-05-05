<?php

namespace App\Helper\Traits;

use App\Services\Entity\Abstracts\EntityServiceAbstract;

trait ControllerEntityServiceTrait
{
    /**
     * Returns an instance of an entity service for a given entity name.
     *
     * @param string $entityName The name of the entity you want the service for (case insensitive).
     *
     * @throws \RuntimeException Container not available.
     * @return EntityServiceAbstract
     */
    protected function getEntityService(string $entityName)
    {
        if (!isset($this->container)) {
            throw new \RuntimeException('Expected the service container to be available. Could not retrieve service.');
        }

        return $this->container->get('app.entity.service.' . strtolower($entityName));
    }
}