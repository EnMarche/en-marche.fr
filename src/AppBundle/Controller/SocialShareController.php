<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SocialShare;
use AppBundle\Entity\SocialShareCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SocialShareController extends Controller
{
    /**
     * @Route("/jepartage", name="app_social_share_list")
     * @Method("GET")
     */
    public function listAction(): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $socialShareCategoryRepository = $this->getDoctrine()->getRepository(SocialShareCategory::class);
        $socialShareRepository = $this->getDoctrine()->getRepository(SocialShare::class);

        return $this->render('social_share/list.html.twig', [
            'socialShareCategories' => $socialShareCategoryRepository->findBy([], ['position' => 'ASC']),
            'socialShares' => $socialShareRepository->findBy(
                ['published' => true],
                ['position' => 'ASC', 'createdAt' => 'DESC']
            ),
        ]);
    }

    /**
     * @Route("/jepartage/{slug}", name="app_social_share_show")
     * @Method("GET")
     */
    public function showAction(SocialShareCategory $socialShareCategory): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $socialShareRepository = $this->getDoctrine()->getRepository(SocialShare::class);

        return $this->render('social_share/show.html.twig', [
            'socialShareCategory' => $socialShareCategory,
            'socialShares' => $socialShareRepository->findBy(
                ['socialShareCategory' => $socialShareCategory, 'published' => true],
                ['position' => 'ASC', 'createdAt' => 'DESC']
            ),
        ]);
    }

    /**
     * @Route("/je-partage")
     * @Method("GET")
     */
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('app_social_share_list', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
