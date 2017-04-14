<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Adherent;
use AppBundle\Form\ActivityPositionType;
use AppBundle\Form\GenderType;
use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', null, [
                    'label' => 'Téléphone',
                ])
                ->add('birthdate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('position', null, [
                    'label' => 'Statut',
                ])
            ->end()
            ->with('Référent', ['class' => 'col-md-3'])
                ->add('isReferent', 'boolean', [
                    'label' => 'Est référent ?',
                ])
                ->add('managedAreaCodesAsString', null, [
                    'label' => 'Codes des zones gérés',
                ])
                ->add('managedAreaMarkerLatitude', null, [
                    'label' => 'Latitude du point sur la carte',
                ])
                ->add('managedAreaMarkerLongitude', null, [
                    'label' => 'Longitude du point sur la carte',
                ])
            ->end()
            ->with('Responsable procuration', ['class' => 'col-md-3'])
                ->add('isProcurationManager', 'boolean', [
                    'label' => 'Est responsable procuration ?',
                ])
                ->add('procurationManagedAreaCodesAsString', null, [
                    'label' => 'Codes des zones gérés',
                ])
            ->end()
            ->with('Candidat législatives', ['class' => 'col-md-3'])
                ->add('isLegislativeCandidate', 'boolean', [
                    'label' => 'Est candidat aux législatives ?',
                ])
                ->add('legislativeCandidate', null, [
                    'label' => 'Nom de la circonscription',
                ])
            ->end()
            ->with('Compte', ['class' => 'col-md-6'])
                ->add('status', null, [
                    'label' => 'Etat du compte',
                ])
                ->add('hasSubscribedMainEmails', 'boolean', [
                    'label' => 'Abonné aux mails nationaux ?',
                ])
                ->add('hasSubscribedReferentsEmails', 'boolean', [
                    'label' => 'Abonné aux mails de référents ?',
                ])
                ->add('hasSubscribedLocalHostEmails', 'boolean', [
                    'label' => 'Abonné aux mails de comités ?',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
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
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'required' => false,
                ])
                ->add('birthdate', 'sonata_type_date_picker', [
                    'label' => 'Date de naissance',
                    'required' => false,
                ])
                ->add('position', ActivityPositionType::class, [
                    'label' => 'Statut',
                ])
            ->end()
            ->with('Compte', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Etat du compte',
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ])
                ->add('hasSubscribedMainEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails nationaux ?',
                    'required' => false,
                ])
                ->add('hasSubscribedReferentsEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails de référents ?',
                    'required' => false,
                ])
                ->add('hasSubscribedLocalHostEmails', CheckboxType::class, [
                    'label' => 'Abonné aux mails de comités ?',
                    'required' => false,
                ])
            ->end()
            ->with('Référent', ['class' => 'col-md-3'])
                ->add('managedArea.codesAsString', TextType::class, [
                    'label' => 'Codes des zones gérés',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas référent. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                ])
                ->add('managedArea.markerLatitude', TextType::class, [
                    'label' => 'Latitude du point sur la carte des référents',
                    'required' => false,
                ])
                ->add('managedArea.markerLongitude', TextType::class, [
                    'label' => 'Longitude du point sur la carte des référents',
                    'required' => false,
                ])
            ->end()
            ->with('Responsable procuration', ['class' => 'col-md-3'])
                ->add('procurationManagedAreaCodesAsString', TextType::class, [
                    'label' => 'Codes des zones gérés',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas responsable procuration. '.
                        'Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.',
                ])
            ->end()
            ->with('Candidat aux législatives', ['class' => 'col-md-3'])
                ->add('legislativeCandidate', TextType::class, [
                    'label' => 'Nom de la circonscription',
                    'required' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas candidat aux législatives.',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
                'field_type' => EmailType::class,
            ])
            ->add('registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('postalCode', 'doctrine_orm_callback', [
                'label' => 'Code postal',
                'field_type' => TextType::class,
                'callback' => function ($qb, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /* @var QueryBuilder $qb */
                    $qb->andWhere(sprintf('%s.postAddress.postalCode', $alias).' LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function ($qb, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /* @var QueryBuilder $qb */
                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
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
                'callback' => function ($qb, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /* @var QueryBuilder $qb */
                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('referent', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'N\'afficher que les référents',
                'field_type' => CheckboxType::class,
                'callback' => function ($qb, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /* @var QueryBuilder $qb */
                    $qb->andWhere(sprintf('%s.managedArea.codes', $alias).' IS NOT NULL');

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent_phone.html.twig',
            ])
            ->add('postAddress.postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
            ])
            ->add('postAddress.country', null, [
                'label' => 'Pays',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/adherent_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent_actions.html.twig',
            ])
        ;
    }
}
