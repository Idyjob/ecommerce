<?php

namespace Ecommerce\EcommerceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ecommerce\EcommerceBundle\Form\Type\MediaType;

class ProduitsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('disponible')
            ->add('categorie','entity',array('class'=>'Ecommerce\EcommerceBundle\Entity\Categories','attr'=>array('class'=>'chosen','multiple'=>false)))
            ->add('tva','entity',array('class'=>'Ecommerce\EcommerceBundle\Entity\Tva','attr'=>array('class'=>'chosen','multiple'=>false)))

            ->add('images', 'collection', array(
            'type' => new MediaType(),

            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference'=> false,
            'attr'=>array('class'=>'well')
        ))

       

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
             'data_class' => 'Ecommerce\EcommerceBundle\Entity\Produits',

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'produits';
    }
}
