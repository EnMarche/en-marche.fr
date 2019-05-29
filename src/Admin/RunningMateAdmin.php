<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\Theme;
use AppBundle\Repository\AdherentRepository;
use League\Flysystem\Filesystem;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class RunningMateAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];
    private $storage;
    private $repository;

    public function getDatagrid()
    {
        static $datagrid = null;

        if (null === $datagrid) {
            $datagrid = new IsAdherentDatagrid(parent::getDatagrid(), $this->repository);
        }

        return $datagrid;
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
            ->add('emailAddress', null, [
                'label' => 'E-mail',
            ])
            ->add('favoriteCities', null, [
                'label' => 'Ville(s) choisie(s)',
            ])
            ->add('curriculum', null, [
                'label' => 'CV',
                'template' => 'admin/running_mate/show_curriculum.html.twig',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adherent',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'E-mail',
            ])
            ->add('address', null, [
                'label' => 'Adresse',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postale',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
            ])
            ->add('phone', PhoneNumberType::class, [
                'label' => 'Téléphone',
                'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            ])
            ->add('profession', null, [
                'label' => 'Profession',
            ])
            ->add('favoriteThemes', EntityType::class, [
                'label' => 'Thèmes favoris',
                'class' => Theme::class,
                'multiple' => true,
            ])
            ->add('favoriteThemeDetails', null, [
                'label' => 'Thèmes favoris détails',
            ])
            ->add('curriculum', FileType::class, [
                'label' => 'CV',
            ])
            ->add('isLocalAssociationMember', BooleanType::class, [
                'label' => "Membre de l'association locale ?",
            ])
            ->add('localAssociationDomain', null, [
                'label' => "Domaine de l'association locale",
            ])
            ->add('isPoliticalActivist', BooleanType::class, [
                'label' => 'Activiste politique ?',
            ])
            ->add('politicalActivistDetails', null, [
                'label' => 'Activiste politique détails',
            ])
            ->add('isPreviousElectedOfficial', BooleanType::class, [
                'label' => "Est l'élu précédent ?",
            ])
            ->add('previousElectedOfficialDetails', null, [
                'label' => 'Elu précédent détails',
            ])
            ->add('projectDetails', null, [
                'label' => 'Détails du projet',
            ])
            ->add('professionalAssets', null, [
                'label' => 'Actifs professionnels',
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
        ;
    }

    /**
     * @param RunningMateRequest $runningMateRequest
     */
    public function prePersist($runningMateRequest)
    {
        parent::prePersist($runningMateRequest);

        $runningMateRequest->setCurriculumNameFromUploadedFile($runningMateRequest->getCurriculum());
        $this->storage->put($runningMateRequest->getPathWithDirectory(), file_get_contents($runningMateRequest->getCurriculum()->getPathname()));
    }

    /**
     * @param RunningMateRequest $runningMateRequest
     */
    public function preUpdate($runningMateRequest)
    {
        parent::preUpdate($runningMateRequest);

        if ($runningMateRequest->getCurriculum()) {
            $runningMateRequest->setCurriculumNameFromUploadedFile($runningMateRequest->getCurriculum());
            $this->storage->put($runningMateRequest->getPathWithDirectory(), file_get_contents($runningMateRequest->getCurriculum()->getPathname()));
        }
    }

    public function setStorage(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @required
     */
    public function setAdherentRepository(AdherentRepository $repository)
    {
        $this->repository = $repository;
    }
}
