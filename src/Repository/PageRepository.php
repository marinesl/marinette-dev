<?php

/**
 * Les méthodes sont :
 * - findNotCorbeille() : On récupère les pages qui n'ont pas le statut "Corbeille"
 * - findAllQuery() : findAll() method but return query.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function save(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * On récupère les pages qui n'ont pas le statut "Corbeille".
     *
     * @return Page[] Returns an array of Page objects
     */
    public function findNotCorbeille()
    {
        return $this->createQueryBuilder('p')
            ->where('p.status != :corbeille_id')
            ->setParameter('corbeille_id', 4) // TODO: mettre l'uuid
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * findAll() method but return query.
     *
     * @return Page[] Returns an array of Page objects
     */
    public function findAllQuery()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :publish')
            ->setParameter('publish', 1)
        ;
    }

//    /**
//     * @return Page[] Returns an array of Page objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Page
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
