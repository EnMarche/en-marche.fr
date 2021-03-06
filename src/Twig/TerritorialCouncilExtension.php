<?php

namespace App\Twig;

use App\TerritorialCouncil\Candidacy\NationalCouncilCandidacyConfigurator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TerritorialCouncilExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_election_poll_vote', [TerritorialCouncilRuntime::class, 'getElectionPollVote']),
            new TwigFunction('get_territorial_council_candidates_stats', [TerritorialCouncilRuntime::class, 'getCandidatesStats']),
            new TwigFunction('get_votes_stats', [TerritorialCouncilRuntime::class, 'getVotesStats']),
            new TwigFunction('get_available_genders_for_candidacy', [NationalCouncilCandidacyConfigurator::class, 'getAvailableGenders']),
            new TwigFunction('get_needed_qualities_for_national_council_designation', [NationalCouncilCandidacyConfigurator::class, 'getNeededQualitiesForNationalCouncilDesignation']),
            new TwigFunction('get_all_coterr_candidacy_invitations_for_membership', [TerritorialCouncilRuntime::class, 'getAllCoterrCandidacyInvitationsForMembership']),
        ];
    }
}
