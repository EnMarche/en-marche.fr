<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\FacebookVideo;
use AppBundle\Entity\Page;
use AppBundle\Entity\Proposal;
use AppBundle\Event\EventCategories;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Each time you add or update a custom url with an harcorded slug in the controller code, you must update the
 * AppBundle\Entity\Page::URLS constant and reindex algolia's page index.
 */
class PageController extends Controller
{
    /**
     * @Route("/emmanuel-macron", name="page_emmanuel_macron")
     * @Method("GET")
     */
    public function emmanuelMacronAction()
    {
        return $this->render('page/emmanuel-macron/ce-que-je-suis.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-ce-que-je-suis'),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/revolution", name="page_emmanuel_macron_revolution")
     * @Method("GET")
     */
    public function emmanuelMacronRevolutionAction()
    {
        return $this->render('page/emmanuel-macron/revolution.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-revolution'),
        ]);
    }

    /**
     * Redirections to the program.
     *
     * @Route("/programme")
     * @Route("/le-programme")
     * @Method("GET")
     */
    public function redirectProgrammeAction()
    {
        return $this->redirectToRoute('page_emmanuel_macron_programme', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/emmanuel-macron/le-programme", name="page_emmanuel_macron_programme")
     * @Method("GET")
     */
    public function emmanuelMacronProgrammeAction()
    {
        return $this->render('page/emmanuel-macron/propositions.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-propositions'),
            'proposals' => $this->getDoctrine()->getRepository(Proposal::class)->findAllOrderedByPosition(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/le-programme/{slug}", name="page_emmanuel_macron_proposition")
     * @Method("GET")
     */
    public function emmanuelMacronPropositionAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository(Proposal::class)->findOneBySlug($slug);
        if (!$proposal || !$proposal->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->render('page/emmanuel-macron/proposition.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * @Route("/emmanuel-macron/desintox", name="page_emmanuel_macron_desintox_list")
     * @Method("GET")
     */
    public function emmanuelMacronDesintoxListAction()
    {
        $repository = $this->getDoctrine()->getRepository(Clarification::class);

        return $this->render('page/emmanuel-macron/desintox.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('desintox'),
            'clarifications' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/desintox/{slug}", name="page_emmanuel_macron_desintox_view")
     * @Method("GET")
     */
    public function emmanuelMacronDesintoxViewAction($slug)
    {
        $clarification = $this->getDoctrine()->getRepository(Clarification::class)->findOneBySlug($slug);

        if (!$clarification || !$clarification->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->render('page/emmanuel-macron/desintox_view.html.twig', [
            'clarification' => $clarification,
        ]);
    }

    /**
     * @Route("/emmanuel-macron/videos", name="page_emmanuel_macron_videos")
     * @Method("GET")
     */
    public function emmanuelMacronVideosAction()
    {
        return $this->render('page/emmanuel-macron/videos.html.twig', [
            'videos' => $this->getDoctrine()->getRepository(FacebookVideo::class)->findBy(['published' => true], ['position' => 'ASC']),
        ]);
    }

    /**
     * @Route("/le-mouvement", name="page_le_mouvement")
     * @Method("GET")
     */
    public function mouvementValeursAction()
    {
        return $this->render('page/le-mouvement/nos-valeurs.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-nos-valeurs'),
        ]);
    }

    /**
     * @Route("/le-mouvement/notre-organisation", name="page_le_mouvement_notre_organisation")
     * @Method("GET")
     */
    public function mouvementOrganisationAction()
    {
        return $this->render('page/le-mouvement/notre-organisation.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-notre-organisation'),
        ]);
    }

    /**
     * @Route("/le-mouvement/legislatives", name="page_le_mouvement_legislatives")
     * @Method("GET")
     */
    public function mouvementLegislativesAction()
    {
        return $this->render('page/le-mouvement/legislatives.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-legislatives'),
        ]);
    }

    /**
     * @Route("/le-mouvement/la-carte", name="page_le_mouvement_la_carte")
     * @Method("GET")
     */
    public function mouvementCarteComitesAction()
    {
        return $this->render('page/la-carte.html.twig', [
            'userCount' => $this->getDoctrine()->getRepository(Adherent::class)->count(),
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->count(),
            'committeeCount' => $this->getDoctrine()->getRepository(Committee::class)->count(),
        ]);
    }

    /**
     * @Route("/evenements/la-carte", name="page_les_evenements_la_carte")
     * @Method("GET")
     */
    public function mouvementCarteEvenementsAction()
    {
        return $this->render('page/les-evenements/la-carte.html.twig', [
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->countUpcomingEvents(),
            'types' => EventCategories::CHOICES,
        ]);
    }

    /**
     * @Route("/le-mouvement/les-comites", name="page_le_mouvement_les_comites")
     * @Method("GET")
     */
    public function mouvementComitesAction()
    {
        return $this->render('page/le-mouvement/les-comites.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-les-comites'),
        ]);
    }

    /**
     * @Route("/le-mouvement/devenez-benevole", name="page_le_mouvement_devenez_benevole")
     * @Method("GET")
     */
    public function mouvementBenevoleAction()
    {
        return $this->render('page/le-mouvement/devenez-benevole.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-devenez-benevole'),
        ]);
    }

    /**
     * @Route("/legislatives", name="site_legislatives")
     * @Method("GET")
     */
    public function legislativesHomeAction()
    {
        return $this->render('page/legislatives/layout.html.twig');
    }

    /**
     * @Route("/legislatives/resultats-noms", name="site_legislatives_resultats_noms")
     * @Method("GET")
     */
    public function legislativesResultatsNomsAction()
    {
        return $this->render('page/legislatives/resultats-noms.html.twig');
    }

    /**
     * @Route("/legislatives/resultats-departements", name="site_legislatives_resultats_departements")
     * @Method("GET")
     */
    public function legislativesResultatsDepartementsAction()
    {
        return $this->render('page/legislatives/resultats-departements.html.twig');
    }

    /**
     * @Route("/legislatives/candidat", name="site_legislatives_candidat")
     * @Method("GET")
     */
    public function legislativesCandidatAction()
    {
        return $this->render('page/legislatives/candidat.html.twig');
    }

    /**
     * @Route("/legislatives/annuaire", name="site_legislatives_annuaire")
     * @Method("GET")
     */
    public function legislativesAnnuaireAction()
    {
        return $this->render('page/legislatives/annuaire.html.twig');
    }

    /**
     * @Route("/legislatives/carte", name="site_legislatives_carte")
     * @Method("GET")
     */
    public function legislativesCarteAction()
    {
        return $this->render('page/legislatives/carte.html.twig');
    }

    /**
     * @Route("/mentions-legales", name="page_mentions_legales")
     * @Method("GET")
     */
    public function mentionsLegalesAction()
    {
        return $this->render('page/mentions-legales.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('mentions-legales'),
        ]);
    }

    /**
     * @Route("/bot", name="page_bot")
     * @Method("GET")
     */
    public function botAction()
    {
        return $this->render('bot/page.html.twig');
    }

    /**
     * @Route("/elles-marchent", name="page_elles_marchent")
     * @Method("GET")
     */
    public function ellesMarchentAction()
    {
        return $this->render('page/elles-marchent.html.twig');
    }
}
