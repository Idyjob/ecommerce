<?php

namespace Ecommerce\EcommerceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitsFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('nom', 'filter_text')
            ->add('description', 'filter_text')
            ->add('prix', 'filter_text')
            ->add('disponible', 'filter_boolean')
          //  ->add('images', 'filter_entity', array('class' => 'Ecommerce\EcommerceBundle\Entity\Media'))
            ->add('categorie', 'filter_entity', array('class' => 'Ecommerce\EcommerceBundle\Entity\Categories'))
            ->add('tva', 'filter_entity', array('class' => 'Ecommerce\EcommerceBundle\Entity\Tva'))
            
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'Ecommerce\EcommerceBundle\Entity\Produits',
            'csrf_protection'   => false,
            'validation_groups' => array('filter'),
            'method'            => 'GET',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'produits_filter';
    }
}
