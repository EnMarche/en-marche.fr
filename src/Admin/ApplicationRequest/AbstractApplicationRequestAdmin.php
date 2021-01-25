<?php

namespace App\Admin\ApplicationRequest;

use App\Form\GenderType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class AbstractApplicationRequestAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('gender', ChoiceFilter::class, [
                'label' => 'Genre',
                'show_filter' => true,
                'field_type' => GenderType::class,
                'field_options' => [
                    'placeholder' => '',
                ],
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('favoriteCities', CallbackFilter::class, [
                'label' => 'Ville choisie (code INSEE)',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere(":inseeCode = ANY_OF(string_to_array($alias.favoriteCities, ','))");
                    $qb->setParameter('inseeCode', $value['value']);

                    return true;
                },
            ])
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    switch ($value['value']) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere("$alias.adherent IS NOT NULL");

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere("$alias.adherent IS NULL");

                            break;
                    }

                    return true;
                },
            ])
            ->add('displayed', BooleanFilter::class, [
                'label' => 'Est affiché ?',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de candidature',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('gender', 'trans', [
                'label' => 'Genre',
                'format' => 'common.gender.%s',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'E-mail',
            ])
            ->add('favoriteCities', null, [
                'label' => 'Ville(s) choisie(s)',
                'template' => 'admin/application_request/show_favorite_cities.html.twig',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adhérent',
            ])
            ->add('displayed', 'boolean', [
                'label' => 'Affiché',
                'editable' => true,
            ])
            ->add('createdAt', null, [
                'label' => 'Candidaté le',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations personnelles')
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'E-mail',
                ])
                ->add('address', null, [
                    'label' => 'Adresse',
                ])
                ->add('postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('city', null, [
                    'label' => 'Code INSEE',
                ])
                ->add('cityName', null, [
                    'label' => 'Ville',
                ])
                ->add('country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                ])
                ->add('displayed', CheckboxType::class, [
                    'label' => 'Affiché',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    public function getExportFields()
    {
        return [
            'UUID' => 'uuid',
            'Genre' => 'gender',
            'Prénom' => 'firstName',
            'Nom' => 'lastName',
            'Email' => 'emailAddress',
            'Ville(s) demandée(s)' => 'getFavoriteCitiesAsString',
            'Téléphone' => 'phone',
            'Adresse' => 'address',
            'Code postal' => 'postalCode',
            'Ville' => 'cityName',
            'Pays' => 'country',
            'Est adhérent ?' => 'isAdherent',
            'Est affiché ?' => 'displayed',
        ];
    }
}
