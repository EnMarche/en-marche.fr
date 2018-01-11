<?php

namespace AppBundle\Admin\OAuth;

use AppBundle\OAuth\ClientManager;
use AppBundle\OAuth\Form\GrantTypesType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ClientAdmin extends AbstractAdmin
{
    /**
     * @var ClientManager
     */
    private $clientManager;

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();

        $query->andWhere(
            $query->expr()->isNull($query->getRootAliases()[0].'.deletedAt')
        );

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
                'route' => [
                    'name' => 'show',
                ],
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('_action', null, [
                'actions' => [
                    'delete' => [
                        'template' => '@SonataAdmin/CRUD/list__action_delete.html.twig',
                    ],
                ],
            ])
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Informations')
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                ])
                ->add('redirectUris', 'array', [
                    'label' => 'Adresses de redirection',
                    'template' => 'admin/oauth/client/_show_redirectUris.html.twig',
                ])
                ->add('createdAt', 'datetime', [
                    'label' => 'Date de création',
                ])
                ->add('updatedAt', 'datetime', [
                    'label' => 'Date de modification',
                ])
            ->end()
            ->with('Paramètres de connexion')
                ->add('askUserForAuthorization', 'boolean', [
                    'label' => 'Demander l\'autorisation de connexion sur cette application',
                ])
                ->add('allowedGrantTypes', 'array', [
                    'label' => 'client.allowedGrantTypes.label',
                    'template' => 'admin/oauth/client/_show_allowedGrantTypes.html.twig',
                ])
                ->add('uuid', null, [
                    'label' => 'client.uuid.label',
                ])
                ->add('secret', null, [
                    'label' => 'client.secret.label',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('askUserForAuthorization', ChoiceType::class, [
                'label' => 'Demander l\'autorisation de connexion sur cette application',
                'choices' => [
                    'global.yes' => true,
                    'global.no' => false,
                ],
            ])
            ->add('allowedGrantTypes', GrantTypesType::class, ['error_bubbling' => false])
            ->add('redirectUris', CollectionType::class, [
                'label' => 'Adresses de redirection',
                'entry_type' => UrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;
    }

    public function prePersist($object)
    {
        $object->addSupportedScope('user_profile');
    }

    public function delete($object)
    {
        $this->clientManager->delete($object);
    }

    public function setClientManager(ClientManager $clientManager): void
    {
        $this->clientManager = $clientManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureBatchActions($actions)
    {
        return [];
    }
}
