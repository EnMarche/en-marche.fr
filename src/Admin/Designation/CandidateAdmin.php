<?php

namespace App\Admin\Designation;

use App\Admin\AbstractAlgoliaAdmin;
use App\Algolia\Sonata\ProxyQuery\ProxyQuery;
use App\Entity\Committee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\TerritorialCouncil\TerritorialCouncilQualityChoiceType;
use App\Repository\CommitteeRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CandidateAdmin extends AbstractAlgoliaAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id', CallbackFilter::class, [
                'label' => 'Candidature id',
                'field_type' => TextType::class,
                'field_options' => [
                ],
                'callback' => function (ProxyQuery $qb, ?string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb
                        ->andWhere($qb->expr()->in($field, ':ids'))
                        ->setParameter('ids', explode(',', $value['value']))
                    ;
                },
            ])
            ->add('designation', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Désignation',
                'field_options' => [
                    'class' => Designation::class,
                    'choice_label' => 'label',
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
                'association_mapping' => [
                    'fieldName' => 'designation.id',
                ],
            ])
            ->add('committee', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Comité',
                'field_options' => [
                    'class' => Committee::class,
                    'choice_label' => 'name',
                    'query_builder' => function (CommitteeRepository $repository) {
                        return $repository
                            ->createQueryBuilder('c')
                            ->select('PARTIAL c.{id, name}')
                            ->innerJoin('c.committeeElections', 'election')
                        ;
                    },
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
                'association_mapping' => [
                    'fieldName' => 'election_entity.committee_id',
                ],
            ])
            ->add('territorialCouncil', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Conseil territorial',
                'field_options' => [
                    'class' => TerritorialCouncil::class,
                    'choice_label' => 'name',
                    'query_builder' => function (TerritorialCouncilRepository $repository) {
                        return $repository
                            ->createQueryBuilder('tc')
                            ->select('PARTIAL tc.{id, name}')
                            ->addSelect('pc')
                            ->leftJoin('tc.politicalCommittee', 'pc')
                            ->andWhere('tc.isActive = true')
                        ;
                    },
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
                'association_mapping' => [
                    'fieldName' => 'election_entity.territorial_council_id',
                ],
            ])
            ->add('first_name', StringFilter::class, [
                'show_filter' => true,
                'label' => 'Prénom',
            ])
            ->add('last_name', StringFilter::class, [
                'show_filter' => true,
                'label' => 'Nom',
            ])
            ->add('status', ChoiceFilter::class, [
                'show_filter' => true,
                'label' => 'Statut',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        CandidacyInterface::STATUS_CONFIRMED,
                        CandidacyInterface::STATUS_DRAFT,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'designation.candidate.status.'.$choice;
                    },
                ],
            ])
            ->add('quality', ChoiceFilter::class, [
                'show_filter' => true,
                'label' => 'Qualité',
                'field_type' => TerritorialCouncilQualityChoiceType::class,
                'field_options' => [
                    'choices' => TerritorialCouncilQualityEnum::ABLE_TO_CANDIDATE,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', 'text', [
                'label' => 'Id interne',
            ])
            ->add('designation.label', 'text', [
                'label' => 'Désignation',
                'header_style' => 'max-width: 150px',
                'template' => 'admin/instances/candidate_list_designation_column.html.twig',
            ])
            ->add('entity', 'text', [
                'label' => 'Entité',
                'virtual_field' => true,
                'template' => 'admin/instances/candidate_list_entity_column.html.twig',
            ])
            ->add('identity', 'text', [
                'label' => 'Identité',
                'virtual_field' => true,
                'template' => 'admin/instances/candidate_list_identity_column.html.twig',
                'header_style' => 'min-width: 150px',
            ])
            ->add('quality', 'trans', [
                'label' => 'Qualité',
                'format' => 'territorial_council.membership.quality.%s',
            ])
            ->add('created_at', 'text', [
                'label' => 'Date de candidature',
            ])
            ->add('status', 'trans', [
                'label' => 'Statut',
                'format' => 'designation.candidate.status.%s',
            ])
            ->add('binomes', 'text', [
                'label' => 'Binome id',
                'virtual_field' => true,
                'template' => 'admin/instances/candidate_list_binomes_column.html.twig',
            ])
            ->add('presentation', 'text', [
                'label' => 'Présentation / projet',
                'virtual_field' => true,
                'template' => 'admin/instances/candidate_list_presentation_column.html.twig',
            ])
            ->add('votes', 'text', [
                'label' => 'Bulletins tour 1/bis',
                'virtual_field' => true,
                'template' => 'admin/instances/candidate_list_votes_column.html.twig',
            ])
        ;
    }

    public function getFilterParameters()
    {
        $parameters = parent::getFilterParameters();

        unset($parameters['_sort_by'], $parameters['_sort_order']);

        return $parameters;
    }
}
