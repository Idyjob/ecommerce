<?php
namespace Ecommerce\EcommerceBundle\Twig\Extension;



class MontantTvaExtension  extends \Twig_Extension{

  public function getFilters(){

    return array(new \Twig_SimpleFilter('montantTva', array($this,'montantTva')));
  }

  public function montantTva($prixHt,$tva){


    return round(($prixHt/ $tva) - $prixHt,2);

  }
  public function getName(){

    return 'montant_tva_extension';
  }



}
