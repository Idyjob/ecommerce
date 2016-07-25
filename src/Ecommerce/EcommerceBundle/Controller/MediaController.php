<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MediaController extends Controller
{


    public function newAction(){

      
    }

    public function uploadTemplateAction(){

        return $this->render('EcommerceBundle:Default:media/upload_template.html.twig');

    }
}
