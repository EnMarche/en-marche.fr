<?php

namespace App\Admin;

use App\Entity\IdeasWorkshop\AuthorCategoryEnum;
use App\Entity\IdeasWorkshop\IdeaStatusEnum;
use App\Repository\IdeasWorkshop\IdeaRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class IdeasWorkshopIdeaAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected $formOptions = [
        'validation_groups' => ['Admin'],
    ];

    private $cachedDatagrid;
    private $ideaRepository;

    public function __construct($code, $class, $baseControllerName, IdeaRepository $repository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->ideaRepository = $repository;
    }

    public function getDatagrid()
    {
        if (!$this->cachedDatagrid) {
            $this->cachedDatagrid = new IdeaDatagrid(parent::getDatagrid(), $this->ideaRepository);
        }

        return $this->cachedDatagrid;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, [
                'label' => 'Titre',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('daysBeforeDeadline', null, [
                'label' => 'Temps restant',
            ])
            ->add('committee', null, [
                'label' => 'Comité associé',
            ])
            ->add('themes', null, [
                'label' => 'Thème',
            ])
            ->add('category', null, [
                'label' => 'Echelle du projet',
            ])
            ->add('needs', null, [
                'label' => 'Besoins',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/ideas_workshop/idea/show_status.html.twig',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', null, [
                'label' => 'Titre',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('committee', null, [
                'label' => 'Comité associé',
            ])
            ->add('themes', null, [
                'label' => 'Thème',
            ])
            ->add('category', null, [
                'label' => 'Echelle du projet',
            ])
            ->add('needs', null, [
                'label' => 'Besoins',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('author.firstName', null, [
                'label' => 'Prénom du créateur',
            ])
            ->add('author.lastName', null, [
                'label' => 'Nom du créateur',
            ])
            ->add('author.emailAddress', null, [
                'label' => 'Mail du créateur',
            ])
            ->add('authorCategory',
                ChoiceFilter::class,
                [
                    'label' => 'Type',
                ],
                ChoiceType::class,
                [
                    'choices' => array_combine(
                        array_map(
                            function ($category) {
                                return 'ideas_workshop.author_category.'.mb_strtolower($category);
                            },
                            AuthorCategoryEnum::ALL_CATEGORIES
                        ),
                        AuthorCategoryEnum::ALL_CATEGORIES
                    ),
                ]
            )
            ->add('status', CallbackFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine(
                        array_map(
                            function ($status) {
                                return 'ideas_workshop.status.'.mb_strtolower($status);
                            },
                            IdeaStatusEnum::ALL_STATUSES
                        ),
                        IdeaStatusEnum::ALL_STATUSES
                    ),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$status = $value['value']) {
                        return;
                    }

                    $this->ideaRepository->addStatusFilter($qb, $alias, $status);

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Titre',
                'header_style' => 'width: 250px',
                'row_align' => 'none;word-break: break-all;',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('author', null, [
                'label' => 'Créateur',
                'template' => 'admin/list/list_author.html.twig',
            ])
            ->add('themes', null, [
                'label' => 'Thème',
            ])
            ->add('category', null, [
                'label' => 'Echelle du projet',
            ])
            ->add('authorCategory', null, [
                'label' => 'Type',
                'template' => 'admin/ideas_workshop/idea/list_author_category.html.twig',
            ])
            ->add('needs', null, [
                'label' => 'Besoins',
            ])
            ->add('extensionsCountAndLastExtensionDate', null, [
                'label' => 'Prolongation',
                'template' => 'admin/ideas_workshop/idea/list_extensions.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'header_style' => 'width:125px;',
                'template' => 'admin/ideas_workshop/idea/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'show_contributors' => [
                        'template' => 'admin/ideas_workshop/idea/list_show_contributors.html.twig',
                    ],
                    'moderate' => [
                        'template' => 'admin/ideas_workshop/list_action_moderate.html.twig',
                    ],
                    'contribute' => [
                        'template' => 'admin/ideas_workshop/idea/list_action_contribute.html.twig',
                    ],
                    'finalize' => [
                        'template' => 'admin/ideas_workshop/idea/list_action_finalize.html.twig',
                    ],
                    'extend' => [
                        'template' => 'admin/ideas_workshop/idea/list_action_extend.html.twig',
                    ],
                    'delete' => [],
                ],
            ])
        ;
    }
}
