<?php

/**
 * Les méthodes sont :
 * - findNotCorbeille() : On récupère les catégories qui n'ont pas le statut "Corbeille"
 * - findPublishOrderByAlpha() : On récupère les catégories publiées rangées par ordre alphabétique.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PostCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostCategory>
 *
 * @method PostCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCategory[]    findAll()
 * @method PostCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostCategory::class);
    }

    public function save(PostCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PostCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * On récupère les catégories qui n'ont pas le statut "Corbeille".
     *
     * @return PostCategory[] Returns an array of PostCategory objects
     */
    public function findNotCorbeille()
    {
        return $this->createQueryBuilder('c')
            ->where('c.status != :corbeille_id')
            ->setParameter('corbeille_id', 4) // TODO: mettre l'uuid
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * On récupère les catégories publiées rangées par ordre alphabétique.
     *
     * @return PostCategory[] Returns an array of PostCategory objects
     */
    public function findPublishOrderByAlpha()
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :publish')
            ->setParameter('publish', 1) // TODO: mettre l'uuid
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return PostCategory[] Returns an array of PostCategory objects
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

//    public function findOneBySomeField($value): ?PostCategory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
