<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\ListMapper, Form\FormMapper
};
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProposalThemeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    protected function configureFormFields(FormMapper $formMapper)
        : void
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('color', null, [
                'label' => 'Couleur de fond',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('color', null, [
                'label' => 'Couleur de fond',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
