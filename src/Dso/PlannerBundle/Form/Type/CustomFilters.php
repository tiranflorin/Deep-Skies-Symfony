<?php

namespace Dso\PlannerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomFilters extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filter_type', 'hidden', array('data' => 'custom'));
        $builder->add('constellation', 'text', array('attr' => array('class' => 'form-control')));
        $builder->add('min_mag', 'text', array('attr' => array('class' => 'form-control')));
        $builder->add('max_mag', 'text', array('attr' => array('class' => 'form-control')));
        $builder->add('obj_type', 'text', array('attr' => array('class' => 'form-control')));
        $builder->add('filter', 'submit', array('attr' => array('class' => 'btn btn-primary')));
    }

    public function getName()
    {
        return 'customfilters';
    }
}
