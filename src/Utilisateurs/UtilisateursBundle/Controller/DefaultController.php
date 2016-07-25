<?php

namespace Utilisateurs\UtilisateursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function flashMessagesAction( )
    {
        return $this->render('UtilisateursBundle:Default:flashMessages.html.twig');
    }
}
