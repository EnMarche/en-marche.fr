<?php

namespace App\Admin;

use App\Adherent\AdherentRoleEnum;
use App\Admin\Filter\ReferentTagAutocompleteFilter;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Coordinator\CoordinatorAreaSectors;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\AdherentTag;
use App\Entity\BaseGroup;
use App\Entity\BoardMember\BoardMember;
use App\Entity\BoardMember\Role;
use App\Entity\CitizenProjectMembership;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccessEnum;
use App\Entity\SubscriptionType;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Form\ActivityPositionType;
use App\Form\Admin\AdherentTerritorialCouncilMembershipType;
use App\Form\Admin\AvailableDistrictAutocompleteType;
use App\Form\Admin\CandidateManagedAreaType;
use App\Form\Admin\CoordinatorManagedAreaType;
use App\Form\Admin\JecouteManagedAreaType;
use App\Form\Admin\LreAreaType;
use App\Form\Admin\MunicipalChiefManagedAreaType;
use App\Form\Admin\ReferentManagedAreaType;
use App\Form\Admin\SenatorAreaType;
use App\Form\Admin\SenatorialCandidateManagedAreaType;
use App\Form\EventListener\BoardMemberListener;
use App\Form\EventListener\RevokeManagedAreaSubscriber;
use App\Form\GenderType;
use App\History\EmailSubscriptionHistoryHandler;
use App\Intl\UnitedNationsBundle;
use App\Membership\Mandates;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'registeredAt',
    ];

    protected $accessMapping = [
        'ban' => 'BAN',
        'certify' => 'CERTIFY',
        'uncertify' => 'UNCERTIFY',
        'extract' => 'EXTRACT',
    ];

    protected $formOptions = [
        'validation_groups' => ['Default', 'Admin'],
    ];

    private $dispatcher;
    private $emailSubscriptionHistoryManager;
    /** @var PoliticalCommitteeManager */
    private $politicalCommitteeManager;

    /**
     * State of adherent data before update
     *
     * @var Adherent
     */
    private $beforeUpdate;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryManager,
        PoliticalCommitteeManager $politicalCommitteeManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->emailSubscriptionHistoryManager = $emailSubscriptionHistoryManager;
        $this->politicalCommitteeManager = $politicalCommitteeManager;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('ban', $this->getRouterIdParameter().'/ban')
            ->add('certify', $this->getRouterIdParameter().'/certify')
            ->add('uncertify', $this->getRouterIdParameter().'/uncertify')
            ->add('extract', 'extract')
            ->remove('create')
            ->remove('delete')
        ;
    }

    public function configureActionButtons($action, $object = null)
    {
        if (\in_array($action, ['ban', 'certify', 'uncertify'], true)) {
            $actions = parent::configureActionButtons('show', $object);
        } else {
            $actions = parent::configureActionButtons($action, $object);
        }

        if (\in_array($action, ['edit', 'show', 'ban', 'certify', 'uncertify'], true)) {
            $actions['switch_user'] = ['template' => 'admin/adherent/action_button_switch_user.html.twig'];
        }

        if (\in_array($action, ['edit', 'show'], true)) {
            if ($this->canAccessObject('ban', $object) && $this->hasRoute('ban')) {
                $actions['ban'] = ['template' => 'admin/adherent/action_button_ban.html.twig'];
            }

            if ($this->canAccessObject('certify', $object) && $this->hasRoute('certify')) {
                $actions['certify'] = ['template' => 'admin/adherent/action_button_certify.html.twig'];
            }

            if ($this->canAccessObject('uncertify', $object) && $this->hasRoute('uncertify')) {
                $actions['uncertify'] = ['template' => 'admin/adherent/action_button_uncertify.html.twig'];
            }
        }

        $actions['extract'] = ['template' => 'admin/adherent/extract/extract_button.html.twig'];

        return $actions;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('status', null, [
                    'label' => 'Etat du compte',
                ])
                ->add('tags', null, [
                    'label' => 'Tags admin',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('customGender', null, [
                    'label' => 'Personnalisez votre genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('certifiedAt', null, [
                    'label' => 'Certifié le',
                ])
                ->add('nickname', null, [
                    'label' => 'Pseudo',
                ])
                ->add('nicknameUsed', null, [
                    'label' => 'Pseudo utilisé ?',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', null, [
                    'label' => 'Téléphone',
                    'template' => 'admin/adherent/show_phone.html.twig',
                ])
                ->add('birthdate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('position', null, [
                    'label' => 'Statut',
                ])
                ->add('mandates', null, [
                    'label' => 'adherent.mandate.admin.label',
                    'template' => 'admin/adherent/show_mandates.html.twig',
                ])
                ->add('subscriptionTypes', null, [
                    'label' => 'Abonné aux notifications via e-mail et mobile',
                    'associated_property' => 'label',
                ])
            ->end()
            ->with('Responsabilités locales', ['class' => 'col-md-3'])
                ->add('isReferent', 'boolean', [
                    'label' => 'Est référent ?',
                ])
                ->add('coordinatorCommitteeArea', null, [
                    'label' => 'Coordinateur régional',
                ])
                ->add('managedArea.tags', null, [
                    'label' => 'referent.label.tags',
                ])
                ->add('procurationManagedAreaCodesAsString', null, [
                    'label' => 'Responsable procurations',
                ])
                ->add('isAssessorManager', 'boolean', [
                    'label' => 'Est responsable assesseur ?',
                ])
                ->add('assessorManagedAreaCodesAsString', null, [
                    'label' => 'Responsable assesseurs',
                ])
                ->add('coordinatorCitizenProjectArea', null, [
                    'label' => 'coordinator.label.codes.cp',
                ])
                ->add('municipalChiefManagedArea', null, [
                    'label' => 'Candidat Municipales 2020 🇫🇷',
                    'required' => false,
                ])
                ->add('isJecouteManager', 'boolean', [
                    'label' => 'Est responsable des questionnaires ?',
                ])
                ->add('jecouteManagedArea.zone', null, [
                    'label' => 'Responsable des questionnaires',
                ])
            ->end()
            ->with('Mandat électif', ['class' => 'col-md-3'])
                ->add('isDeputy', 'boolean', [
                    'label' => 'Est un(e) député(e) ?',
                ])
                ->add('managedDistrict.name', null, [
                    'label' => 'Circonscription député',
                ])
            ->end()
            ->with('Membre du Conseil', ['class' => 'col-md-6'])
                ->add('isBoardMember', 'boolean', [
                    'label' => 'Est membre du Conseil ?',
                ])
                ->add('boardMember.area', null, [
                    'label' => 'Région',
                ])
                ->add('boardMember.roles', null, [
                    'label' => 'Rôles',
                    'template' => 'admin/adherent/list_board_member_roles.html.twig',
                ])
            ->end()
            ->with('Coordinateur', ['class' => 'col-md-3'])
                ->add('isCoordinator', 'boolean', [
                    'label' => 'Est coordinateur ?',
                ])
            ->end()
            ->with('Responsable procuration', ['class' => 'col-md-3'])
                ->add('isProcurationManager', 'boolean', [
                    'label' => 'Est responsable procuration ?',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations personnelles', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Etat du compte',
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ])
                ->add('tags', ModelType::class, [
                    'label' => 'Tags admin',
                    'multiple' => true,
                    'by_reference' => false,
                    'btn_add' => false,
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('customGender', TextType::class, [
                    'required' => false,
                    'label' => 'Personnalisez votre genre',
                    'attr' => [
                        'max' => 80,
                    ],
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom',
                    'filter_emojis' => true,
                    'format_identity_case' => true,
                ])
                ->add('firstName', TextType::class, [
                    'label' => 'Prénom',
                    'filter_emojis' => true,
                    'format_identity_case' => true,
                ])
                ->add('nickname', TextType::class, [
                    'label' => 'Pseudo',
                    'filter_emojis' => true,
                    'required' => false,
                ])
                ->add('nicknameUsed', null, [
                    'label' => 'Pseudo utilisé ?',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'required' => false,
                ])
                ->add('birthdate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                    'required' => false,
                ])
                ->add('position', ActivityPositionType::class, [
                    'label' => 'Statut',
                ])
                ->add('mandates', ChoiceType::class, [
                    'label' => 'adherent.mandate.admin.label',
                    'choices' => Mandates::CHOICES,
                    'required' => false,
                    'multiple' => true,
                ])
                ->add('subscriptionTypes', null, [
                    'label' => 'Notifications via e-mail et mobile :',
                    'choice_label' => 'label',
                    'required' => false,
                    'multiple' => true,
                ])
                ->add('media', null, [
                    'label' => 'Photo',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Biographie',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
                ->add('twitterPageUrl', UrlType::class, [
                    'label' => 'Page Twitter',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://twitter.com/alexandredumoulin',
                    ],
                ])
                ->add('facebookPageUrl', UrlType::class, [
                    'label' => 'Page Facebook',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://facebook.com/alexandre-dumoulin',
                    ],
                ])
            ->end()
            ->with('Identité de l\'élu', [
                'class' => 'col-md-6',
                'description' => 'adherent.admin.elected_representative.description',
                'box_class' => 'box box-success',
            ])
                ->add('electedRepresentative', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                ])
            ->end()
            ->with('Responsabilités locales', ['class' => 'col-md-6'])
                ->add('coordinatorCommitteeArea', CoordinatorManagedAreaType::class, [
                    'label' => 'coordinator.label.codes.committee',
                    'sector' => CoordinatorAreaSectors::COMMITTEE_SECTOR,
                ])
                ->add('managedArea', ReferentManagedAreaType::class, [
                    'label' => false,
                    'required' => false,
                ])
                ->add('coordinatorCitizenProjectArea', CoordinatorManagedAreaType::class, [
                    'label' => 'coordinator.label.codes.cp',
                    'sector' => CoordinatorAreaSectors::CITIZEN_PROJECT_SECTOR,
                ])
                ->add('lreArea', LreAreaType::class, [
                    'label' => 'La république ensemble',
                    'required' => false,
                ])
                ->add('jecouteManagedArea', JecouteManagedAreaType::class, [
                    'label' => 'jecoute_manager',
                    'required' => false,
                    'help' => "Laisser vide si l'adhérent n'est pas responsable des questionnaires. Choisissez un département, un arrondissement de Paris ou une circonscription des Français établis hors de France",
                ])
                ->add('printPrivilege', null, [
                    'label' => 'Accès à "La maison des impressions"',
                    'required' => false,
                ])
            ->end()
            ->with('Élections 🇫🇷', ['class' => 'col-md-6'])
                ->add('municipalChiefManagedArea', MunicipalChiefManagedAreaType::class, [
                    'label' => 'Candidat Municipales 2020 🇫🇷',
                    'help' => <<<HELP
        Laisser vide si l'adhérent n'est pas chef municipal. 
        Utiliser les codes INSEE des villes (54402 pour NORROY-LE-SEC). <br/> 
        Utiliser <strong>75100</strong> pour la ville de Paris, 
        <strong>13200</strong> - Marseille, <strong>69380</strong> - Lyon
HELP
                    ,
                ])
                ->add('senatorialCandidateManagedArea', SenatorialCandidateManagedAreaType::class, [
                    'label' => 'Candidat Sénatoriales 2020',
                ])
                ->add('legislativeCandidateManagedDistrict', AvailableDistrictAutocompleteType::class, [
                    'label' => 'Candidat aux législatives',
                    'by_reference' => false,
                    'required' => false,
                    'help' => 'Vous pouvez choisir uniquement parmi les circonscriptions non prises',
                    'callback' => [DistrictAdmin::class, 'prepareLegislativeCandidateAutocompleteFilterCallback'],
                ])
                ->add('candidateManagedArea', CandidateManagedAreaType::class, [
                    'label' => 'Candidat',
                ])
                ->add('procurationManagedAreaCodesAsString', TextType::class, [
                    'label' => 'coordinator.label.codes',
                    'required' => false,
                    'help' => "Laisser vide si l'adhérent n'est pas responsable procuration. Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.",
                ])
                ->add('assessorManagedAreaCodesAsString', TextType::class, [
                    'label' => 'assessors_manager',
                    'required' => false,
                    'help' => "Laisser vide si l'adhérent n'est pas responsable assesseur. Utiliser les codes de pays (FR, DE, ...) ou des préfixes de codes postaux.",
                ])
                ->add('electionResultsReporter', null, [
                    'label' => 'Accès au formulaire de remontée des résultats du ministère de l\'Intérieur',
                    'required' => false,
                ])
            ->end()
            ->with('Mandat électif', ['class' => 'col-md-6'])
                ->add('managedDistrict', AvailableDistrictAutocompleteType::class, [
                    'label' => 'Circonscription député',
                    'by_reference' => false,
                    'required' => false,
                    'help' => 'Vous pouvez choisir uniquement parmi les circonscriptions non prises',
                ])
                ->add('senatorArea', SenatorAreaType::class, [
                    'required' => false,
                    'label' => 'Circonscription sénateur',
                    'help' => 'Laisser vide si l\'adhérent n\'est pas parlementaire.',
                ])
            ->end()
            ->with('Membre du Conseil territorial et CoPol', [
                'class' => 'col-md-6 territorial-council-member-info',
                'description' => 'territorial_council.admin.description',
            ])
                ->add('territorialCouncilMembership', AdherentTerritorialCouncilMembershipType::class, [
                    'label' => false,
                    'invalid_message' => 'Un adhérent ne peut être membre que d\'un seul Conseil territorial.',
                ])
            ->end()
            ->with('Membre du Conseil', ['class' => 'col-md-6'])
                ->add('boardMemberArea', ChoiceType::class, [
                    'label' => 'Région',
                    'choices' => BoardMember::AREAS_CHOICES,
                    'required' => false,
                    'mapped' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas membre du Conseil.',
                ])
                ->add('boardMemberRoles', ModelType::class, [
                    'expanded' => true,
                    'multiple' => true,
                    'btn_add' => false,
                    'class' => Role::class,
                    'mapped' => false,
                    'help' => 'Laisser vide si l\'adhérent n\'est pas membre du Conseil.',
                ])
            ->end()
            ->with('Responsable communauté thématique', ['class' => 'col-md-6'])
                ->add('handledThematicCommunities', EntityType::class, [
                    'label' => 'Communautés thématiques',
                    'class' => ThematicCommunity::class,
                    'required' => false,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('tc')
                            ->andWhere('tc.enabled = true')
                            ;
                    },
                ])
            ->end()
            ->with('Zone expérimentale 🚧', [
                'class' => 'col-md-6',
                'box_class' => 'box box-warning',
            ])
                ->add('canaryTester')
            ->end()
        ;

        $formMapper->getFormBuilder()
            ->addEventSubscriber(new BoardMemberListener())
            ->addEventSubscriber(new RevokeManagedAreaSubscriber())
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
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('certified', CallbackFilter::class, [
                'label' => 'Certifié',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return "global.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'yes':
                            $qb->andWhere("$alias.certifiedAt IS NOT NULL");

                            return true;

                        case 'no':
                            $qb->andWhere("$alias.certifiedAt IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('certifiedAt', DateRangeFilter::class, [
                'label' => 'Date de certification',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('nickname', null, [
                'label' => 'Pseudo',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('lastLoggedAt', DateRangeFilter::class, [
                'label' => 'Dernière connexion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.mb_strtolower($value['value']).'%');

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

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', mb_strtolower($value['value']));

                    return true;
                },
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags admin',
                'field_options' => [
                    'class' => AdherentTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('subscriptionTypes', ModelFilter::class, [
                'label' => 'Types de souscriptions',
                'field_options' => [
                    'class' => SubscriptionType::class,
                    'multiple' => true,
                    'choice_label' => 'label',
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('canaryTester')
            ->add('status', null, ['label' => 'Etat du compte'], ChoiceType::class, [
                'choices' => [
                    'Activé' => Adherent::ENABLED,
                    'Désactivé' => Adherent::DISABLED,
                ],
            ])
            ->add('referentTags', ModelAutocompleteFilter::class, [
                'label' => 'Tags souscrits',
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'name',
                ],
            ])
            ->add('managedArea', ReferentTagAutocompleteFilter::class, [
                'label' => 'Tags gérés',
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.$field", 'managed_area')
                        ->leftJoin('managed_area.tags', 'tags')
                        ->andWhere('tags IN (:tags)')
                        ->setParameter('tags', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('role', CallbackFilter::class, [
                'label' => 'common.role',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => AdherentRoleEnum::toArray(),
                    'choice_label' => function (string $value) {
                        return $value;
                    },
                    'multiple' => true,
                ],
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    /** @var QueryBuilder $qb */

                    // Referent
                    if (\in_array(AdherentRoleEnum::REFERENT, $value['value'], true)) {
                        $where->add(sprintf('%s.managedArea IS NOT NULL', $alias));
                    }

                    // Co-Referent
                    if (\in_array(AdherentRoleEnum::COREFERENT, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.referentTeamMember', $alias), 'rtm');
                        $where->add('rtm.id IS NOT NULL');
                    }

                    // Committee supervisor
                    if ($committeeMandates = array_intersect([AdherentRoleEnum::COMMITTEE_SUPERVISOR, AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR], $value['value'])) {
                        $qb->leftJoin(sprintf('%s.adherentMandates', $alias), 'am');
                        $provisionalCondition = '';
                        if (1 === \count($committeeMandates)
                            && \in_array(AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR, $value['value'], true)) {
                            $provisionalCondition = ' AND am.provisional = true';
                        }
                        $where->add('am.quality = :supervisor AND am.committee IS NOT NULL AND am.finishAt IS NULL'.$provisionalCondition);

                        $qb->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR);
                    }

                    // Committee host
                    if (\in_array(AdherentRoleEnum::COMMITTEE_HOST, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.memberships', $alias), 'ms');
                        $where->add('ms.privilege == :committee_privilege');
                        $qb->setParameter('committee_privilege', CommitteeMembership::COMMITTEE_HOST);
                    }

                    // Deputy
                    if (\in_array(AdherentRoleEnum::DEPUTY, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.managedDistrict', $alias), 'district');
                        $where->add('district IS NOT NULL');
                    }

                    // Senator
                    if (\in_array(AdherentRoleEnum::SENATOR, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.senatorArea', $alias), 'senatorArea');
                        $where->add('senatorArea IS NOT NULL');
                    }

                    // Board Member
                    if (\in_array(AdherentRoleEnum::BOARD_MEMBER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.boardMember', $alias), 'boardMember');
                        $where->add('boardMember IS NOT NULL');
                    }

                    // Coordinator
                    if (\in_array(AdherentRoleEnum::COORDINATOR, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.coordinatorCommitteeArea', $alias), 'coordinatorCommitteeArea');
                        $where->add('coordinatorCommitteeArea IS NOT NULL');
                    }

                    // REC
                    if (\in_array(AdherentRoleEnum::REC, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.coordinatorCitizenProjectArea', $alias), 'coordinatorCitizenProjectArea');
                        $where->add('coordinatorCitizenProjectArea IS NOT NULL');
                    }

                    // Procuration Manager
                    if (\in_array(AdherentRoleEnum::PROCURATION_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.procurationManagedArea', $alias), 'procurationManagedArea');
                        $where->add('procurationManagedArea IS NOT NULL AND procurationManagedArea.codes IS NOT NULL');
                    }

                    // Assessor Manager
                    if (\in_array(AdherentRoleEnum::ASSESSOR_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.assessorManagedArea', $alias), 'assessorManagedArea');
                        $where->add('assessorManagedArea IS NOT NULL AND assessorManagedArea.codes IS NOT NULL');
                    }

                    // Assessor
                    if (\in_array(AdherentRoleEnum::ASSESSOR, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.assessorRole', $alias), 'assessorRole');
                        $where->add('assessorRole IS NOT NULL AND assessorRole.votePlace IS NOT NULL');
                    }

                    // Municipal Manager
                    if (\in_array(AdherentRoleEnum::MUNICIPAL_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.municipalManagerRole', $alias), 'municipalManagerRole');
                        $where->add('municipalManagerRole IS NOT NULL');
                    }

                    // Municipal Manager Supervisor
                    if (\in_array(AdherentRoleEnum::MUNICIPAL_MANAGER_SUPERVISOR, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.municipalManagerSupervisorRole', $alias), 'municipalManagerSupervisorRole');
                        $where->add('municipalManagerSupervisorRole IS NOT NULL');
                    }

                    // Election results reporter
                    if (\in_array(AdherentRoleEnum::ELECTION_RESULTS_REPORTER, $value['value'], true)) {
                        $where->add(sprintf('%s.electionResultsReporter = :election_result_reporter', $alias));
                        $qb->setParameter('election_result_reporter', true);
                    }

                    // J'écoute Manager
                    if (\in_array(AdherentRoleEnum::JECOUTE_MANAGER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.jecouteManagedArea', $alias), 'jecouteManagedArea');
                        $where->add('jecouteManagedArea IS NOT NULL AND jecouteManagedArea.zone IS NOT NULL');
                    }

                    // User
                    if (\in_array(AdherentRoleEnum::USER, $value['value'], true)) {
                        $where->add(sprintf('%s.adherent = false', $alias));
                    }

                    // Citizen project holder
                    if (\in_array(AdherentRoleEnum::CITIZEN_PROJECT_HOLDER, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.citizenProjectMemberships', $alias), 'cpms');
                        $where->add('cpms.privilege = :cp_privilege');
                        $qb->setParameter('cp_privilege', CitizenProjectMembership::CITIZEN_PROJECT_ADMINISTRATOR);
                    }

                    // Municipal chief
                    if (\in_array(AdherentRoleEnum::MUNICIPAL_CHIEF, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.municipalChiefManagedArea', $alias), 'municipalChiefManagedArea');
                        $where->add('municipalChiefManagedArea IS NOT NULL');
                    }

                    // Print privilege
                    if (\in_array(AdherentRoleEnum::PRINT_PRIVILEGE, $value['value'], true)) {
                        $where->add("$alias.printPrivilege = :printPrivilege");
                        $qb->setParameter('printPrivilege', true);
                    }

                    if ($delegatedTypes = array_intersect(
                        [
                            AdherentRoleEnum::DELEGATED_REFERENT,
                            AdherentRoleEnum::DELEGATED_DEPUTY,
                            AdherentRoleEnum::DELEGATED_SENATOR,
                        ],
                        $value['value']
                    )) {
                        $qb->leftJoin(\sprintf('%s.receivedDelegatedAccesses', $alias), 'rda');
                        $where->add('rda.type IN (:delegated_types)');
                        $qb->setParameter('delegated_types', \array_map(static function ($type) {
                            return \substr($type, 10); // remove "delegated_" prefix
                        }, $delegatedTypes));
                    }

                    // LRE
                    if (\in_array(AdherentRoleEnum::LRE, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.lreArea', $alias), 'lre');
                        $where->add('lre IS NOT NULL');
                    }

                    // Legislative candidate
                    if (\in_array(AdherentRoleEnum::LEGISLATIVE_CANDIDATE, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.legislativeCandidateManagedDistrict', $alias), 'lcmd');
                        $where->add('lcmd IS NOT NULL');
                    }

                    if (\in_array(AdherentRoleEnum::SENATORIAL_CANDIDATE, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.senatorialCandidateManagedArea', $alias), 'senatorialCandidateManagedArea');
                        $where->add('senatorialCandidateManagedArea IS NOT NULL');
                    }

                    if ($candidateRoles = array_intersect(AdherentRoleEnum::getCandidates(), $value['value'])) {
                        $qb
                            ->leftJoin(sprintf('%s.candidateManagedArea', $alias), 'candidateManagedArea')
                            ->leftJoin('candidateManagedArea.zone', 'candidate_zone')
                            ->setParameter('candidate_zone_types', array_map(function (string $role) {
                                switch ($role) {
                                    case AdherentRoleEnum::CANDIDATE_REGIONAL_HEADED:
                                        return Zone::REGION;
                                    case AdherentRoleEnum::CANDIDATE_REGIONAL_LEADER:
                                        return Zone::DEPARTMENT;
                                    case AdherentRoleEnum::CANDIDATE_DEPARTMENTAL:
                                        return Zone::CANTON;
                                }
                            }, $candidateRoles))
                        ;

                        $where->add('candidate_zone.type IN (:candidate_zone_types)');
                    }

                    if ($delegatedCandidateRoles = array_intersect(AdherentRoleEnum::getDelegatedCandidates(), $value['value'])) {
                        if ($delegatedTypes = array_intersect(
                            [
                                AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_HEADED,
                                AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_LEADER,
                                AdherentRoleEnum::DELEGATED_CANDIDATE_DEPARTMENTAL,
                            ],
                            $value['value']
                        )) {
                            if (!\in_array('rda', $qb->getAllAliases(), true)) {
                                $qb->leftJoin(\sprintf('%s.receivedDelegatedAccesses', $alias), 'rda');
                            }
                            $where->add('rda.type = :delegated_candidate');
                            $qb->setParameter('delegated_candidate', DelegatedAccessEnum::TYPE_CANDIDATE);
                        }

                        $qb
                            ->leftJoin('rda.delegator', 'delegator')
                            ->leftJoin('delegator.candidateManagedArea', 'delegatorCandidateManagedArea')
                            ->leftJoin('delegatorCandidateManagedArea.zone', 'delegator_candidate_zone')
                            ->setParameter('delegator_candidate_zone_types', array_map(function (string $role) {
                                switch ($role) {
                                    case AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_HEADED:
                                        return Zone::REGION;
                                    case AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_LEADER:
                                        return Zone::DEPARTMENT;
                                    case AdherentRoleEnum::DELEGATED_CANDIDATE_DEPARTMENTAL:
                                        return Zone::CANTON;
                                }
                            }, $delegatedCandidateRoles))
                        ;

                        $where->add('delegator_candidate_zone.type IN (:delegator_candidate_zone_types)');
                    }

                    // thematic community chief
                    if (\in_array(AdherentRoleEnum::THEMATIC_COMMUNITY_CHIEF, $value['value'], true)) {
                        $qb->leftJoin(sprintf('%s.handledThematicCommunities', $alias), 'tc');
                        $where->add('tc IS NOT NULL');
                    }

                    if ($where->count()) {
                        $qb->andWhere($where);
                    }

                    return true;
                },
            ])
            ->add('mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) (legacy)',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => Mandates::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $mandate) {
                        $where->add("$alias.mandates LIKE :mandate_".$mandate);
                        $qb->setParameter('mandate_'.$mandate, "%$mandate%");
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('elected_representative_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) RNE',
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

                    $qb
                        ->leftJoin(ElectedRepresentative::class, 'er', Expr\Join::WITH, sprintf('%s.id = er.adherent', $alias))
                        ->leftJoin('er.mandates', 'mandate')
                        ->andWhere('mandate.finishAt IS NULL')
                        ->andWhere('mandate.onGoing = true')
                        ->andWhere('mandate.isElected = true')
                        ->andWhere('mandate.type IN (:types)')
                        ->setParameter('types', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('adherent_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) internes',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => \array_merge(
                        TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS,
                        ['TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT]
                    ),
                    'choice_label' => function (string $choice) {
                        if ('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT === $choice) {
                            return 'territorial_council.membership.quality.elected_candidate_adherent';
                        } else {
                            return 'political_committee.membership.quality.'.$choice;
                        }
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $mandatesCondition = 'adherentMandate.quality IN (:qualities)';
                    if (\in_array('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, $value['value'])) {
                        $mandatesCondition = '(adherentMandate.quality IN (:qualities) OR adherentMandate.committee IS NOT NULL)';
                    }

                    $qb
                        ->leftJoin("$alias.adherentMandates", 'adherentMandate')
                        ->andWhere('adherentMandate.finishAt IS NULL')
                        ->andWhere($mandatesCondition)
                        ->setParameter('qualities', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('memberships.committee', CallbackFilter::class, [
                'label' => 'Comité de vote',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => Committee::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias().'.status = :approved')
                            ->setParameter('approved', BaseGroup::APPROVED)
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.committee IN (:committees)")
                        ->andWhere("$alias.enableVote = true")
                        ->setParameter('committees', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('territorialCouncilMembership.territorialCouncil', CallbackFilter::class, [
                'label' => 'Conseil territorial',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => TerritorialCouncil::class,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'codes',
                    ],
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.territorialCouncil IN (:tc)")
                        ->setParameter('tc', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('politicalCommitteeMembership.politicalCommittee', CallbackFilter::class, [
                'label' => 'Comité politique',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => PoliticalCommittee::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias().'.isActive = true')
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.politicalCommittee IN (:pc)")
                        ->setParameter('pc', $value['value'])
                    ;

                    return true;
                },
            ])
            ->add('municipalChiefManagedArea.jecouteAccess', null, ['label' => 'Candidat municipal: Accès J\'écoute'])
            ->add('municipalChiefManagedArea.inseeCode', null, ['label' => 'Candidat municipal: Insee code'])
        ;
    }

    /**
     * @param Adherent $subject
     */
    public function setSubject($subject)
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $subject;
        }

        parent::setSubject($subject);
    }

    public function preUpdate($object)
    {
        $this->dispatcher->dispatch(new UserEvent($this->beforeUpdate), UserEvents::USER_BEFORE_UPDATE);
    }

    /**
     * @param Adherent $object
     */
    public function postUpdate($object)
    {
        // No need to handle referent tags update as they are not update-able from admin
        $this->emailSubscriptionHistoryManager->handleSubscriptionsUpdate($object, $subscriptionTypes = $this->beforeUpdate->getSubscriptionTypes());
        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($object, $this->beforeUpdate->getTerritorialCouncilMembership());

        $this->dispatcher->dispatch(new UserEvent($object, null, null, $subscriptionTypes), UserEvents::USER_UPDATE_SUBSCRIPTIONS);
        $this->dispatcher->dispatch(new UserEvent($object), UserEvents::USER_UPDATED);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('lastName', null, [
                'label' => 'Nom Prénom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent/list_phone.html.twig',
            ])
            ->add('postAddress', null, [
                'label' => 'Ville (CP) Pays',
                'template' => 'admin/adherent/list_postaddress.html.twig',
                'header_style' => 'min-width: 75px',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('lastLoggedAt', null, [
                'label' => 'Dernière connexion',
            ])
            ->add('instances', null, [
                'label' => 'Instances de vote',
                'virtual_field' => true,
                'header_style' => 'min-width: 150px',
                'template' => 'admin/adherent/list_vote_instances.html.twig',
            ])
            ->add('referentTags', null, [
                'label' => 'Tags souscrits',
                'associated_property' => 'code',
            ])
            ->add('managedAreaTags', null, [
                'label' => 'Tags gérés',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_managed_area_tags.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Rôles',
                'template' => 'admin/adherent/list_status.html.twig',
            ])
            ->add('allMandates', null, [
                'label' => 'Mandats',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_mandates.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'UUID' => 'uuid',
            'Email' => 'emailAddress',
            'Prénom' => 'firstName',
            'Nom' => 'lastName',
            'Date de naissance' => 'birthdate',
            'Téléphone' => 'phone',
            'Inscrit(e) le' => 'registeredAt',
            'Sexe' => 'gender',
            'Adresse' => 'postAddress.address',
            'Code postal' => 'postAddress.postalCode',
            'Ville' => 'postAddress.cityName',
            'Pays' => 'postAddress.country',
        ];
    }
}
