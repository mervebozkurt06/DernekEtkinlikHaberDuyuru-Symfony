<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Content;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class Content1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'class'=> Category::class,
                'choice_label'=>'title',
            ])

            ->add('title')
            ->add('keywords')
            ->add('description')
            ->add('detail',CKEditorType::class,array(
                'config' =>array(
                    'uiColor' => '#ffffff',
                ),
            ))

            ->add('image', FileType::class, [
                'label'=>'Content Main Image',
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[
                    new File([
                        'maxSize'=> '1024k',
                        'mimeTypes'=>[
                            'image/*', //all image types
                        ],
                        'mimeTypesMessage'=>'Please upload a valid Image File'
                    ])
                ],
            ])

            ->add('type',ChoiceType::class, [
                'choices'  => [
                    'Haberler'  => 'Haberler',
                    'Duyurular' => 'Duyurular',
                    'Etkinlik'  => 'Etkinlik'

                ],
            ])
            ;
            /*->add('created_at')
            ->add('updated_at')*/

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}
