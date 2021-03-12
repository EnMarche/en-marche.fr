<?php

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Form\DoubleNewlineTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TerritorialCouncilCandidacyType extends AbstractType
{
    public function getParent()
    {
        return BaseCandidacyBiographyType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('biography', DoubleNewlineTextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 500],
                'constraints' => [new NotBlank(), new Length(['max' => 500])],
                'filter_emojis' => true,
            ])
            ->add('faithStatement', DoubleNewlineTextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 2000],
                'constraints' => [new NotBlank(), new Length(['max' => 2000])],
                'filter_emojis' => true,
            ])
            ->add('isPublicFaithStatement', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Candidacy::class,
            ])
        ;
    }
}
