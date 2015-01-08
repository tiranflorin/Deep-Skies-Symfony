<?php

namespace Dso\PlannerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FeedbackForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('attr' => array('class' => 'form-control')));
        $builder->add('email', 'email', array('attr' => array('class' => 'form-control')));
        $builder->add('message', 'textarea', array('attr' => array('class' => 'form-control')));
        $builder->add('Send', 'submit', array('attr' => array('class' => 'btn btn-primary btn-block')));
    }

    public function getName()
    {
        return 'feedback';
    }
}