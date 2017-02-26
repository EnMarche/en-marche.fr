<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeRepository;

class ManagedUserExporter
{
    private $committeeRepository;

    public function __construct(CommitteeRepository $committeeRepository)
    {
        $this->committeeRepository = $committeeRepository;
    }

    /**
     * @param ManagedUser[] $managedUsers
     *
     * @return string
     */
    public function exportAsJson(array $managedUsers): string
    {
        $registry = $this->createCommitteesRegistry();
        $data = [];

        foreach ($managedUsers as $user) {
            $isHost = $user->isAdherent() && $user->getOriginal()->isHost();

            $data[] = [
                'type' => $user->getType(),
                'id' => $user->getId(),
                'postalCode' => $user->getPostalCode(),
                'email' => [
                    'label' => $isHost ? $user->getEmail() : '',
                    'url' => 'mailto:'.($isHost ? $user->getEmail() : ''),
                ],
                'phone' => $isHost ? (string) $user->getOriginal()->getPhone() : '',
                'name' => $user->getPartialName() ?: '',
                'age' => $user->getAge() ?: '',
                'city' => $user->getCity() ?: '',
                'country' => $user->getCountry() ?: '',
                'committees' => $this->createCommitteesListFor($user, $registry),
                'emailsSubscription' => $user->hasReferentsEmailsSubscription() ? 'Oui' : 'Non',
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }

    private function createCommitteesRegistry(): array
    {
        $committees = $this->committeeRepository->findApprovedCommittees();
        $registry = [];

        foreach ($committees as $committee) {
            $registry[$committee->getUuid()->toString()] = $committee;
        }

        return $registry;
    }

    /**
     * @param ManagedUser $user
     * @param Committee[] $registry
     *
     * @return string
     */
    private function createCommitteesListFor(ManagedUser $user, array $registry): string
    {
        if (!$user->isAdherent()) {
            return '(marcheur)';
        }

        $committees = [];

        foreach ($user->getOriginal()->getMemberships() as $membership) {
            if (isset($registry[$membership->getCommitteeUuid()->toString()])) {
                $committees[] = $registry[$membership->getCommitteeUuid()->toString()]->getName();
            }
        }

        return implode(', ', $committees);
    }
}
