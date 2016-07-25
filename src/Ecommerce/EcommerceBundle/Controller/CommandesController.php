<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ecommerce\EcommerceBundle\Entity\Commandes;
use Ecommerce\EcommerceBundle\Entity\Produits;
use Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses;

class CommandesController extends Controller
{

      public function prepareCommandeAction(){
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();
        $commande = new Commandes();

        if($session->has('commande'))
          $commande = $em->getRepository('EcommerceBundle:Commandes')->find($session->get('commande'));



        $commande->setDate(new \DateTime());
        $commande->setUtilisateur($this->container->get('security.context')->getToken()->getUser());
        $commande->setValider(0);
        $commande->setReference(0);
        $commande->setCommande($this->facture());

        if(!$session->has('commande')){
          $em->persist($commande);
          $session->set('commande',$commande);
        }

        $em->flush();

        return new Response($commande->getId());
      }




      public function facture(){

        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();
        $generator = $this->container->get('security.secure_random');
        $adresse = $session->get('adresse');
        $panier = $session->get('panier');
        $totalHT = 0;
        $totalTTC = 0;
        $commande = array();
        $facturation = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['facturation']);
        $livraison = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['livraison']);
        $produits = $em->getRepository('EcommerceBundle:Produits')->findArray(array_keys($panier));

        foreach($produits as $produit){
              $prixHT = $produit->getPrix()*$panier[$produit->getId()];
              $prixTTC = $prixHT/$produit->getTva()->getMultiplicate();

              $totalHT += $prixHT;
              $totalTTC += $prixTTC;
              if(!isset($commande['tva']['%'.$produit->getTva()->getValeur()]))
                $commande['tva']['%'.$produit->getTva()->getValeur()] = round($prixTTC-$prixHT,2);

              else
                $commande['tva']['%'.$produit->getTva()->getValeur()] += round($prixTTC-$prixHT,2);


              $commande['produits'][$produit->getId()] = array(
                'id' => $produit->getId(),
                'reference' => $produit->getNom(),
                'quantite' => $panier[$produit->getId()],
                'prixHT' => round($produit->getPrix(),2),
                'prixTTC' => round($produit->getPrix()/$produit->getTva()->getMultiplicate(),2)
              );
           }// end foreach
              $commande['facturation'] =  array(
                'prenom' => $facturation->getPrenom(),
                'nom' => $facturation->getNom(),
                'telephone' => $facturation->getTelephone(),
                'adresse' =>$facturation->getAdresse(),
                'cp' =>$facturation->getCp(),
                'ville' =>$facturation->getVille(),
                'pays' =>$facturation->getPays(),
                'complement' =>$facturation->getComplement(),
              );

              $commande['livraison'] =  array(
                'prenom' => $livraison->getPrenom(),
                'nom' => $livraison->getNom(),
                'telephone' => $livraison->getTelephone(),
                'adresse' =>$livraison->getAdresse(),
                'cp' =>$livraison->getCp(),
                'ville' =>$livraison->getVille(),
                'pays' =>$livraison->getPays(),
                'complement' =>$livraison->getComplement(),
              );

              $commande['prixHT'] = round($totalHT,2);
              $commande['prixTTC'] = round($totalTTC,2);
              $commande['token'] = bin2hex($generator->nextBytes(20));



        return $commande;

      }
}
