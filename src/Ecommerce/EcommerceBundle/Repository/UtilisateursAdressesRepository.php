<?php

namespace Ecommerce\EcommerceBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UtiisateursAdressesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UtilisateursAdressesRepository extends EntityRepository
{

  public function adressesByUser($utilisateur){

    $qb = $this->createQueryBuilder('u')
      ->select('u')
      ->where('u.utilisateur = :utilisateur')

      ->orderBy('u.id')
      ->setParameter('utilisateur',$utilisateur);
      return $qb->getQuery()->getArrayResult();
  }


  public function getNewAdresse($id){
    $qb = $this->createQueryBuilder('u')
      ->select('u')
      ->where('u.id = :id')
      ->setParameter('id',$id);
      return $qb->getQuery()->getArrayResult();

  }
}
