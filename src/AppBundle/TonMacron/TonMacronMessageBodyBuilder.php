<?php

namespace AppBundle\TonMacron;

use AppBundle\Repository\TonMacronChoiceRepository;

class TonMacronMessageBodyBuilder
{
    private $twig;
    private $repository;

    public function __construct(
        \Twig_Environment $twig,
        TonMacronChoiceRepository $repository
    ) {
        $this->twig = $twig;
        $this->repository = $repository;
    }

    public function buildMessageBody(InvitationProcessor $invitation): void
    {
        $invitation->messageContent = $this->twig->render('campaign/ton_macron.html.twig', [
            'introduction' => $this->repository->findMailIntroduction(),
            'gender_choice' => $this->repository->findGenderChoice($invitation->friendGender),
            'conclusion' => $this->repository->findMailConclusion(),
            'invitation' => $invitation,
        ]);
    }
}
