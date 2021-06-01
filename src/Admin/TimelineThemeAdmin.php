<?php

namespace App\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TimelineThemeAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Traductions', ['class' => 'col-md-6'])
                ->add('translations', TranslationsType::class, [
                    'by_reference' => false,
                    'label' => false,
                    'fields' => [
                        'title' => [
                            'label' => 'Titre',
                        ],
                        'slug' => [
                            'label' => 'URL de publication',
                            'sonata_help' => 'Ne spécifier que la fin : http://en-marche.fr/timeline/profil/[votre-valeur]<br />Doit être unique',
                        ],
                        'description' => [
                            'label' => 'Description',
                            'filter_emojis' => true,
                        ],
                    ],
                ])
            ->end()
            ->with('Publication', ['class' => 'col-md-4'])
                ->add('featured', CheckboxType::class, [
                    'label' => 'Mise en avant',
                    'required' => false,
                ])
                ->add('media', null, [
                    'label' => 'Image principale',
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
            ->add('slug', CallbackFilter::class, [
                'label' => 'URL',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb
                        ->join("$alias.translations", 'translations')
                        ->andWhere('ILIKE(translations.slug, :slug) = true')
                        ->setParameter('slug', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('description', CallbackFilter::class, [
                'label' => 'Description',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb
                        ->join("$alias.translations", 'translations')
                        ->andWhere('ILIKE(translations.description, :description) = true')
                        ->setParameter('description', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('featured', null, [
                'label' => 'Mise en avant',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_thumbnail', null, [
                'label' => 'Image',
                'virtual_field' => true,
                'template' => 'admin/timeline/theme/list_image.html.twig',
            ])
            ->addIdentifier('title', null, [
                'label' => 'Titre',
                'virtual_field' => true,
                'template' => 'admin/timeline/theme/list_title.html.twig',
            ])
            ->add('featured', null, [
                'label' => 'Mise en avant',
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
            'Slug (FR, EN)' => 'exportSlugs',
            'Description (FR, EN)' => 'exportDescriptions',
            'Mise en avant' => 'featured',
        ];
    }
}
