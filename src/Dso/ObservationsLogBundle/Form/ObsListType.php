<?php

namespace Dso\ObservationsLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObsListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('equipment')
            ->add('conditions')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dso\ObservationsLogBundle\Entity\ObsList'
        ));
    }

    public function getName()
    {
        return 'dso_observationslogbundle_obslist';
    }
}
