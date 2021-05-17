<?php

namespace App\Form\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isEdition = $options['edit'];
        $isNotifiable = $options['is_notifiable'];

        $builder
            ->add('title', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('text', TextareaType::class, [
                'filter_emojis' => true,
            ])
            ->add('externalLink', UrlType::class, [
                'required' => false,
            ])
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choices' => $options['zones'],
                'disabled' => $isEdition,
            ])
        ;
        if ($isNotifiable) {
            $builder
                ->add('notification', CheckboxType::class, [
                    'required' => false,
                    'disabled' => $isEdition,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['zones', 'edit', 'is_notifiable'])
            ->setAllowedTypes('zones', [Zone::class.'[]'])
            ->setAllowedTypes('edit', 'bool')
            ->setAllowedTypes('is_notifiable', 'bool')
            ->setDefaults([
                'data_class' => News::class,
                'zones' => [],
                'edit' => false,
                'is_notifiable' => true,
            ])
        ;
    }
}
