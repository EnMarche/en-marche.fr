<?php

namespace App\Admin\ThematicCommunity;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ThematicCommunity\AdherentMembership;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Entity\ThematicCommunity\ThematicCommunityToUserListDefinitionEnum;
use App\Entity\UserListDefinition;
use App\Form\GenderType;
use App\Form\UnitedNationsCountryType;
use App\Intl\UnitedNationsBundle;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ThematicCommunityMembershipAdmin extends AbstractAdmin
{
    public $otherMemberships = [];

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('firstname', null, [
                'label' => 'Prénom',
            ])
            ->add('lastname', null, [
                'label' => 'Nom',
            ])
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent/list_phone.html.twig',
            ])
            ->add('cityWithZipcode', null, [
                'label' => 'Ville',
            ])
            ->add('community', null, [
                'label' => 'Communauté',
                'template' => 'admin/thematic_community/member/community.html.twig',
            ])
            ->add('userListDefinitions', null, [
                'label' => 'Catégories',
                'template' => 'admin/thematic_community/member/categories.html.twig',
            ])
            ->add('joinedAt', null, [
                'label' => 'Membre depuis le',
            ])
            ->add('qualificatons', null, [
                'label' => 'Qualifications',
                'template' => 'admin/thematic_community/member/qualifications.html.twig',
            ])
            ->add('association', null, [
                'label' => 'Avec Association ?',
            ])
            ->add('job', null, [
                'label' => 'Métier',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->setOtherMemberships($formMapper->getFormBuilder()->getDataClass());

        $isContactMembership = ContactMembership::class === $formMapper->getFormBuilder()->getDataClass();

        $formMapper
            ->with('Membre', ['class' => 'col-md-6'])
                ->add('firstname', TextType::class, [
                    'label' => 'Prénom',
                    'disabled' => !$isContactMembership,
                ])
                ->add('lastname', TextType::class, [
                    'label' => 'Nom',
                    'disabled' => !$isContactMembership,
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                    'disabled' => !$isContactMembership,
                ])
                ->add('customGender', TextType::class, [
                    'required' => false,
                    'label' => 'Personnalisez votre genre',
                    'disabled' => !$isContactMembership,
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'disabled' => !$isContactMembership,
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'disabled' => !$isContactMembership,
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                    'disabled' => !$isContactMembership,
                ])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Adresse',
                    'disabled' => !$isContactMembership,
                ])
            ->end()
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('userListDefinitions', EntityType::class, [
                    'label' => 'Catégories',
                    'class' => UserListDefinition::class,
                    'multiple' => true,
                    'expanded' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('uld')
                            ->where('uld.type = :type')
                            ->setParameter('type', ThematicCommunityToUserListDefinitionEnum::MAP[$this->getSubject()->getCommunity()->getName()] ?? null)
                        ;
                    },
                ])
                ->add('hasJob', ChoiceType::class, [
                    'label' => 'Métier en lien avec la communauté ?',
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('job', TextType::class, [
                    'label' => 'Métier ?',
                    'required' => false,
                ])
                ->add('association', ChoiceType::class, [
                    'label' => 'Membre d\'une association',
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('associationName', TextType::class, [
                    'label' => 'Nom de l\'association',
                    'required' => false,
                ])
                ->add('motivations', ChoiceType::class, [
                    'label' => 'Modes d\'engagement',
                    'expanded' => false,
                    'multiple' => true,
                    'placeholder' => '-- Choisir un mode d\'engagement --',
                    'choices' => ThematicCommunityMembership::MOTIVATIONS,
                    'choice_label' => static function ($choice) {
                        return 'admin.thematic_community.membership.motivations.'.$choice;
                    },
                    'required' => false,
                ])
                ->add('expert', ChoiceType::class, [
                    'label' => 'Expert ?',
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('type', CallbackFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ThematicCommunityMembership::TYPES,
                    'choice_label' => function ($choice) {
                        return 'admin.thematic_community_membership.type.'.$choice;
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    // no need to perform complex query if all types have been selected
                    if (\count($value['value']) === \count(ThematicCommunityMembership::TYPES)) {
                        return true;
                    }

                    $or = new Orx();

                    if (\in_array(ThematicCommunityMembership::TYPE_ADHERENT, $value['value'], true)) {
                        $qb
                            ->leftJoin("$alias.adherent", 'a')
                            ->leftJoin(ElectedRepresentative::class, 'e', Join::WITH, 'e.adherent = a')
                        ;
                        $or->add("e IS NULL AND $alias.contact IS NULL");
                    }

                    if (\in_array(ThematicCommunityMembership::TYPE_ELECTED_REPRESENTATIVE, $value['value'], true)) {
                        if (!\in_array('a', $qb->getAllAliases(), true)) {
                            $qb
                                ->leftJoin("$alias.adherent", 'a')
                                ->leftJoin(ElectedRepresentative::class, 'e', Join::WITH, 'e.adherent = a')
                            ;
                        }
                        $or->add("e IS NOT NULL AND $alias.contact IS NULL");
                    }

                    if (\in_array(ThematicCommunityMembership::TYPE_CONTACT, $value['value'], true)) {
                        $or->add("$alias.contact IS NOT NULL");
                    }

                    $qb->orWhere($or);

                    return true;
                },
            ])
            ->add('community', CallbackFilter::class, [
                'label' => 'Communauté',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => ThematicCommunity::class,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('tc')
                            ->where('tc.enabled = true')
                        ;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->andWhere("$alias.community IN (:value)")
                        ->setParameter('value', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('firstName', CallbackFilter::class, [
                'label' => 'Prénom',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("ILIKE(adherent.$field, :value) = true OR ILIKE(contact.$field, :value) = true")
                        ->setParameter('value', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('lastName', CallbackFilter::class, [
                'label' => 'Nom',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("ILIKE(adherent.$field, :value) = true OR ILIKE(contact.$field, :value) = true")
                        ->setParameter('value', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('emailAddress', CallbackFilter::class, [
                'label' => 'Email',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("adherent.$field = :value OR contact.email = :value")
                        ->setParameter('value', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('joinedAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('cityName', CallbackFilter::class, [
                'label' => 'Ville',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("LOWER(adherent.postAddress.$field) LIKE :value OR LOWER(contact.postAddress.$field) LIKE :value")
                        ->setParameter('value', '%'.mb_strtolower($value['value']).'%')
                    ;

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(UnitedNationsBundle::getCountries()),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.contact", 'contact')
                        ->leftJoin("$alias.adherent", 'adherent')
                        ->andWhere("LOWER(adherent.postAddress.$field) LIKE :value OR LOWER(contact.postAddress.$field) LIKE :value")
                        ->setParameter('value', '%'.mb_strtolower($value['value']).'%')
                    ;

                    return true;
                },
            ])
            ->add('expert', null, [
                'label' => 'Expert ?',
                'show_filter' => true,
            ])
            ->add('association', null, [
                'label' => 'Avec association ?',
                'show_filter' => true,
            ])
            ->add('with_job', CallbackFilter::class, [
                'label' => 'Avec métier ?',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Non' => 0,
                        'Oui' => 1,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (0 !== $value['value'] && 1 !== $value['value']) {
                        return false;
                    }

                    $qb->andWhere("$alias.hasJob = :with_job")
                        ->setParameter('with_job', (bool) $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('motivation', CallbackFilter::class, [
                'label' => 'Motivation',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => ThematicCommunityMembership::MOTIVATIONS,
                    'choice_label' => static function ($choice) {
                        return 'admin.thematic_community.membership.motivations.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $or = new Orx();
                    foreach ($value['value'] as $i => $motivation) {
                        $or->add(":motivation_$i = ANY_OF(string_to_array($alias.motivations, ','))");
                        $qb->setParameter(sprintf('motivation_%d', $i), mb_strtolower($motivation));
                    }
                    $qb->andWhere($or);

                    return true;
                },
            ])
        ;
    }

    public function toString($object)
    {
        return sprintf('Adhésion de %s à la communauté %s', $object->getFirstName().' '.$object->getLastName(), $object->getCommunity()->getName());
    }

    private function setOtherMemberships(string $class): void
    {
        switch ($class) {
            case AdherentMembership::class:
                $field = ThematicCommunityMembership::TYPE_ADHERENT;
                $object = $this->getSubject()->getAdherent();
                break;
            case ContactMembership::class:
                $field = ThematicCommunityMembership::TYPE_CONTACT;
                $object = $this->getSubject()->getContact();
                break;
            default:
                throw new \LogicException(sprintf('Class "%s" is not a valid membership class.', $class));
        }

        $this->otherMemberships = array_filter(
            $this->getModelManager()->findBy($class, [$field => $object]),
            function (ThematicCommunityMembership $membership) {
                return $membership !== $this->getSubject();
            }
        );
    }
}
