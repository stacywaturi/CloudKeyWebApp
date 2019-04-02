<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/05
 * Time: 08:39
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class CertificateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('additional', RadioType::class, array('required' => false))
            ->add('common_name', TextType::class, array('required' => false))
            ->add('organization', TextType::class, array('required' => false))
            ->add('organization_unit', TextType::class, array('required' => false))
            ->add('country', TextType::class, array('required' => false))
            ->add('state', TextType::class, array('required' => false))
            ->add('advanced', RadioType::class, array('required' => false))
            ->add('key_type', TextType::class, array('required' => false))
            ->add('key_size', TextType::class, array('required' => false));
    }

}