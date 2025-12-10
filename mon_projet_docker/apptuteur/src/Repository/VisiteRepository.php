<?php

namespace App\Repository;

use App\Entity\Visite;
use App\Entity\Etudiant;
use App\Entity\Tuteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visite>
 */
class VisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visite::class);
    }

    /**
     * Return visits for a student filtered by statut and ordered by date.
     */
    public function findForEtudiantWithFilters(Etudiant $etudiant, ?string $statut, string $direction = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.etudiant = :etudiant')
            ->setParameter('etudiant', $etudiant)
            ->orderBy('v.date', strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');

        if ($statut) {
            $qb->andWhere('v.statut = :statut')
                ->setParameter('statut', $statut);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Return upcoming visits for a tuteur ordered by date ascending.
     */
    public function findUpcomingForTuteur(Tuteur $tuteur, int $limit = 5): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.tuteur = :tuteur')
            ->andWhere('v.date >= :now')
            ->setParameter('tuteur', $tuteur)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('v.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Visite[] Returns an array of Visite objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Visite
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
