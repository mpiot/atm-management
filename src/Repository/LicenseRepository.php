<?php

/*
 * Copyright 2020 Mathieu Piot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Repository;

use App\Entity\License;
use App\Entity\Season;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use function Symfony\Component\String\u;

/**
 * @method License|null find($id, $lockMode = null, $lockVersion = null)
 * @method License|null findOneBy(array $criteria, array $orderBy = null)
 * @method License[]    findAll()
 * @method License[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenseRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, License::class);
        $this->paginator = $paginator;
    }

    public function findLastUserSeason(User $user)
    {
        $query = $this->createQueryBuilder('license')
            ->innerJoin('license.user', 'user')
            ->innerJoin('license.seasonCategory', 'seasonCategory')->addSelect('seasonCategory')
            ->innerJoin('seasonCategory.season', 'season')->addSelect('season')
            ->where('user = :user')
            ->orderBy('season.name', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function findBySeasonPaginated(Season $season, $query = null, $page = 1): PaginationInterface
    {
        $qb = $this->createQueryBuilder('license')
            ->addSelect('(
                    CASE WHEN JSON_GET_TEXT(license.marking, \'medical_certificate_validated\') = \'1\' AND JSON_GET_TEXT(license.marking, \'wait_payment_validation\') = \'1\' THEN 2
                         WHEN JSON_GET_TEXT(license.marking, \'payment_validated\') = \'1\' AND JSON_GET_TEXT(license.marking, \'wait_medical_certificate_validation\') = \'1\' THEN 3
                         WHEN JSON_GET_TEXT(license.marking, \'medical_certificate_validated\') = \'1\' AND JSON_GET_TEXT(license.marking, \'payment_validated\') = \'1\' THEN 4
                         WHEN JSON_GET_TEXT(license.marking, \'validated\') = \'1\' THEN 5
                         ELSE 1
                    END) AS HIDDEN mainSort'
            )
            ->innerJoin('license.user', 'user')->addSelect('user')
            ->innerJoin('license.medicalCertificate', 'medical_certificate')->addSelect('medical_certificate')
            ->innerJoin('license.seasonCategory', 'season_category')->addSelect('season_category')
            ->innerJoin('season_category.season', 'season')
            ->where('season = :season')
            ->addOrderBy('mainSort', 'ASC')
            ->addOrderBy('user.firstName', 'ASC')
            ->addOrderBy('user.lastName', 'ASC')
            ->setParameter('season', $season)
        ;

        if ($query) {
            $qb
                ->andWhere('LOWER(user.firstName) LIKE :query OR LOWER(user.lastName) LIKE :query OR LOWER(user.email) LIKE :query')
                ->setParameter('query', '%'.u($query)->lower()->toString().'%')
            ;
        }

        return $this->paginator->paginate(
            $qb->getQuery(),
            $page,
            License::NUM_ITEMS
        );
    }
}
