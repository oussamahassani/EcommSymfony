<?php
namespace App\Form;
use App\Entity\Entreprise;
use App\Entity\Role;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileEntrepriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'required' => true,
            ])
            ->add('field', TextType::class, [
                'label' => 'Field',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Profile Image (leave empty if you do not want to change it)',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPEG or PNG file',
                    ]),
                ],
                'mapped' => false,
            ])
            ->add('addressStreet', TextType::class, [
                'label' => 'Street',
                'required' => true,
                'property_path' => 'address.street',
            ])
            ->add('addressCity', TextType::class, [
                'label' => 'City',
                'required' => true,
                'property_path' => 'address.city',
            ])
            ->add('addressPostalCode', TextType::class, [
                'label' => 'Postal Code',
                'required' => true,
                'property_path' => 'address.postalCode',
            ])
            ->add('addressCountry', TextType::class, [
                'label' => 'Country',
                'required' => true,
                'property_path' => 'address.country',
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}