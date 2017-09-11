<?php

namespace AppBundle\Admin;

use AppBundle\Intl\UnitedNationsBundle;
use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\DatagridMapper, Datagrid\ListMapper
};
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\{
    Datagrid\ProxyQuery, Filter\CallbackFilter, Filter\DateRangeFilter
};
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType, ChoiceType, TextType};

class SummaryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'id',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        : void
    {
        $datagridMapper
            ->add('member.id', null, [
                'label' => 'ID',
            ])
            ->add('member.lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('member.firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('member.emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('member.registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('m.postAddress.postalCode LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('LOWER(m.postAddress.cityName) LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value['value']).'%');

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(UnitedNationsBundle::getCountries()),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('LOWER(m.postAddress.country) = :country');
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('referent', CallbackFilter::class, [
                'label' => 'N\'afficher que les référents',
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->innerJoin(sprintf('%s.member', $alias), 'm');
                    $qb->andWhere('m.managedArea.codes IS NOT NULL');

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
    {
        $listMapper
            ->addIdentifier('member', null, [
                'label' => 'Membre',
            ])
            ->add('currentProfession', null, [
                'label' => 'Métier principal',
            ])
            ->add('contributionWishLabel', null, [
                'label' => 'Souhait de contribution',
            ])
            ->add('availabilities', null, [
                'label' => 'Disponibilités',
                'template' => 'admin/summary/list_availabilities.html.twig',
            ])
            ->add('contactEmail', null, [
                'label' => 'Email',
            ])
            ->add('public', null, [
                'label' => 'Visible au public',
                'template' => 'admin/summary/public_show.html.twig',
            ])
        ;
    }
}
