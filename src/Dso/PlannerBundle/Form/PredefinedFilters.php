<?php

namespace Dso\PlannerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PredefinedFilters extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filter_type', 'hidden', array('data' => 'predefined'));
        $builder->add('naked_eye', 'submit', array('attr' => array('class' => 'btn btn-success')));
        $builder->add('binoculars', 'submit', array('attr' => array('class' => 'btn btn-success')));
        $builder->add('small_telescope', 'submit', array('attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'predefinedfilters';
    }
}