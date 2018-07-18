<?php
// src/AppBundle/Form/file.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ModifierInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder->add('age');
      $builder->add('race');
      $builder->add('famille');
      $builder->add('nourriture');
    }

    public function getParent()
    {
      return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getName()
    {
        return 'fos_user_profile';
    }
}
