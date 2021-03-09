<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\CandidacyInvitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidacyInvitationType extends AbstractType
{
    public function getParent()
    {
        return CollectionType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('membership', CandidacyInvitedMembershipType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CandidacyInvitation::class,
        ]);
    }
}
