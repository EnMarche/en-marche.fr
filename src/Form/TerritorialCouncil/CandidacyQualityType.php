<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidacyQualityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Candidacy $candidacy */
        $candidacy = $builder->getData();

        $builder
            ->add('quality', ChoiceType::class, [
                'choices' => array_combine($options['qualities'], $options['qualities']),
                'choice_label' => function (string $choice) {
                    return 'territorial_council.membership.quality.'.$choice;
                },
            ])
            ->add('invitations', CollectionType::class, [
                'entry_type' => CandidacyInvitationType::class,
                'allow_add' => true,
                'entry_options' => [
                    'label' => false,
                    'validation_groups' => [DesignationTypeEnum::COPOL === $candidacy->getElection()->getDesignationType() ? 'copol_election' : 'Default'],
                ],
            ])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Candidacy $model */
                $model = $event->getData();

                if (($invitation = $model->getFirstInvitation()) && !$invitation->getMembership() && $model->isCouncilor()) {
                    $model->removeInvitation($invitation);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'allow_extra_fields' => true,
                'data_class' => Candidacy::class,
                'validation_groups' => ['Default', 'invitation_edit'],
            ])
            ->setRequired('qualities')
            ->setAllowedTypes('qualities', 'array')
        ;
    }
}
