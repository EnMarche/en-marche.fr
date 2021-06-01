<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CommitteeMergeHistoryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'date',
    ];

    protected $accessMapping = [
        'merge' => 'MERGE',
        'revert' => 'REVERT',
    ];

    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->clearExcept('list')
            ->add('merge', 'merge')
            ->add('revert', $this->getRouterIdParameter().'/revert')
        ;
    }

    public function configureActionButtons($action, $object = null)
    {
        if ('merge' === $action) {
            $actions = parent::configureActionButtons('show', $object);
        } else {
            $actions = parent::configureActionButtons($action, $object);
        }

        if ($this->hasAccess('merge') && $this->hasRoute('merge')) {
            $actions['merge'] = ['template' => 'admin/committee/merge/merge_button.html.twig'];
        }

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('sourceCommittee', CallbackFilter::class, [
                'label' => 'Comité source',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.sourceCommittee", 'sc')
                        ->andWhere('ILIKE(sc.name, :sourceName) = true')
                        ->setParameter('sourceName', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('destinationCommittee', CallbackFilter::class, [
                'label' => 'Comité de destination',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.destinationCommittee", 'dc')
                        ->andWhere('ILIKE(dc.name, :destinationName) = true')
                        ->setParameter('destinationName', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('date', DateRangeFilter::class, [
                'label' => 'Date de fusion',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('mergedBy', null, [
                'label' => 'Fusionné par',
                'show_filter' => true,
            ])
            ->add('revertedAt', DateRangeFilter::class, [
                'label' => 'Date d\'annulation',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('revertedBy', null, [
                'label' => 'Annulé par',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('sourceCommittee', null, [
                'label' => 'Comité source',
            ])
            ->add('destinationCommittee', null, [
                'label' => 'Comité de destination',
            ])
            ->add('mergedBy', null, [
                'label' => 'Fusionné par',
            ])
            ->add('date', null, [
                'label' => 'Fusionné le',
            ])
            ->add('revertedBy', null, [
                'label' => 'Annulé par',
            ])
            ->add('revertedAt', null, [
                'label' => 'Annulé le',
            ])
        ;

        if ($this->hasAccess('revert') && $this->hasRoute('revert')) {
            $listMapper->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/committee/merge/list_actions.html.twig',
            ]);
        }
    }
}
