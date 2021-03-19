<?php

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use App\Form\CroppedImageType;
use App\Form\DoubleNewlineTextareaType;
use Imagine\Filter\Basic\Crop;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseCandidacyBiographyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', CroppedImageType::class, ['label' => false])
            ->add('biography', DoubleNewlineTextareaType::class, [
                'required' => false,
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
                'filter_emojis' => true,
            ])
            ->add('save', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['skip'])) {
                unset($data['croppedImage'], $data['biography'], $data['image']);
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseCandidacy::class,
        ]);
    }
}
