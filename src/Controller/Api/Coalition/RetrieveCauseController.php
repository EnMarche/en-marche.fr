<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Coalition\Cause;
use App\Repository\Coalition\CauseRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RetrieveCauseController
{
    private $causeRepository;

    public function __construct(CauseRepository $causeRepository)
    {
        $this->causeRepository = $causeRepository;
    }

    public function __invoke(string $uuid): ?Cause
    {
        if (false === Uuid::isValid($uuid)) {
            $data = $this->causeRepository->findOneBy(['slug' => $uuid]);
        } else {
            $data = $this->causeRepository->findOneByUuid($uuid);
        }

        if (null === $data || !$data->isApproved()) {
            throw new NotFoundHttpException(sprintf('Cause with id or slug %s not found.', $uuid));
        }

        return $data;
    }
}
