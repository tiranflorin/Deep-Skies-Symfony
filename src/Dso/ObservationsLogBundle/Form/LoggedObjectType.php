<?php

namespace Dso\ObservationsLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoggedObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objId')
            ->add('userId')
            ->add('listId')
            ->add('comment')
            ->add('createdAt')
            ->add('obsList')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dso\ObservationsLogBundle\Entity\LoggedObject'
        ));
    }

    public function getName()
    {
        return 'dso_observationslogbundle_loggedobject';
    }
}
