<?php

declare(strict_types=1);

namespace PrestaShop\Module\ProductLabel\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PrestaShop\Module\ProductLabel\Entity\ProductLabel;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface as PrestaSearchCriteriaInterface;

class ProductLabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductLabel::class);
    }


    public function findByProductId(int $productId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.products', 'p')
            ->where('p.id = :id')
            ->setParameter('id', $productId)
            ->getQuery()
            ->getResult();
    }

    public function finaAllWithProducts(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.products', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }

    public function findForGrid(PrestaSearchCriteriaInterface $searchCriteria): array
    {
        $qb =  $this->createQueryBuilder('l');

        $orderBy = $searchCriteria->getOrderBy();
        $sortOrder = $searchCriteria->getOrderWay();
        $filters = $searchCriteria->getFilters();

        if (!empty($orderBy)) {
            $qb->orderBy('l.' . $orderBy, $sortOrder);
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('l.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        $qb->setFirstResult($searchCriteria->getOffset());
        $qb->setMaxResults($searchCriteria->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function deleteExistingConnectionsForProduct(int $productId): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $conn->executeStatement('DELETE FROM product_label_product WHERE product_id = :id', [
            'id' => $productId,
        ]);
    }

    public function addNewConnectionsForProduct(array $labelIds, int $productId): void 
    {
        if (empty($labelIds)) {
            return;
        }

        $conn = $this->getEntityManager()->getConnection();

        $values = [];
        $params = [];

        foreach (array_values($labelIds) as $index => $labelId) {
            $values[] = "(:lid{$index}, :pid{$index})";
            $params["lid{$index}"] = $labelId;
            $params["pid{$index}"] = $productId;
        }

        $sql = 'INSERT INTO product_label_product (label_id, product_id) VALUES ' . implode(', ', $values);
        $conn->executeStatement($sql, $params);
    }

    public function countAll(PrestaSearchCriteriaInterface $searchCriteria): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');

        $filters = $searchCriteria->getFilters();
        if (!empty($filters['name'])) {
            $qb->andWhere('l.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        return (int) $qb->getQuery()
            ->getSingleScalarResult();
    }
}
