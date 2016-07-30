<?php

namespace Utilisateurs\UtilisateursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Utilisateurs\UtilisateursBundle\Repository\VillesRepository;

class UtilisateursController extends Controller
{

    public function allCodePostalAction(){

      $em = $this->getDoctrine()->getManager();
      $villes = $em->getRepository('UtilisateursBundle:Villes')->getVilles();
      $response = new JsonResponse();
      $cp =  array();

      foreach ($villes as $ville) {
       //$cp[$ville->getVilleId()] = $ville->getVilleCodePostal();

       array_push($cp, $ville->getVilleNom()  );
      }

      $response->setData(array('codepostaux' => $cp));
      return $response;

    }
    public function villesAction($cp){

      $em = $this->getDoctrine()->getManager();
      $villes = $em->getRepository('UtilisateursBundle:Villes')->findBy(array('villeCodePostal' =>$cp));
      $villesByCp = array();
      if(count($villes) > 0){
        foreach($villes as $ville){
          $villesByCp[] = $ville->getVilleNom();

        }
      }else{
        $villesByCp = null;
      }
      $response = new JsonResponse();

      $response->setData(array('villes' => $villesByCp));
      return $response;

    }
     public function factureAction(){

       $em = $this->getDoctrine()->getManager();
       $utilisateur = $this->container->get('security.context')->getToken()->getUser();
       $factures = $em->getRepository('EcommerceBundle:Commandes')->byFacture($utilisateur);
       return $this->render('UtilisateursBundle:Default:utilisateurs/layout/facture.html.twig',
       array('factures'=>$factures));
     }

     public function facturePDFAction($id){

       $em = $this->getDoctrine()->getManager();
       $utilisateur = $this->container->get('security.context')->getToken()->getUser();
       $facture = $em->getRepository('EcommerceBundle:Commandes')->findOneBy(
               array(
                 'id' => $id,
                 'valider' => 1,
                 'utilisateur' => $utilisateur
               )
        );

        if(!$facture){
          throw $this->createNotFoundException('Cette page n\'existe pas');
        }
        //on stocke la vue à convertir en PDF, en n'oubliant pas les paramètres twig si la vue comporte des données dynamiques
        $html = $this->renderView('UtilisateursBundle:Default:facturePDF.html.twig', array('facture' => $facture));


        $html2pdf = $this->get('html2pdf_factory')->create();

        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->pdf->SetTitle('Dramanable Ecommerce Facture');
        $html2pdf->pdf->SetAuthor('Dramanable');

        $html2pdf->writeHTML($html);

        return new Response($html2pdf->Output($utilisateur->getUsername().'_facture_'.$facture->getReference().'.pdf'), 200, array('Content-Type' => 'application/pdf'));

        //$this->container->get('setNewFacture')->facture($facture);

     }


}
