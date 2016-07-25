<?php

namespace Ecommerce\EcommerceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('name', 'filter_text')
            //->add('path', 'filter_text')
            ->add('updatedAt', 'filter_date',array(
    'widget' => 'single_text',

    // do not render as type="date", to avoid HTML5 date pickers
    'html5' => false,

    // add a class that can be selected in JavaScript
    'attr' => ['class' => 'dateField']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'Ecommerce\EcommerceBundle\Entity\Media',
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
        return 'media_filter';
    }
}
