<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/05
 * Time: 10:11
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class RenewCertificate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('additional', RadioType::class, array('required' => false))
            ->add('advanced', RadioType::class, array('required' => false));
    }

}