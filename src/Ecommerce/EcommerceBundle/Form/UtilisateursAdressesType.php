<?php

namespace Ecommerce\EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UtilisateursAdressesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
     private $em;
     public function __construct($em){
       $this->em =$em;

     }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom','text', array('attr'=>array('class'=>'form-control')))
            ->add('prenom','text', array('attr'=>array('class'=>'form-control')))
            ->add('telephone','text', array('attr'=>array('class'=>'form-control')))
            ->add('adresse','text', array('attr'=>array('class'=>'form-control')))

            ->add('cp','text', array('attr'=>array('class'=>'form-control cp ', 'maxlength' =>5)))
          //  ->add('ville','choice', array('attr'=>array('class'=>'form-control ville'),'choices'=>array('class'=>'Utilisateurs\UtilisateursBundle\Entity\Villes')))
          ->add('ville','text'


            )

            ->add('complement',null, array('attr'=>array('class'=>'form-control')))
            ->add('pays','text', array('attr'=>array('class'=>'form-control')))
          //  ->add('utilisateur')
        ;
        $city = function(FormInterface $form, $cp){
          $villes = $this->em->getRepository('UtilisateursBundle:Villes')->findBy(array('villeCodePostal' =>$cp));
          $villesByCp = array();
          if(count($villes) > 0){
            foreach($villes as $ville){
            $villesByCp[] = $ville;
            // $villesByCp[] =(string) $ville->getVilleNom();

            }
          }else{
            $villesByCp = null;
          }

          $form->add('ville','choice', array('choices'=>$villesByCp,'attr'=>array('class'=>'form-control ville')));
        };

          $builder->get('cp')->addEventListener(FormEvents::POST_SUBMIT,function(FormEvent $event) use ($city){
          $city($event->getForm()->getParent(),$event->getForm()->getData());
        });


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ecommerce_ecommercebundle_utilisateursadresses';
    }
}
