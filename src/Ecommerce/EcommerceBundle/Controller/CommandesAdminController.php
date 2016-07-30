<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class CommandesAdminController extends Controller
{

      public function commandesAction(){

        $em = $this->getDoctrine()->getManager();
        $commandes = $em->getRepository('EcommerceBundle:Commandes')->findAll();
        return $this->render('EcommerceBundle:Administration:commandes/commandes.html.twig',array('commandes'=>$commandes));


      }

      public function showFacturePDFAction($id){


                $em = $this->getDoctrine()->getManager();
                $facture = $em->getRepository('EcommerceBundle:Commandes')->find($id);
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

                return new Response($html2pdf->Output($facture->getUtilisateur()->getUsername().'_facture_'.$facture->getReference().'.pdf'), 200, array('Content-Type' => 'application/pdf'));

      }





}
