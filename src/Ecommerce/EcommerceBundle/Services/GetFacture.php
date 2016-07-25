<?php
namespace Ecommerce\EcommerceBundle\Services;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;


class GetFacture{

  public function __construct(  ContainerInterface $container){

       $this->container = $container;

  }

public function facture($facture){

  //on stocke la vue à convertir en PDF, en n'oubliant pas les paramètres twig si la vue comporte des données dynamiques
  $html = $this->container->get('templating')->render('UtilisateursBundle:Default:facturePDF.html.twig', array('facture' => $facture));


  $html2pdf = $this->container->get('html2pdf_factory')->create();

  $html2pdf->pdf->SetDisplayMode('real');
  $html2pdf->pdf->SetTitle('Dramanable Ecommerce Facture');
  $html2pdf->pdf->SetAuthor('Dramanable');

  $html2pdf->writeHTML($html);

  return new Response($html2pdf->Output($facture->getReference().'.pdf'), 200, array('Content-Type' => 'application/pdf'));



}







}
