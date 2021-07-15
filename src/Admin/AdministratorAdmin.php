<?php

namespace App\Admin;

use App\Entity\Administrator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    /**
     * @var EncoderFactoryInterface
     */
    private $encoders;

    /**
     * @var GoogleAuthenticator
     */
    private $googleAuthenticator;

    /**
     * @param Administrator $admin
     */
    public function prePersist($admin)
    {
        parent::prePersist($admin);

        $admin->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Administrator $admin */
        $admin = $this->getSubject();
        $isCreation = null === $admin->getId();

        $formMapper
            ->add('emailAddress', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('activated', null, [
                'label' => 'Activé',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Permissions',
                'expanded' => true,
                'multiple' => true,
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'choices' => [
                    'ROLE_SUPER_ADMIN',
                    'ROLE_ADMIN_DASHBOARD',
                    'ROLE_ADMIN_MEDIAS',
                    'ROLE_ADMIN_CONTENT',
                    'ROLE_ADMIN_HOME',
                    'ROLE_ADMIN_PROPOSALS',
                    'ROLE_ADMIN_ORDERS',
                    'ROLE_ADMIN_FACEBOOK_PROFILES',
                    'ROLE_ADMIN_FACEBOOK_VIDEOS',
                    'ROLE_ADMIN_REDIRECTIONS',
                    'ROLE_ADMIN_NEWSLETTER',
                    'ROLE_ADMIN_JE_MARCHE',
                    'ROLE_ADMIN_UNREGISTRATIONS',
                    'ROLE_ADMIN_TON_MACRON',
                    'ROLE_ADMIN_MY_EUROPE',
                    'ROLE_ADMIN_LEGISLATIVES',
                    'ROLE_ADMIN_ADHERENTS',
                    'ROLE_ADMIN_ADHERENTS_READONLY',
                    'ROLE_ADMIN_COMMITTEES',
                    'ROLE_ADMIN_COMMITTEES_MERGE',
                    'ROLE_ADMIN_COMMITTEE_DESIGNATION',
                    'ROLE_ADMIN_EVENTS',
                    'ROLE_ADMIN_INSTITUTIONAL_EVENTS',
                    'ROLE_ADMIN_REPORTS',
                    'ROLE_ADMIN_PROCURATIONS',
                    'ROLE_ADMIN_ELECTIONS',
                    'ROLE_ADMIN_DONATIONS',
                    'ROLE_ADMIN_MAILER',
                    'ROLE_ADMIN_ADHERENT_TAGS',
                    'ROLE_ADMIN_REFERENT_TAGS',
                    'ROLE_ADMIN_REFERENTS',
                    'ROLE_ADMIN_REFERENTS_AREAS',
                    'ROLE_ADMIN_BOARD_MEMBER_ROLES',
                    'ROLE_ADMIN_TIMELINE',
                    'ROLE_ADMIN_CLIENTS',
                    'ROLE_ADMIN_SCOPES',
                    'ROLE_ADMIN_ORGANIGRAMM',
                    'ROLE_ADMIN_MOOC',
                    'ROLE_ADMIN_EMAIL',
                    'ROLE_ADMIN_EMAIL_SUBSCRIPTION_TYPES',
                    'ROLE_ADMIN_LABEL',
                    'ROLE_ADMIN_BIOGRAPHY',
                    'ROLE_ADMIN_JECOUTE',
                    'ROLE_ADMIN_BAN',
                    'ROLE_ADMIN_CERTIFICATION',
                    'ROLE_ADMIN_IDEAS_WORKSHOP',
                    'ROLE_ADMIN_FORMATION',
                    'ROLE_ADMIN_ASSESSOR',
                    'ROLE_ADMIN_APPLICATION_REQUEST',
                    'ROLE_ADMIN_CHEZ_VOUS',
                    'ROLE_ADMIN_FINANCE',
                    'ROLE_ADMIN_PROGRAMMATIC_FOUNDATION',
                    'ROLE_ADMIN_ELECTION_CITY_CARD',
                    'ROLE_ADMIN_ELECTION_CITY_CARD_MANAGERS',
                    'ROLE_ADMIN_ELECTED_REPRESENTATIVE',
                    'ROLE_APP_ADMIN_ELECTED_REPRESENTATIVE_LABELS',
                    'ROLE_ADMIN_TERRITORIAL_COUNCIL',
                    'ROLE_ADMIN_TERRITORIAL_COUNCIL_MEMBERSHIP_LOG',
                    'ROLE_ADMIN_THEMATIC_COMMUNITY',
                    'ROLE_ADMIN_FILES',
                    'ROLE_APP_ADMIN_ADHERENT_CONSEIL',
                ],
            ])
            ->add(
                $formMapper->create('password', RepeatedType::class, [
                    'first_options' => [
                        'label' => 'Mot de passe',
                    ],
                    'second_options' => [
                        'label' => 'Confirmation',
                    ],
                    'type' => PasswordType::class,
                    'required' => $isCreation,
                ])
                ->addModelTransformer(new CallbackTransformer(
                    function () {
                        return '';
                    },
                    function ($plain) use ($admin) {
                        return \is_string($plain) && '' !== $plain
                            ? $this->encoders->getEncoder($admin)->encodePassword($plain, null)
                            : $admin->getPassword();
                    }
                ))
            )
        ;

        if (!$isCreation) {
            $formMapper->add('googleAuthenticatorSecret', null, [
                'label' => 'Clé Google Authenticator',
            ]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('emailAddress', null, [
                'label' => 'E-mail',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'qrcode' => [
                        'template' => 'admin/admin/list_qrcode.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
            ->add('activated', null, [
                'label' => 'Activé',
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }

    public function setEncoders(EncoderFactoryInterface $encoders)
    {
        $this->encoders = $encoders;
    }

    public function setGoogleAuthenticator(GoogleAuthenticator $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }
}
