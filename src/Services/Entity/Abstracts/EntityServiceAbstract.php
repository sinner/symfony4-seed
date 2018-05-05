<?php

namespace App\Services\Entity\Abstracts;

use App\Entity\Abstracts\EntityInterface;
use Doctrine\ORM\{
    EntityManager,
    EntityRepository,
    Proxy\Proxy,
    Query
};
use Symfony\Component\Form\Form;

/**
 * Class EntityServiceAbstract
 * @package App\Services\Entity\Abstracts
 */
abstract class EntityServiceAbstract
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @param EntityManager $entityManager
     * @param EntityRepository $entityRepository
     */
    public function __construct(EntityManager $entityManager, EntityRepository $entityRepository)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityRepository;
    }

    /**
     * Returns a Doctrine proxy object for the given entity and associated id.
     *
     * @param string $entityClass Ex: ENOneReachBasicDoctrineBundle:Campaign
     * @param mixed $id The primary key id of the entity.
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Proxy
     */
    public function getReference(string $entityClass, $id): Proxy
    {
        return $this->getEntityManager()->getReference($entityClass, $id);
    }

    /**
     * Persist entity data to the application.
     *
     * @param EntityInterface $entity
     * @param bool $flush If true, will flush entity to database.
     * @throws \Doctrine\ORM\ORMException
     * 
     * @return EntityServiceAbstract
     * 
     */
    public function merge(EntityInterface $entity, bool $flush = false): EntityServiceAbstract
    {
        $this->getEntityManager()->merge($entity);
        if ($flush) $this->flush($entity);

        return $this;
    }

    /**
     * Flush persisted entities to the database.
     *
     * @param EntityInterface $entity
     * @throws \Doctrine\ORM\ORMException
     * 
     * @return EntityServiceAbstract
     */
    public function flush(EntityInterface $entity = null): EntityServiceAbstract
    {
        $this->getEntityManager()->flush($entity);

        return $this;
    }


    /**
     * @param EntityInterface $entity
     * @param bool $flush If true, will flush entity to database.
     * @throws \Doctrine\ORM\ORMException
     * 
     * @return EntityServiceAbstract
     */
    public function persist(EntityInterface $entity, bool $flush = false): EntityServiceAbstract
    {
        $this->setReferenceProxies($entity);

        $this->getEntityManager()->persist($entity);
        if ($flush) $this->flush($entity);

        return $this;
    }

    /**
     * Returns all entries.
     *
     * @param array|null $columns Optional. A numerically indexed array of table columns to return.
     * @param int|null $hydrationMode Optional. The hydration mode to use when performing the query.
     * @param bool $useCache Optional. If true, use results from cache.
     *
     * @return EntityInterface[]|null
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
     */
    public function findAll(array $columns = null, int $hydrationMode = Query::HYDRATE_OBJECT, bool $useCache = true): array
    {
        return $this->getEntityRepository()->findAll($columns, $hydrationMode, $useCache);
    }

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed    $id          The identifier.
     * @param int|null $lockMode    One of the \Doctrine\DBAL\LockMode::* constants
     *                              or NULL if no specific lock mode should be used
     *                              during the search.
     * @param int|null $lockVersion The lock version.
     *
     * @return EntityInterface|null The entity instance or NULL if the entity can not be found.
     */
    public function find($id, ?int $lockMode = null, ?int $lockVersion = null)
    {
        return $this->getEntityRepository()->find($id, $lockMode, $lockVersion);
    }

    /**
     * Finds entities by a set of criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->getEntityRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getEntityRepository()->findOneBy($criteria, $orderBy);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->entityRepository;
    }

    public function getEntityName()
    {
        return get_class($this->getEntityRepository());
    }

    /**
     * Set all reference proxies.
     *
     * @param EntityInterface $entity
     * @throws \Doctrine\ORM\ORMException
     */
    private function setReferenceProxies(EntityInterface $entity)
    {
        $entityManager = $this->getEntityManager();;
        foreach($entity->referenceProxies as $referenceProxy) {
            $getter = "get{$referenceProxy}";
            $proxy = $entity->$getter();

            if ($proxy instanceof EntityInterface
                && ($proxyId = $proxy->getId())
            ) {
                $setter = "set{$referenceProxy}";
                $entity->$setter($entityManager->getReference(get_class($proxy), $proxyId));
            }
        }
    }

    /**
     * @param Form $form
     * @param bool $showAllFields
     * @return array
     */
    protected function getErrorsFromAForm(Form $form, bool $showAllFields=false): array
    {
        $errors = [];
        foreach ($form as $fieldName => $formField) {
            if (!$showAllFields && $formField->getErrors(true)->count()===0) {
                continue;
            }
            $errors[$fieldName]['errors'] = [];
            foreach ($formField->getErrors(true) as $key => $error) {
                $errors[$fieldName]['errors'][$key] = $error->getMessage();
            }
        }
        return $errors;
    }

}