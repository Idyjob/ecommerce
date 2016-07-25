<?php
namespace Ecommerce\EcommerceBundle\Listener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class RedirectionListener{

  public function __construct(ContainerInterface $container, Session $session){
    $this->session = $session;
    $this->router = $container->get('router');
    $this->securityContext = $container->get('security.context');
  }

  public function  onKernelRequest(GetResponseEvent $event){
    $route = $event->getRequest()->attributes->get('_route');

    if($route && ($route == 'validation' || $route == 'livraison')){

      if(!is_object($this->securityContext->getToken()->getUser())){
        $this->session->getFlashBag()->add('warning','vous devez vous authentifier');
        $event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
      }else{
        if(!$this->session->has('panier') || count($this->session->get('panier')) == 0 || count($this->session->get('panier')) == null){
          $this->session->getFlashBag()->add('success','votre panier est vide');
          $event->setResponse(new RedirectResponse($this->router->generate('panier')));
        }
      }

    }

  }







}
