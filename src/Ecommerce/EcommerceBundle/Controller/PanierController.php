<?php
namespace Ecommerce\EcommerceBundle\Controller;
use Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses;
use Ecommerce\EcommerceBundle\Form\UtilisateursAdressesType;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PanierController extends Controller
{


  public function adressesAction(Request $request){
    $utilisateur = $this->container->get('security.context') ->getToken()->getUser();
    if( is_object($utilisateur)){
      $em = $this->getDoctrine()->getManager();
      $adresses = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->adressesByUser($utilisateur->getId());
      $json = json_encode(array(
            'adresses' => $adresses,


        ));

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($json);
        return $response;

    }
}
public function adresseSuppressionAction($id){

  if($id){

      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($id);
      if($entity){
        $em->remove($entity);
        $em->flush();
        $json = json_encode(array(
              'deletedadresse' => $id,
          ));
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($json);
        return $response;

      }
  }

}
    public function ajouterAdresseAction(  ){

      $entity = new UtilisateursAdresses();
      $utilisateur = $this->get('security.context')->getToken()->getUser();
      $form = $this->createFormAjoutAdresse($entity);
      $request = $this->getRequest();




      if($request->getMethod() == 'POST' && $request->isXmlHttpRequest()){
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
          $entity->setUtilisateur($utilisateur);
          $em = $this->getDoctrine()->getManager();
          $em->persist($entity);
          $em->flush();
          $newEntity = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->getNewAdresse($entity->getId());

          $json = json_encode(array(
                'newadresse' => $newEntity ,


            ));

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($json);
            return $response;
      }else{
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent('form is invalid');
        return $response;

      }
     
      }








  }

  private function createFormAjoutAdresse(UtilisateursAdresses $entity){
    $em = $this->getDoctrine()->getManager();
    $form = $this->createForm(new UtilisateursAdressesType($em), $entity,
            array(
        'action' => $this->generateUrl('ajoutadresses'),
        'method' => 'POST',
    ));

    return $form;
  }
  public function livraisonAction( ){

      $em = $this->getDoctrine()->getManager();

      $entity = new UtilisateursAdresses();
      $form = $this->createFormAjoutAdresse($entity);





      return $this->render('EcommerceBundle:Default:panier/layout/livraison.html.twig', array('form' => $form->createView()));
  }

    public function menuAction(){
      $session = $this->getRequest()->getSession();
      $articles  = 0;
      if($session->has('panier')){
        $articles = count($session->get('panier'));
      }


      return $this->render('EcommerceBundle:Default:panier/modulesUsed/panier.html.twig',
      array(

        'articles' => $articles
    ));



    }
    public function ajouterAction($id){
      $session = $this->getRequest()->getSession();
      $qte =   $this->getRequest()->query->get('qte');

      if(!$session->has('panier')) {$session->set('panier',array());}
      $panier = $session->get('panier');

      if(array_key_exists($id,$panier)){

          if($qte != null){

              $panier[$id] =  $this->getRequest()->query->get('qte');
              $session->getFlashBag()->add('success','quantité modifiée avec succès !');

          }

      }else{
        if($qte !=null){
          $panier[$id] =  $this->getRequest()->query->get('qte');
        }else{
            $panier[$id] = 1;

        }
        $session->getFlashBag()->add('success','produit ajouté au panier avec succès !');

      }
       $session->set('panier',$panier);


      return $this->redirect($this->generateUrl('panier'));

    }

    public function supprimerAction($id){
        $session = $this->getRequest()->getSession();
        $panier = $session->get('panier');

        if($panier){
          if(array_key_exists($id,$panier)){
            unset($panier[$id]);
            $session->set('panier',$panier);
            $session->getFlashBag()->add('success','article supprimé');
          }
        }



        return $this->redirect($this->generateUrl('panier'));


    }
    public function panierAction()
    {
        $session = $this->getRequest()->getSession();
        if(!$session->has('panier')) {$session->set('panier',array());}
        $panier = $session->get('panier');


        $em = $this->getDoctrine()->getManager();
        $produits_array_ids = array_keys($panier);
        $produits = $em->getRepository('EcommerceBundle:Produits')->findArray($produits_array_ids);
        return $this->render('EcommerceBundle:Default:panier/layout/panier.html.twig',
        array(
          'produits'=>$produits,
          'panier' => $panier
      ));
    }


private function setLivraisonOnSession(){
        $request = $this->getRequest();
        $session = $request->getSession();
        $adresse = false;

        if(!$session->has('adresse')){

          $session->set('adresse',array());
        }

        $adresse =  $session->get('adresse');

        if($request->getMethod()== 'POST'){

          $adresse['livraison'] = $request->request->get('livraison');
          $adresse['facturation'] = $request->request->get('facturation');


        }else{
          $session->getFlashBag()->add('success','Vueillez valider vos adresses de livraion et de facturation');
          return $this->redirect($this->generateUrl('livraison'));
        }


          $session->set('adresse',$adresse);
          return $this->redirect($this->generateUrl('validation'));



      }

    public function validationAction()
    {     $request = $this->getRequest();





          if($request->getMethod() == 'POST'){
                $this->setLivraisonOnSession();
                $em = $this->getDoctrine()->getManager();
                $prepareCommande = $this->forward('EcommerceBundle:Commandes:prepareCommande');
                $commande = $em->getRepository('EcommerceBundle:Commandes')->find($prepareCommande->getContent());


                return $this->render('EcommerceBundle:Default:panier/layout/validation.html.twig',
                  array(
                    'commande' => $commande

                  )

              );
            }



    }


    public function validationCommandeAction($id){
      $em = $this->getDoctrine()->getManager();
      $request = $this->getRequest();
      $utilisateur = $this->container->get('security.context')->getToken()->getUser();

      $commande = false;

      if($request->getMethod() == 'POST'){
        if($id){
          $commande = $em->getRepository('EcommerceBundle:Commandes')->find($id);
          if($commande){
            if($commande->getUtilisateur()->getId()== $utilisateur->getId() && $commande->getValider()!=1){

              $commande->setValider(1);
              $commande->setReference($this->container->get('setNewReference')->reference());
              $em->persist($commande);
              $em->flush();

              $session = $request->getSession();
              $session->remove('panier');
              $session->remove('adresse');
              $session->remove('commande');

              $session->getFlashBag()->add('success','Votre commande a été validée');
              return $this->redirect($this->generateUrl('facture'));



            }else{
              throw $this->createNotFoundException('Cette page n\'existe pas');

            }
          }else{
            throw $this->createNotFoundException('Cette page n\'existe pas');

          }

        }else{
          throw $this->createNotFoundException('Cette page n\'existe pas');
        }

      }else{
        throw $this->createNotFoundException('Cette page n\'existe pas');

      }

    }
}
