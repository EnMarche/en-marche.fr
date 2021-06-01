<?php

namespace App\Form\Admin;

use App\VotingPlatform\Designation\DesignationZoneEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignationZoneType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => array_combine(DesignationZoneEnum::toArray(), DesignationZoneEnum::toArray()),
            'choice_label' => static function (string $type) {
                return 'voting_platform.designation.zone_'.mb_strtolower($type);
            },
        ]);
    }
}
