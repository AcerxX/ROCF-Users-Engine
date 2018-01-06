<?php
/**
 * Created by PhpStorm.
 * User: alexa
 * Date: 07.01.2018
 * Time: 00:32
 */

namespace App\Form\Request;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginRequest extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                array(
                    'required' => true,
                    'label' => 'email'
                )
            )
            ->add(
                'password',
                PasswordType::class,
                array(
                    'required' => true,
                    'label' => 'password'
                )
            )
            ->add(
                'locale',
                TextType::class,
                array(
                    'required' => true,
                    'label' => 'locale'
                )
            )
            ->add(
                'ipAddress',
                TextType::class,
                array(
                    'required' => true,
                    'label' => 'ipAddress'
                )
            );
    }

    public function getName()
    {
        return 'login';
    }
}