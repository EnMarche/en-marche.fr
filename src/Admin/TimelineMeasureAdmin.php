<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Timeline\Measure;
use App\Entity\Timeline\Theme;
use App\Repository\Timeline\ProfileRepository;
use App\Repository\Timeline\ThemeRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TimelineMeasureAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Traductions', ['class' => 'col-md-6'])
                ->add('translations', TranslationsType::class, [
                    'label' => false,
                    'fields' => [
                        'title' => [
                            'label' => 'Titre',
                        ],
                    ],
                ])
            ->end()
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('link', null, [
                    'label' => 'Lien',
                    'required' => false,
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => Measure::STATUSES,
                ])
            ->end()
            ->with('Tags', ['class' => 'col-md-6'])
                ->add('profiles', null, [
                    'label' => 'Profils',
                ])
                ->add('themes', EntityType::class, [
                    'label' => 'Thèmes',
                    'class' => Theme::class,
                    'by_reference' => false,
                    'multiple' => true,
                ])
                ->add('manifesto', null, [
                    'label' => 'Programme',
                ])
                ->add('major', null, [
                    'label' => 'Mise en avant (32)',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', CallbackFilter::class, [
                'label' => 'Titre',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb
                        ->join("$alias.translations", 'translations')
                        ->andWhere('ILIKE(translations.title, :title) = true')
                        ->setParameter('title', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('profiles', null, [
                'label' => 'Profils',
                'show_filter' => true,
            ], null, [
                'multiple' => true,
                'query_builder' => function (ProfileRepository $repository) {
                    return $repository->createTranslatedChoicesQueryBuilder();
                },
            ])
            ->add('themes', null, [
                'label' => 'Thèmes',
                'show_filter' => true,
            ], null, [
                'multiple' => true,
                'query_builder' => function (ThemeRepository $repository) {
                    return $repository->createTranslatedChoicesQueryBuilder();
                },
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => Measure::STATUSES,
                ],
            ])
            ->add('major', null, [
                'label' => 'Mise en avant (32)',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
                'virtual_field' => true,
                'template' => 'admin/timeline/measure/list_title.html.twig',
            ])
            ->add('profiles', TextType::class, [
                'label' => 'Profils',
            ])
            ->add('themes', TextType::class, [
                'label' => 'Thèmes',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de modification',
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'template' => 'admin/timeline/measure/list_status.html.twig',
            ])
            ->add('major', null, [
                'label' => 'Mise en avant (32)',
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

    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Titre (FR, EN)' => 'exportTitles',
            'Statut' => 'status',
            'Dernière modification' => 'updatedAt',
            'Mise en avant' => 'major',
            'Thèmes' => 'exportThemes',
            'Profils' => 'exportProfiles',
            'Programme' => 'exportManifesto',
        ];
    }
}
