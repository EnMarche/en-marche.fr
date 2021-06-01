<?php

namespace App\Controller\EnMarche;

use App\Controller\EntityControllerTrait;
use App\Entity\MemberSummary\JobExperience;
use App\Entity\MemberSummary\Language;
use App\Entity\MemberSummary\Training;
use App\Form\JobExperienceType;
use App\Form\LanguageType;
use App\Form\SummaryType;
use App\Form\TrainingType;
use App\Membership\MemberActivityTracker;
use App\Repository\MemberSummary\JobExperienceRepository;
use App\Repository\MemberSummary\LanguageRepository;
use App\Repository\MemberSummary\TrainingRepository;
use App\Repository\SkillRepository;
use App\Summary\SummaryManager;
use Cocur\Slugify\SlugifyInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/mon-profil")
 */
class SummaryManagerController extends AbstractController
{
    use EntityControllerTrait;

    private $summaryManager;

    public function __construct(SummaryManager $summaryManager)
    {
        $this->summaryManager = $summaryManager;
    }

    /**
     * @Route(name="app_summary_manager_index", methods={"GET"})
     */
    public function indexAction(MemberActivityTracker $tracker): Response
    {
        $member = $this->getUser();
        $summary = $this->summaryManager->getForAdherent($this->getUser());
        $this->summaryManager->setUrlProfilePicture($summary);

        return $this->render('summary_manager/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => $tracker->getRecentActivitiesForAdherent($member),
        ]);
    }

    /**
     * @Route("/activites_recentes/cacher_afficher", name="app_summary_manager_toggle_showing_recent_activities", methods={"GET"})
     */
    public function toggleShowingRecentActivitiesAction(): Response
    {
        $summary = $this->summaryManager->getForAdherent($this->getUser());

        $summary->toggleShowingRecentActivities();
        $this->summaryManager->updateSummary($summary);
        $this->addFlash('info', 'summary.step.success');

        return $this->redirectToRoute('app_summary_manager_index', ['slug' => $summary->getSlug()]);
    }

    /**
     * @Route("/experience/{id}", defaults={"id": null}, name="app_summary_manager_handle_experience", methods={"GET", "POST"})
     */
    public function handleExperienceAction(Request $request, JobExperienceRepository $repository, ?int $id): Response
    {
        $experience = null;

        if ($id) {
            if (!$experience = $repository->find($id)) {
                throw $this->createNotFoundException();
            }
        }

        $summary = $this->summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(JobExperienceType::class, $experience, [
            'summary' => $summary,
            'collection' => $summary->getExperiences(),
            'add_submit_button' => false,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->summaryManager->updateExperiences($summary, $experience ?: $form->getData());
            $this->addFlash('info', 'summary.handle_experience.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/handle_experience.html.twig', [
            'experience_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/experience/{id}/supprimer", name="app_summary_manager_remove_experience", methods={"DELETE"})
     */
    public function removeExperienceAction(Request $request, JobExperience $experience): Response
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_EXPERIENCE_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->summaryManager->removeExperience($this->getUser(), $experience)) {
            $this->addFlash('info', 'summary.remove_experience.success');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/formation/{id}", defaults={"id": null}, name="app_summary_manager_handle_training", methods={"GET", "POST"})
     */
    public function handleTrainingAction(Request $request, TrainingRepository $repository, ?int $id): Response
    {
        $training = null;

        if ($id) {
            if (!$training = $repository->find($id)) {
                throw $this->createNotFoundException();
            }
        }

        $summary = $this->summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(TrainingType::class, $training, [
            'summary' => $summary,
            'collection' => $summary->getTrainings(),
            'add_submit_button' => false,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->summaryManager->updateTrainings($summary, $training ?: $form->getData());
            $this->addFlash('info', 'summary.handle_training.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/handle_training.html.twig', [
            'training_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/formation/{id}/supprimer", name="app_summary_manager_remove_training", methods={"DELETE"})
     */
    public function removeTrainingAction(Request $request, Training $training): Response
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_TRAINING_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->summaryManager->removeTraining($this->getUser(), $training)) {
            $this->addFlash('info', 'summary.remove_training.success');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/langue/{id}", defaults={"id": null}, name="app_summary_manager_handle_language", methods={"GET", "POST"})
     */
    public function handleLanguageAction(Request $request, LanguageRepository $repository, ?int $id): Response
    {
        $language = null;

        if ($id) {
            if (!$language = $repository->find($id)) {
                throw $this->createNotFoundException();
            }
        }

        $form = $this->createForm(LanguageType::class, $language);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->summaryManager->updateLanguages($this->getUser(), $language ?: $form->getData());
            $this->addFlash('info', 'summary.handle_language.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/handle_language.html.twig', [
            'language_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/langue/{id}/supprimer", name="app_summary_manager_remove_language", methods={"DELETE"})
     */
    public function removeLanguageAction(Request $request, Language $language): Response
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_LANGUAGE_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->summaryManager->removeLanguage($this->getUser(), $language)) {
            $this->addFlash('info', 'summary.remove_language.success');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/publier", name="app_summary_manager_publish", methods={"GET"})
     */
    public function publishAction(): Response
    {
        $summary = $this->summaryManager->getForAdherent($this->getUser());

        $this->summaryManager->publishSummary($summary);

        $this->addFlash('info', 'summary.published.success');

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/depublier", name="app_summary_manager_unpublish", methods={"GET"})
     */
    public function unpublishAction(): Response
    {
        if ($this->summaryManager->unpublishSummaryForAdherent($this->getUser())) {
            $this->addFlash('info', 'summary.unpublished.success');
        } else {
            $this->addFlash('info', 'summary.unpublished.error');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/competences/autocompletion",
     *     name="app_summary_manager_skills_autocomplete",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function skillsAutocompleteAction(
        Request $request,
        SlugifyInterface $slugify,
        SkillRepository $repository
    ): Response {
        return new JsonResponse($repository->findAvailableSkillsFor(
            $slugify->slugify($request->query->get('term')),
            $this->getUser()
        ));
    }

    /**
     * @Route("/{step}", name="app_summary_manager_step", methods={"GET", "POST"})
     */
    public function stepAction(Request $request, string $step): Response
    {
        if (!SummaryType::stepExists($step)) {
            throw $this->createNotFoundException(sprintf('Invalid step "%s", known steps are "%s".', $step, implode('", "', SummaryType::STEPS)));
        }

        $summary = $this->summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(SummaryType::class, $summary, [
            'step' => $step,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->summaryManager->updateSummary($summary);
            $this->addFlash('info', 'summary.step.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/step.html.twig', [
            'summary_form' => $form->createView(),
            'step' => $step,
        ]);
    }

    /**
     * @Route("/photo/supprimer", name="app_summary_manager_remove_photo", methods={"DELETE"})
     */
    public function removePhotoAction(Request $request): Response
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_PHOTO_TOKEN, $request);

        if (!$form->isValid()) {
            throw $this->createNotFoundException('Invalid token.');
        }

        $summary = $this->summaryManager->getForAdherent($this->getUser());

        if ($this->summaryManager->removePhoto($summary)) {
            $this->addFlash('info', 'summary.remove_photo.success');
        } else {
            $this->addFlash('error', 'summary.remove_photo.error');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }
}
