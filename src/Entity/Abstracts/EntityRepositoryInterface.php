<?php

declare(strict_types=1);

namespace App\Entity\Abstracts;

use Doctrine\Common\Collections\Criteria;

interface EntityRepositoryInterface
{
    public function createQueryBuilder($alias, $indexBy = null);
    public function createResultSetMappingBuilder($alias);
    public function createNamedQuery($queryName);
    public function createNativeNamedQuery($queryName);
    public function clear();
    public function find($id, $lockMode = null, $lockVersion = null);
    public function findAll();
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
    public function findOneBy(array $criteria, array $orderBy = null);
    public function count(array $criteria);
    public function __call($method, $arguments);
    public function getClassName();
    public function matching(Criteria $criteria);
}