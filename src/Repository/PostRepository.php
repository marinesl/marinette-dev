<?php

/**
 * Les méthodes sont :
 * - findNotCorbeille() : On récupère les posts qui n'ont pas le statut "Corbeille"
 * - findLast10Edited() : On récupère les 10 derniers posts édités
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * On récupère les posts qui n'ont pas le statut "Corbeille"
     */
    public function findNotCorbeille()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status != :corbeille_id')
            ->setParameter('corbeille_id', 4)  // TODO: mettre l'uuid
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * On récupère les 10 derniers posts édités
     */
    public function findLast10Edited()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status != :corbeille_id')
            ->setParameter('corbeille_id', 4)  // TODO: mettre l'uuid
            ->orderBy('p.edited_at', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Post[] Returns an array of Post objects
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

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}