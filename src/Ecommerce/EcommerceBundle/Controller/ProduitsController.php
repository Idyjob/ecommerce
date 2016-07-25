<?php
namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ecommerce\EcommerceBundle\Repository\ProduitsRepository;
use Ecommerce\EcommerceBundle\Form\RechercheType;
use Ecommerce\EcommerceBundle\Entity\Categories;

class ProduitsController extends Controller
{


    public function produitsAction(Categories $categorie = null)
    {

        $session = $this->getRequest()->getSession();
        $panier = false;
        $em = $this->getDoctrine()->getManager();
        $produits = false;



        if($session->has('panier')){
          $panier = $session->get('panier');
        }


      //  $categorie = $em->getRepository('EcommerceBundle:Categories')->find($categorie);

        if($categorie != null){
          $produitsfound = $em->getRepository('EcommerceBundle:Produits')->byCategorie($categorie);

        }else{
          $produitsfound = $em->getRepository('EcommerceBundle:Produits')->findBy(array('disponible' => 1));
        }
        $request = $this->getRequest();
        $produits  = $this->get('knp_paginator')->paginate(

              $produitsfound, /* query NOT result */
              $request->query->getInt('page', 1)/*page number*/,
              6/*limit per page*/
            );
        return $this->render('EcommerceBundle:Default:produits/layout/produits.html.twig',
        array('produits'=>$produits,

              'panier' => $panier
      ));
    }

    public function presentationAction($slug){
      $session = $this->getRequest()->getSession();
      $panier = false;

      if($session->has('panier')){
        $panier = $session->get('panier');
      }
      $em = $this->getDoctrine()->getManager();
      $produit = $em->getRepository('EcommerceBundle:Produits')->findOneBySlug($slug);
      if(!$produit){
        throw $this->createNotFoundException('Cette page n\'existe pas');
      }
      return $this->render('EcommerceBundle:Default:produits/layout/presentation.html.twig',
      array('produit'=>$produit,
            'panier' => $panier
    ));
    }

public function rechercheAction(){
  $form= $this->createForm(new RechercheType());
  return $this->render('EcommerceBundle:Default:recherche/modulesUsed/recherche.html.twig',array('form'=>$form->createView()) );


}

public function rechercheTraitementAction( ){
  $form = $this->createForm(new RechercheType());
  $request = $this->getRequest();
  if($request->getMethod() =='POST'){
     $form->bind($this->get('request'));

      $chaine= $form['recherche']->getData();
      $em = $this->getDoctrine()->getManager();
      $produitsfound = $em->getRepository('EcommerceBundle:Produits')->recherche($form['recherche']->getData());
      $produits  = $this->get('knp_paginator')->paginate(

            $produitsfound, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            6/*limit per page*/
          );

      return $this->render('EcommerceBundle:Default:produits/layout/produits.html.twig', array('produits'=>$produits));




  }else{


      throw $this->createNotFoundException('Cette page n\'existe pas');

  }



}


}
