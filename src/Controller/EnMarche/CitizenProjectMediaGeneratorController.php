<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Form\CitizenProjectImageType;
use AppBundle\Form\CitizenProjectTractType;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/projets-citoyens/media-generateur")
 */
class CitizenProjectMediaGeneratorController extends Controller
{
    /**
     * @Route("/images", name="app_citizen_project_image_generator")
     * @Method({"GET", "POST"})
     */
    public function generateImageAction(Request $request): Response
    {
        $form = $this
            ->createForm(CitizenProjectImageType::class)
            ->handleRequest($request);

        $coverImage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImage = $this->get('app.citizen_project.cover_generator')->generate($form->getData());
        }

        return $this->render(
            'citizen_project/media_generator_image_form.html.twig',
            [
                'form' => $form->createView(),
                'previewCoverImage' => $coverImage ? $coverImage->getContentAsDataUrl() : null,
            ]
        );
    }

    /**
     * @Route("/tracts", name="app_citizen_project_tract_generator")
     * @Method({"GET", "POST"})
     */
    public function generateTractAction(Request $request): Response
    {
        $form = $this
            ->createForm(CitizenProjectTractType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tractCommand = $form->getData();
            $tractGenerator = $this->get('app.citizen_project.tract_generator');

            $pdfContent = $tractGenerator->generate($tractCommand);

            return new PdfResponse($pdfContent->getContent(), $tractCommand->getCitizenProjectTitle().'.pdf');
        }

        return $this->render(
            'citizen_project/media_generator_tract_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
