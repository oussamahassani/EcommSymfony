<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'required' => true,
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The new password and confirmation password do not match.',
                'required' => true,
                'first_options'  => [
                    'label' => 'New Password',
                    'constraints' => [
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password must be at least {{ limit }} characters long',
                        ]),
                    ],
                ],
                'second_options' => ['label' => 'Confirm New Password'],
                'options' => ['attr' => ['class' => 'form-control']],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}