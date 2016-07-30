<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UploadController extends Controller
{


    public function newAction(){


    }

    public function uploadAction(){

        return $this->render('EcommerceBundle:Default:upload.html.twig');

    }
}
