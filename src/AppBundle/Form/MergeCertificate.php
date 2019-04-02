<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/05
 * Time: 09:28
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;use Symfony\Component\OptionsResolver\OptionsResolver;

class MergeCertificate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
//            ->add('name', TextType::class)
            ->add('attachment', TextType::class);
    }
}