<?php

namespace App\Admin\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use App\Form\TerritorialCouncil\TerritorialCouncilQualityChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TerritorialCouncilMembershipLogAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('adherent', ModelAutocompleteFilter::class, [
                'show_filter' => true,
                'label' => 'Adhérent',
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ],
            ])
            ->add('isResolved', null, [
                'show_filter' => true,
                'label' => 'Résolu',
            ])
            ->add('qualityName', null, [
                'show_filter' => true,
                'label' => 'Qualité',
                'field_type' => TerritorialCouncilQualityChoiceType::class,
            ])
            ->add('type', null, [
                'show_filter' => true,
                'label' => 'Type',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => TerritorialCouncilMembershipLog::ALL_TYPES,
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
            ])
            ->add('description', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Description',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('ILIKE(%s.description, :text) = true', $alias));
                    $qb->setParameter('text', '%'.mb_strtolower($value['value']).'%');

                    return true;
                },
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/territorial_council/list_type.html.twig',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/territorial_council/list_adherent.html.twig',
            ])
            ->add('qualityName', null, [
                'label' => 'Qualité',
                'template' => 'admin/territorial_council/list_quality_name.html.twig',
            ])
            ->add('actualTerritorialCouncil', null, [
                'label' => 'CoTerr actuel',
            ])
            ->add('actualQualityNames', null, [
                'label' => 'Qualités actuelles',
                'template' => 'admin/territorial_council/list_qualities.html.twig',
            ])
            ->add('foundTerritorialCouncils', null, [
                'label' => 'CoTerr trouvé(s)',
                'template' => 'admin/territorial_council/list_territorial_councils.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('isResolved', null, [
                'label' => 'Résolu',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/territorial_council/membership_log_list_actions.html.twig',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('isResolved', ChoiceType::class, [
                'required' => true,
                'label' => 'Résolu',
                'choices' => [
                    'global.yes' => true,
                    'global.no' => false,
                ],
            ])
        ;
    }
}
