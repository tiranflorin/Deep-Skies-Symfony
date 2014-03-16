<?php

namespace Dso\PlannerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PredefinedFilters extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filter_type', 'hidden', array('data' => 'predefined'));
        $builder->add('naked_eye', 'submit');
        $builder->add('binoculars', 'submit');
        $builder->add('small_telescope', 'submit');
    }

    public function getName()
    {
        return 'predefinedfilters';
    }
}