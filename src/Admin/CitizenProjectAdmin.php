<?php

namespace App\Admin;

use App\CitizenProject\CitizenProjectManager;
use App\CitizenProject\CitizenProjectWasUpdatedEvent;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectSkill;
use App\Events;
use App\Form\PurifiedTextareaType;
use App\Form\UnitedNationsCountryType;
use App\Intl\UnitedNationsBundle;
use App\Referent\ReferentTagManager;
use App\Repository\AdherentRepository;
use App\Repository\CitizenProjectMembershipRepository;
use App\Repository\CitizenProjectRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CitizenProjectAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    private $adherentRepository;
    private $manager;
    private $citizenProjectMembershipRepository;
    private $citizenProjectRepository;
    private $cachedDatagrid;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    private $referentTagManager;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        CitizenProjectManager $manager,
        CitizenProjectRepository $repository,
        CitizenProjectMembershipRepository $membershipRepository,
        AdherentRepository $adherentRepository,
        ReferentTagManager $referentTagManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->manager = $manager;
        $this->adherentRepository = $adherentRepository;
        $this->citizenProjectRepository = $repository;
        $this->citizenProjectMembershipRepository = $membershipRepository;
        $this->referentTagManager = $referentTagManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid()
    {
        if (!$this->cachedDatagrid) {
            $this->cachedDatagrid = new CitizenProjectDatagrid(parent::getDatagrid(), $this->manager);
        }

        return $this->cachedDatagrid;
    }

    public function getTemplate($name)
    {
        if ('show' === $name) {
            return 'admin/citizen_project/show.html.twig';
        }

        if ('edit' === $name) {
            return 'admin/citizen_project/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    public function postUpdate($object)
    {
        $this->referentTagManager->assignReferentLocalTags($object);

        $this->eventDispatcher->dispatch(new CitizenProjectWasUpdatedEvent($object), Events::CITIZEN_PROJECT_UPDATED);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Projet citoyen', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('district', null, [
                    'label' => 'Ville/Quartier',
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('status', null, [
                    'label' => 'Statut',
                ])
                ->add('createdAt', null, [
                    'label' => 'Date de création',
                ])
                ->add('approvedAt', null, [
                    'label' => 'Date d\'approbation',
                ])
                ->add('refusedAt', null, [
                    'label' => 'Date de refus',
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
            ->with('Téléphone', ['class' => 'col-md-5'])
                ->add('phone', null, [
                    'label' => 'Téléphone',
                    'template' => 'admin/adherent/show_phone.html.twig',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Projet citoyen', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('district', null, [
                    'label' => 'Ville/Quartier',
                    'format_title_case' => true,
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('subtitle', null, [
                    'label' => 'Sous-titre',
                    'format_title_case' => true,
                ])
                ->add('category', null, [
                    'label' => 'Catégorie',
                ])
                ->add('problemDescription', null, [
                    'label' => 'Description du problème',
                ])
                ->add('proposedSolution', PurifiedTextareaType::class, [
                    'label' => 'Solution du problème',
                    'filter_emojis' => true,
                    'purifier_type' => 'enrich_content',
                    'attr' => ['class' => 'ck-editor'],
                ])
                ->add('requiredMeans', null, [
                    'label' => 'Feuille de route',
                    'filter_emojis' => true,
                ])
                ->add('skills', EntityType::class, [
                    'class' => CitizenProjectSkill::class,
                    'label' => 'Compétences',
                    'multiple' => true,
                ])
                ->add('matchedSkills', null, [
                    'label' => 'Compétences matchées',
                ])
                ->add('featured', null, [
                    'label' => 'Coup de coeur',
                ])
            ->end()
            ->with('Localisation', ['class' => 'col-md-5'])
                ->add('postAddress.latitude', null, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', null, [
                    'label' => 'Longitude',
                    'help' => 'Pour modifier l\'adresse, impersonnifiez un organisateur de ce projet citoyen.',
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $adherentRepository = $this->adherentRepository;
        $citizenProjectRepository = $this->citizenProjectRepository;
        $citizenProjectMembershipRepository = $this->citizenProjectMembershipRepository;

        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('administratorFirstName', CallbackFilter::class, [
                'label' => 'Prénom de l\'organisateur/créateur',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($citizenProjectRepository, $citizenProjectMembershipRepository, $adherentRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    $creatorCitizenProjectUuids = $citizenProjectRepository->findCitizenProjectUuidByCreatorUuids($adherentRepository->findAdherentsUuidByFirstName($value['value']));
                    $administratorCitizenProjectUuids = $citizenProjectMembershipRepository->findCitizenProjectsUuidByAdministratorFirstName($value['value']);
                    if (!$creatorCitizenProjectUuids && !$administratorCitizenProjectUuids) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $citizenProjectUuids = array_unique(array_merge($creatorCitizenProjectUuids, $administratorCitizenProjectUuids));
                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $citizenProjectUuids));

                    return true;
                },
            ])
            ->add('administratorLastName', CallbackFilter::class, [
                'label' => 'Nom de l\'organisateur/créateur',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($citizenProjectRepository, $citizenProjectMembershipRepository, $adherentRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    $creatorCitizenProjectUuids = $citizenProjectRepository->findCitizenProjectUuidByCreatorUuids($adherentRepository->findAdherentsUuidByLastName($value['value']));
                    $administratorCitizenProjectUuids = $citizenProjectMembershipRepository->findCitizenProjectsUuidByAdministratorLastName($value['value']);
                    if (!$creatorCitizenProjectUuids && !$administratorCitizenProjectUuids) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $citizenProjectUuids = array_unique(array_merge($creatorCitizenProjectUuids, $administratorCitizenProjectUuids));
                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $citizenProjectUuids));

                    return true;
                },
            ])
            ->add('administratorEmailAddress', CallbackFilter::class, [
                'label' => 'Email de l\'organisateur/créateur',
                'field_type' => EmailType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($citizenProjectRepository, $citizenProjectMembershipRepository, $adherentRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    $creatorCitizenProjectUuids = $citizenProjectRepository->findCitizenProjectUuidByCreatorUuids($adherentRepository->findAdherentsUuidByEmailAddress($value['value']));
                    $administratorCitizenProjectUuids = $citizenProjectMembershipRepository->findCitizenProjectsUuidByAdministratorEmailAddress($value['value']);
                    if (!$creatorCitizenProjectUuids && !$administratorCitizenProjectUuids) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $citizenProjectUuids = array_unique(array_merge($creatorCitizenProjectUuids, $administratorCitizenProjectUuids));
                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $citizenProjectUuids));

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
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => [
                        'En attente' => CitizenProject::PENDING,
                        'Pré-accepté' => CitizenProject::PRE_APPROVED,
                        'Accepté' => CitizenProject::APPROVED,
                        'Pré-refusé' => CitizenProject::PRE_REFUSED,
                        'Refusé' => CitizenProject::REFUSED,
                    ],
                ],
            ])
            ->add('skills', null, [
                'label' => 'Compétences recherchées',
            ], null, [
                'multiple' => true,
            ])
            ->add('matchedSkills', null, [
                'label' => 'Compétences matchées',
            ])
            ->add('featured', null, [
                'label' => 'Coup de coeur',
            ])
            ->add('turnkeyProject', CallbackFilter::class, [
                'label' => 'Type de projet',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(CitizenProject::TYPES),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    $qb->andWhere(sprintf('%s.turnkeyProject is %s', $alias, CitizenProject::TURNKEY_TYPE === $value['value'] ? 'not null' : 'null'));

                    return true;
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('projectType', null, [
                'label' => 'Type de projet',
                'header_style' => 'width: 10%;',
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'template' => 'admin/citizen_project/list_name.html.twig',
            ])
            ->add('district', null, [
                'label' => 'Ville/Quartier',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('creator', TextType::class, [
                'label' => 'Créateur',
                'template' => 'admin/citizen_project/list_creator.html.twig',
            ])
            ->add('administrators', TextType::class, [
                'label' => 'Organisateur(s)',
                'template' => 'admin/citizen_project/list_administrators.html.twig',
            ])
            ->add('membersCount', null, [
                'label' => 'Membres',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
                'template' => 'admin/citizen_project/city.html.twig',
            ])
            ->add('skills', null, [
                'label' => 'Compétences recherchées',
            ])
            ->add('nextAction', null, [
                'label' => 'Prochaine action',
                'template' => 'admin/citizen_project/list_next_citizen_action.html.twig',
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'template' => 'admin/citizen_project/list_status.html.twig',
            ])
            ->add('committeeSupports', null, [
                'label' => 'Comités en soutien',
                'template' => 'admin/citizen_project/list_committee_supports.html.twig',
            ])
            ->add('featured', null, [
                'label' => 'Coup de coeur',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/citizen_project/list_actions.html.twig',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields()
    {
        return [
            'Nom' => 'name',
            'Sous-titre' => 'subtitle',
            'Type de projet' => 'getProjectType',
            'Date de création' => 'createdAt',
            'Catégorie' => 'category',
            'Créateur' => 'createdBy',
            'Membres' => 'membersCount',
            'Ville' => 'postAddress.cityName',
            'Code postal' => 'postAddress.postalCode',
            'Compétences recherchées' => 'exportSkills',
            'Compétences matchées' => 'matchedSkills',
            'Statut' => 'status',
            'Comités en soutien' => 'exportCommitteeSupports',
            'Coup de coeur' => 'featured',
        ];
    }

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
