<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/*
    Login form provides admin accounts authentificator
*/

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('username', TextType::class, [
            'label' => false,
            'attr' => [
                'class' => 'text-input',
                'placeholder' => 'Username',
            ],
            'mapped' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a username',
                ])
            ],
            'translation_domain' => false
        ])
        ->add('password', PasswordType::class, [
            'label' => false,
            'attr' => [
                'class' => 'text-input',
                'placeholder' => 'Password',
            ],
            'mapped' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ])
            ],
            'translation_domain' => false
        ])
        ->add('remember', CheckboxType::class, [
            'label' => 'Remember me',
            'attr' => [
                'class' => 'checkbox',
            ],
            'mapped' => false,
            'required' => false,
            'translation_domain' => false
        ])  
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
