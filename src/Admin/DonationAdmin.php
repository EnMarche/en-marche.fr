<?php

namespace AppBundle\Admin;

use AppBundle\Donation\DonationEvents;
use AppBundle\Donation\DonationWasCreatedEvent;
use AppBundle\Donation\DonationWasUpdatedEvent;
use AppBundle\Entity\Donation;
use AppBundle\Entity\DonationTag;
use AppBundle\Entity\PostAddress;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as FormNumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DonationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    private $storage;
    private $dispatcher;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        Filesystem $storage,
        EventDispatcher $dispatcher
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->storage = $storage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Donation|null $object
     */
    public function hasAccess($action, $object = null)
    {
        if ($object && 'delete' === $action && $object->isCB()) {
            return false;
        }

        return parent::hasAccess($action, $object);
    }

    public function configureBatchActions($actions)
    {
        unset($actions['delete']);

        return $actions;
    }

    protected function configureFormFields(FormMapper $form)
    {
        /** @var Donation $donation */
        $donation = $this->getSubject();

        $form
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('donator', ModelAutocompleteType::class, [
                    'label' => 'Donateur',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'identifier',
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'disabled' => !$this->isCurrentRoute('create'),
                    'choices' => $this->getTypeChoices(),
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ])
                ->add('amountInEuros', FormNumberType::class, [
                    'label' => 'Montant',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
                ->add('donatedAt', null, [
                    'label' => 'Date du don',
                    'disabled' => $donation->isCB(),
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut du don',
                    'disabled' => $donation->isCB(),
                    'choices' => [
                        Donation::STATUS_WAITING_CONFIRMATION,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_REFUNDED,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.status.'.$choice;
                    },
                ])
                ->add('checkNumber', null, [
                    'label' => 'Numéro de chèque',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
                ->add('transferNumber', null, [
                    'label' => 'Numéro de virement',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
            ->end()
        ;
        if ($this->isCurrentRoute('create')) {
            $form
                ->with('Adresse', ['class' => 'col-md-5'])
                    ->add('address', TextType::class, [
                        'label' => 'Rue',
                        'mapped' => false,
                    ])
                    ->add('postalCode', TextType::class, [
                        'label' => 'Code postal',
                        'mapped' => false,
                    ])
                    ->add('cityName', TextType::class, [
                        'label' => 'Ville',
                        'mapped' => false,
                    ])
                    ->add('country', CountryType::class, [
                        'label' => 'Pays',
                        'mapped' => false,
                    ])
                ->end()
            ;

            $formMapper
                ->getFormBuilder()
                ->addEventListener(FormEvents::SUBMIT, [$this, 'buildPostAddress'])
            ;
        } else {
            $form
                ->with('Adresse', ['class' => 'col-md-5'])
                ->add('address.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('address.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('address.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('address.country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->end()
            ;
        }

        $form
            ->with('Fichier', ['class' => 'col-md-6'])
                ->add('file', FileType::class, [
                    'required' => false,
                    'label' => 'Ajoutez un fichier',
                    'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                ])
                ->add('removeFile', CheckboxType::class, [
                    'label' => 'Supprimer le fichier ?',
                    'required' => false,
                ])
            ->end()
            ->with('Administration', ['class' => 'col-md-6'])
                ->add('tags', null, [
                    'label' => 'Tags',
                ])
                ->add('comment', null, [
                    'label' => 'Commentaire',
                ])
            ->end()

        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('donator', ModelAutocompleteFilter::class, [
                'label' => 'Donateur',
                'show_filter' => true,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'identifier',
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ],
            ])
            ->add('type', null, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getTypeChoices(),
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        Donation::STATUS_WAITING_CONFIRMATION,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_REFUNDED,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.status.'.$choice;
                    },
                ],
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags',
                'show_filter' => true,
                'field_options' => [
                    'class' => DonationTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('donator', null, [
                'label' => 'Donateur',
            ])
            ->add('amountInEuros', NumberType::class, [
                'label' => 'Montant',
                'template' => 'admin/donation/list_amount.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/donation/list_type.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut du don',
                'template' => 'admin/donation/list_status.html.twig',
            ])
            ->add('donatedAt', null, [
                'label' => 'Date',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
                'template' => 'admin/donation/list_tags.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Montant' => 'amountInEuros',
            'Date' => 'createdAt',
            'Type' => 'type',
            'Statut' => 'status',
            'Numéro donateur' => 'donator.identifier',
            'Nom' => 'donator.lastName',
            'Prénom' => 'donator.firstName',
            'Civilité' => 'donator.gender',
            'Adresse e-mail' => 'donator.emailAddress',
            'Ville du donateur' => 'donator.city',
            'Pays du donateur' => 'donator.country',
            'Adresse de référence' => 'donator.getReferenceAddress',
            'Tags du donateur' => 'donator.getTagsAsString',
        ];
    }

    /**
     * @param Donation $donation
     */
    public function prePersist($donation)
    {
        parent::prePersist($donation);

        $this->handleFile($donation);

        $this->dispatcher->dispatch(DonationEvents::CREATED, new DonationWasCreatedEvent($donation));
    }

    /**
     * @param Donation $donation
     */
    public function preUpdate($donation)
    {
        parent::preUpdate($donation);

        $this->handleFile($donation);

        $this->dispatcher->dispatch(DonationEvents::UPDATED, new DonationWasUpdatedEvent($donation));
    }

    /**
     * @param Donation $donation
     */
    public function postRemove($donation)
    {
        parent::postRemove($donation);

        if ($donation->hasFileUploaded()) {
            $filePath = $donation->getFilePathWithDirectory();

            if ($this->storage->has($filePath)) {
                $this->storage->delete($filePath);
            }
        }
    }

    public function buildPostAddress(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Donation $donation */
        $donation = $event->getData();

        $country = $form->get('country')->getData();
        $postalCode = $form->get('postalCode')->getData();
        $city = $form->get('cityName')->getData();
        $street = $form->get('address')->getData();

        if (PostAddress::FRANCE === $country) {
            $address = PostAddress::createFrenchAddress(
                $street,
                $postalCode
            );
        } else {
            $address = PostAddress::createForeignAddress(
                $country,
                $postalCode,
                $city,
                $street
            );
        }

        $donation->setPostAddress($address);
    }

    public function handleFile(Donation $donation): void
    {
        $filepath = $donation->getFilePathWithDirectory();

        if ($donation->getRemoveFile() && $this->storage->has($filepath)) {
            $this->storage->delete($filepath);
            $donation->removeFilename();

            return;
        }

        $this->uploadFile($donation);
    }

    public function uploadFile(Donation $donation): void
    {
        $uploadedFile = $donation->getFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $donation->setFilenameFromUploadedFile();

        $this->storage->put($donation->getFilePathWithDirectory(), file_get_contents($donation->getFile()->getPathname()));
    }

    private function getTypeChoices(): array
    {
        $choices = [
            Donation::TYPE_CHECK,
            Donation::TYPE_TRANSFER,
        ];

        if (!$this->isCurrentRoute('create')) {
            $choices[] = Donation::TYPE_CB;
        }

        return $choices;
    }
}
