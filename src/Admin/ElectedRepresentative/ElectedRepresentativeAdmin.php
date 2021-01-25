<?php

namespace App\Admin\ElectedRepresentative;

use App\Address\Address;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\ElectedRepresentative\ElectedRepresentativeMandatesOrderer;
use App\ElectedRepresentative\UserListDefinitionHistoryManager;
use App\Election\VoteListNuanceEnum;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\LabelNameEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use App\Form\AdherentEmailType;
use App\Form\ElectedRepresentative\SponsorshipType;
use App\Form\GenderType;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\CollectionType as SonataCollectionType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ElectedRepresentativeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    private $dispatcher;
    private $userListDefinitionHistoryManager;

    /**
     * @var UserListDefinition[]|array
     */
    private $userListDefinitionsBeforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
        UserListDefinitionHistoryManager $userListDefinitionHistoryManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->userListDefinitionHistoryManager = $userListDefinitionHistoryManager;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('show')
            ->remove('create')
            ->remove('delete')
        ;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();
        $alias = $query->getRootAlias();

        $query
            ->leftJoin("$alias.mandates", 'mandate')
            ->leftJoin("$alias.politicalFunctions", 'politicalFunction')
            ->leftJoin("$alias.labels", 'label')
        ;

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('currentMandates', null, [
                'label' => 'Mandats actuels (nuance politique)',
                'template' => 'admin/elected_representative/list_mandates.html.twig',
            ])
            ->add('currentZones', null, [
                'label' => 'Périmètre(s) géographique(s)',
                'template' => 'admin/elected_representative/list_zones.html.twig',
            ])
            ->add('currentPoliticalFunctions', null, [
                'label' => 'Fonctions actuelles',
                'template' => 'admin/elected_representative/list_political_functions.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Qualifications',
                'template' => 'admin/elected_representative/list_type.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/elected_representative/list_actions.html.twig',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('emailAddress', EmailType::class, [
                    'mapped' => false,
                    'label' => 'Adresse e-mail de l\'adhérent',
                    'template' => 'admin/elected_representative/show_email.html.twig',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre e-mail de contact',
                ])
                ->add('phone', null, [
                    'mapped' => false,
                    'label' => 'Téléphone',
                    'template' => 'admin/elected_representative/show_phone.html.twig',
                ])
                ->add('contactPhone', null, [
                    'label' => 'Autre téléphone de contact',
                    'template' => 'admin/elected_representative/show_contact_phone.html.twig',
                ])
                ->add('birthDate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'label' => 'Lieu de naissance',
                ])
                ->add('hasFollowedTraining', null, [
                    'label' => 'Formation Tous Politiques !',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                    'placeholder' => 'common.gender.unknown',
                    'required' => false,
                    'choices' => [
                        'common.gender.woman' => Genders::FEMALE,
                        'common.gender.man' => Genders::MALE,
                    ],
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('adherent', AdherentEmailType::class, [
                    'required' => false,
                    'label' => 'Adresse e-mail',
                    'help' => 'Attention, changer l\'e-mail ici fera que l\'élu sera associé à un autre compte adhérent.'
                        .' Si vous souhaitez ajouter un autre mail de contact, faites-le ci-dessous.',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre e-mail de contact',
                    'required' => false,
                ])
                ->add('contactPhone', PhoneNumberType::class, [
                    'required' => false,
                    'label' => 'Autre téléphone de contact',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'default_region' => Address::FRANCE,
                    'preferred_country_choices' => [Address::FRANCE],
                    'attr' => ['class' => 'phone'],
                ])
                ->add('birthDate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'required' => false,
                    'label' => 'Lieu de naissance',
                ])
                ->add('userListDefinitions', null, [
                    'label' => 'Labels',
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('uld')
                            ->andWhere('uld.type IN (:type)')
                            ->setParameter('type', [
                                UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
                                UserListDefinitionEnum::TYPE_LRE,
                            ])
                            ->orderBy('uld.label', 'ASC')
                        ;
                    },
                ])
                ->add('hasFollowedTraining', null, [
                    'label' => 'Formation Tous Politiques !',
                ])
            ->end()
            ->with(
                'Réseaux sociaux',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('socialNetworkLinks', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.social_network_link',
                ])
            ->end()
            ->with(
                'Étiquettes',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('labels', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.label',
                ])
            ->end()
            ->with(
                'Parrainages',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('sponsorships', CollectionType::class, [
                    'entry_type' => SponsorshipType::class,
                    'label' => false,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Mandats')
                ->add('mandates', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.mandate',
                ])
            ->end()
            ->with('Fonctions')
                ->add('politicalFunctions', SonataCollectionType::class, [
                    'by_reference' => false,
                    'label' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'admin_code' => 'app.admin.elected_representative.political_function',
                ])
            ->end()
        ;

        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function submit(FormEvent $event): void
    {
        /** @var ElectedRepresentative $electedRepresentative */
        $electedRepresentative = $event->getData();

        // change mandates order
        if (!$electedRepresentative->getMandates()->isEmpty()) {
            ElectedRepresentativeMandatesOrderer::updateOrder($electedRepresentative->getMandates());
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('mandatesType', CallbackFilter::class, [
                'label' => 'Mandats',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => MandateTypeEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $mandate) {
                        $where->add("mandate.type = :mandate_$mandate");
                        $qb->setParameter("mandate_$mandate", $mandate);
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('mandatesOnGoing', CallbackFilter::class, [
                'label' => 'Mandat en cours ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    switch ($value['value']) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere('mandate.onGoing = true');

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere('mandate.onGoing = false');

                            break;
                    }

                    return true;
                },
            ])
            ->add('politicalFunctionsName', CallbackFilter::class, [
                'label' => 'Fonctions',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => PoliticalFunctionNameEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $politicalFunctions) {
                        $where->add("politicalFunction.name = :function_$politicalFunctions");
                        $qb->setParameter("function_$politicalFunctions", $politicalFunctions);
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('politicalFunctionsOnGoing', CallbackFilter::class, [
                'label' => 'Fonction en cours ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    switch ($value['value']) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere('politicalFunction.onGoing = true');

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere('politicalFunction.onGoing = false');

                            break;
                    }

                    return true;
                },
            ])
            ->add('mandates.politicalAffiliation', CallbackFilter::class, [
                'label' => 'Nuance politique',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => VoteListNuanceEnum::toArray(),
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $politicalAffiliation) {
                        $where->add("$alias.politicalAffiliation = :pa_".$politicalAffiliation);
                        $qb->setParameter('pa_'.$politicalAffiliation, $politicalAffiliation);
                    }

                    $qb->andWhere($where);
                    $qb->andWhere("$alias.onGoing = true");

                    return true;
                },
            ])
            ->add('mandates.geoZone', ZoneAutocompleteFilter::class, [
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
            ])
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return 'global.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'yes':
                            $qb->andWhere("$alias.adherent IS NOT NULL");

                            return true;
                        case 'no':
                            $qb->andWhere("$alias.adherent IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('userListDefinitions', ModelAutocompleteFilter::class, [
                'label' => 'Labels',
                'show_filter' => true,
                'field_options' => [
                    'minimum_input_length' => 0,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'label',
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $qb = $datagrid->getQuery();
                        $alias = $qb->getRootAlias();
                        $qb
                            ->andWhere($alias.'.type IN (:type)')
                            ->setParameter('type', [
                                UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
                                UserListDefinitionEnum::TYPE_LRE,
                            ])
                            ->orderBy($alias.'.label', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
            ])
            ->add('labelsName', CallbackFilter::class, [
                'label' => 'Étiquettes',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => LabelNameEnum::ALL,
                    'multiple' => true,
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere('label.name IN (:label_names)');
                    $qb->setParameter('label_names', $value['value']);

                    return true;
                },
            ])
        ;
    }

    /**
     * @param ElectedRepresentative $subject
     */
    public function setSubject($subject)
    {
        if (null === $this->userListDefinitionsBeforeUpdate) {
            $this->userListDefinitionsBeforeUpdate = $subject->getUserListDefinitions()->toArray();

            $this->dispatcher->dispatch(new ElectedRepresentativeEvent($subject), ElectedRepresentativeEvents::BEFORE_UPDATE);
        }

        parent::setSubject($subject);
    }

    /**
     * @param ElectedRepresentative $object
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);

        $this->userListDefinitionHistoryManager->handleChanges($object, $this->userListDefinitionsBeforeUpdate);
    }

    /**
     * @param ElectedRepresentative $object
     */
    public function postUpdate($object)
    {
        parent::postUpdate($object);

        $this->dispatcher->dispatch(new ElectedRepresentativeEvent($object), ElectedRepresentativeEvents::POST_UPDATE);
    }

    public function getExportFields()
    {
        return [
            'Nom' => 'lastName',
            'Prénom' => 'firstName',
            'Mandats actuels (nuance politique)' => 'exportMandates',
            'Fonctions actuelles' => 'exportPoliticalFunctions',
            'Adhérent' => 'exportIsAdherent',
        ];
    }

    public function getExportFormats()
    {
        return ['csv', 'xls'];
    }
}
