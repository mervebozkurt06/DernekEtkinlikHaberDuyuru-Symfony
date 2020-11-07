<?php

namespace App\Form;

use App\Entity\Content;
use App\Entity\Image;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('image', FileType::class,[
                'label'=> 'Content Gallery Image',
                'mapped'=>false,
                'required'=> false,
                'constraints'=>[
                    new File([
                        'maxSize'=>'4096k',
                        'mimeTypes'=>[
                            'image/*', //All image types
                        ],
                        'mimeTypesMessage'=>'Please Upload a Valid Image File',
                    ])
                ],
            ])

            ->add('content',EntityType::class,[ //content ve image ilişkisi
                'class'=>Content::class,
                'choice_label'=>'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'csrf_protection'=>false,
        ]);
    }
}
