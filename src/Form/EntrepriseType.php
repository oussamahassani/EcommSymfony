<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrepriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('company_name', TextType::class, ['label' => 'Company Name'])
        ->add('tax_code', TextType::class, ['label' => 'Tax Code'])
        ->add('field', TextType::class, ['label' => 'Field',])
        ->add('email', EmailType::class, ['label' => 'Email'])
        ->add('phone', IntegerType::class, ['label' => 'Phone'])
        ->add('password', TextType::class, ['label' => 'Password'])
        ->add('supplier', CheckboxType::class, ['label' => 'Supplier','required' => false,])
        ->add('submit', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
