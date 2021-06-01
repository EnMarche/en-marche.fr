<?php

namespace App\Admin;

use App\CitizenAction\CitizenActionEvent;
use App\Entity\Event\CitizenAction;
use App\Events;
use App\Form\CitizenActionCategoryType;
use App\Form\UnitedNationsCountryType;
use App\Referent\ReferentTagManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CitizenActionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    private $referentTagManager;

    public function getTemplate($name)
    {
        if ('show' === $name) {
            return 'admin/citizen_action/show.html.twig';
        }

        if ('edit' === $name) {
            return 'admin/citizen_action/edit.html.twig';
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

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Actions citoyennes', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', null, [
                    'label' => 'Catégorie',
                ])
                ->add('citizenProject', null, [
                    'label' => 'Projet citoyen d\'origine',
                ])
                ->add('organizer', null, [
                    'label' => 'Organisateur',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                    'safe' => true,
                ])
                ->add('beginAt', null, [
                    'label' => 'Date de début',
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                ])
                ->add('createdAt', null, [
                    'label' => 'Date de création',
                ])
                ->add('participantsCount', null, [
                    'label' => 'Nombre de participants',
                ])
                ->add('status', null, [
                    'label' => 'Statut',
                ])
            ->end()
            ->with('Organisateur', ['class' => 'col-md-5'])
                ->add('organizer.fullName', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('organizer.emailAddress', TextType::class, [
                    'label' => 'Adresse E-mail',
                ])
                ->add('organizer.phone', null, [
                    'label' => 'Téléphone',
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
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Action citoyenne', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('category', CitizenActionCategoryType::class, [
                    'label' => 'Catégorie',
                ])
                ->add('beginAt', null, [
                    'label' => 'Date de début',
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => CitizenAction::STATUSES,
                    'choice_translation_domain' => 'forms',
                    'choice_label' => function (?string $choice) { return $choice; },
                ])
                ->add('published', null, [
                    'label' => 'Publié',
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
            ->with('Description', ['class' => 'col-md-12'])
                ->add('description', TextareaType::class, [
                    'label' => 'description',
                    'required' => false,
                    'filter_emojis' => true,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
                'field_type' => CitizenActionCategoryType::class,
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('beginAt', DateRangeFilter::class, [
                'label' => 'Date de début',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('organizer.firstName', null, [
                'label' => 'Prénom de l\'organisateur',
                'show_filter' => true,
            ])
            ->add('organizer.lastName', null, [
                'label' => 'Nom de l\'organisateur',
                'show_filter' => true,
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
            ->add('published', BooleanFilter::class, [
                'label' => 'Publié',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('citizenProject', null, [
                'label' => 'Projet citoyen d\'origine',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/citizen_action/list_organizer.html.twig',
            ])
            ->add('description', 'html', [
                'label' => 'Description',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('postAddress', null, [
                'label' => 'Lieu',
                'virtual_field' => true,
                'template' => 'admin/citizen_action/list_location.html.twig',
            ])
            ->add('participantsCount', null, [
                'label' => 'Inscrits',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/citizen_action/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/citizen_action/list_actions.html.twig',
            ])
        ;
    }

    public function preUpdate($object)
    {
        $this->eventDispatcher->dispatch(new CitizenActionEvent($object, $object->getOrganizer()), Events::CITIZEN_ACTION_PRE_UPDATE);
    }

    public function postUpdate($object)
    {
        $this->referentTagManager->assignReferentLocalTags($object);

        $this->eventDispatcher->dispatch(new CitizenActionEvent($object, $object->getOrganizer()), Events::CITIZEN_ACTION_UPDATED);
    }

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @required
     */
    public function setReferentTagManager(ReferentTagManager $referentTagManager): void
    {
        $this->referentTagManager = $referentTagManager;
    }
}
