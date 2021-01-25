<?php

namespace App\Repository\IdeasWorkshop;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\IdeasWorkshop\Answer;
use App\Entity\IdeasWorkshop\AuthorCategoryEnum;
use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\IdeaStatusEnum;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\Entity\IdeasWorkshop\VoteTypeEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class IdeaRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Idea::class);
    }

    public function findOneByUuid(string $uuid, bool $disabledEntity = false): Idea
    {
        if ($disabledEntity && $this->_em->getFilters()->isEnabled('enabled')) {
            $this->_em->getFilters()->disable('enabled');
        }

        static::validUuid($uuid);

        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function getContributors(Idea $idea): array
    {
        return array_unique(
            array_merge(
                $this->getThreadContributors($idea),
                $this->getCommentsContributors($idea)
            ),
            \SORT_REGULAR
        );
    }

    public function countContributors(Idea $idea, Adherent $adherent = null): array
    {
        $sqlIdeaContributors = <<<'SQL'
        (
            SELECT threadComment.author_id
            FROM ideas_workshop_comment threadComment
            INNER JOIN ideas_workshop_thread thread ON thread.id = threadComment.thread_id
            INNER JOIN ideas_workshop_answer answer ON answer.id = thread.answer_id
            INNER JOIN ideas_workshop_idea idea ON idea.id = answer.idea_id
            WHERE idea.id = :idea AND threadComment.enabled = true AND thread.enabled = true
            AND threadComment.deleted_at IS NULL AND thread.deleted_at IS NULL
        )
        UNION 
        (
            SELECT thread.author_id
            FROM ideas_workshop_thread thread 
            INNER JOIN ideas_workshop_answer answer ON answer.id = thread.answer_id
            INNER JOIN ideas_workshop_idea idea ON idea.id = answer.idea_id
            WHERE idea.id = :idea AND thread.enabled = true AND thread.deleted_at IS NULL
        )
SQL;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sqlIdeaContributors);
        $stmt->bindValue(':idea', $idea->getId());
        $stmt->execute();

        $result = ['count' => \count($ids = $stmt->fetchAll(\PDO::FETCH_COLUMN))];
        if ($adherent) {
            $result['contributed_by_me'] = \in_array($adherent->getId(), array_values($ids));
        }

        return $result;
    }

    private function getThreadContributors(Idea $idea): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(Adherent::class, 'adherent')
            ->select('adherent')
            ->join(Thread::class, 'thread', Join::WITH, 'thread.author = adherent.id')
            ->join(Answer::class, 'answer', Join::WITH, 'thread.answer = answer.id')
            ->join('answer.idea', 'idea')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->andWhere('thread.deletedAt IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    private function getCommentsContributors(Idea $idea): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(Adherent::class, 'adherent')
            ->select('adherent')
            ->join(ThreadComment::class, 'comment', Join::WITH, 'comment.author = adherent.id')
            ->join('comment.thread', 'thread')
            ->join(Answer::class, 'answer', Join::WITH, 'thread.answer = answer.id')
            ->join('answer.idea', 'idea')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->andWhere('comment.deletedAt IS NULL')
            ->andWhere('thread.deletedAt IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countVotesByType(Idea $idea): array
    {
        $votes = $this
            ->createQueryBuilder('idea')
            ->select('vote.type, COUNT(idea) as count')
            ->innerJoin('idea.votes', 'vote')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->groupBy('vote.type')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_replace(
            array_fill_keys(VoteTypeEnum::toArray(), 0),
            array_column($votes, 'count', 'type')
        );
    }

    public function getAdherentVotesForIdea(Idea $idea, Adherent $adherent): array
    {
        $votes = $this
            ->createQueryBuilder('idea')
            ->select('vote.type, vote.id')
            ->innerJoin('idea.votes', 'vote')
            ->where('idea = :idea')
            ->andWhere('vote.author = :author')
            ->setParameter('idea', $idea)
            ->setParameter('author', $adherent)
            ->groupBy('vote.type')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_reduce($votes, function ($result, $vote) {
            $result[$vote['type']] = $vote['id'];

            return $result;
        }, []);
    }

    public function removeNotFinalizedIdeas(Adherent $author): void
    {
        $qb = $this->createQueryBuilder('idea');

        $qb->delete()
            ->set('idea.author', $qb->expr()->literal(null))
            ->where('idea.author = :author')
            ->andWhere('idea.finalizedAt IS NULL OR idea.finalizedAt > :now')
            ->setParameter('author', $author)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute()
        ;
    }

    public function anonymizeFinalizedIdeas(Adherent $author): void
    {
        $this->createQueryBuilder('idea')
            ->update()
            ->set('idea.author', 'null')
            ->where('idea.author = :author')
            ->andWhere('idea.finalizedAt IS NOT NULL AND idea.finalizedAt <= :now')
            ->setParameter('author', $author)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @param QueryBuilder|ProxyQuery $queryBuilder
     */
    public function addStatusFilter($queryBuilder, string $alias, string $status): void
    {
        switch ($status) {
            case IdeaStatusEnum::UNPUBLISHED:
                $queryBuilder->andWhere(sprintf('%s.enabled = false', $alias));
                break;
            case IdeaStatusEnum::DRAFT:
                $queryBuilder->andWhere(sprintf('%s.publishedAt IS NULL', $alias));
                break;
            case IdeaStatusEnum::PENDING:
                $queryBuilder
                    ->andWhere(sprintf('%s.publishedAt IS NOT NULL AND %s.finalizedAt > :now', $alias, $alias))
                    ->setParameter('now', new \DateTime())
                ;
                break;
            case IdeaStatusEnum::FINALIZED:
                $queryBuilder->andWhere(sprintf('%s.finalizedAt <= :now', $alias))
                    ->setParameter('now', new \DateTime())
                ;
                break;
        }
    }

    public function updateAuthorCategoryForIdeasOf(Adherent $adherent): void
    {
        // We disable 'enabled' doctrine filter to update even disabled ideas
        if ($isFilterEnabled = $this->_em->getFilters()->isEnabled('enabled')) {
            $this->_em->getFilters()->disable('enabled');
        }

        $qb = $this->createQueryBuilder('idea');
        $categoryType = null;

        if ($adherent->isLarem()) {
            $categoryType = AuthorCategoryEnum::QG;
        } elseif ($adherent->isElected()) {
            $categoryType = AuthorCategoryEnum::ELECTED;
        }

        if ($categoryType) {
            $qb->update()
                ->set('idea.authorCategory', $qb->expr()->literal($categoryType))
                ->where('idea.author = :adherent')
                ->setParameter('adherent', $adherent)
                ->getQuery()
                ->execute()
            ;
        } else {
            $qb->update()
                ->set('idea.authorCategory', $qb->expr()->literal(AuthorCategoryEnum::COMMITTEE))
                ->where('idea.author = :adherent AND idea.committee IS NOT NULL')
                ->setParameter('adherent', $adherent)
                ->getQuery()
                ->execute()
            ;

            $qb->update()
                ->set('idea.authorCategory', $qb->expr()->literal(AuthorCategoryEnum::ADHERENT))
                ->where('idea.author = :adherent AND idea.committee IS NULL')
                ->setParameter('adherent', $adherent)
                ->getQuery()
                ->execute()
            ;
        }

        // We enable 'enabled' doctrine filter, if we disabled it at the beginning of the method
        if ($isFilterEnabled) {
            $this->_em->getFilters()->enable('enabled');
        }
    }

    public function getIdeasProposalsFromAdherent(Adherent $adherent, int $page = 1, int $limit = 5): PaginatorInterface
    {
        $queryBuilder = $this
            ->createQueryBuilder('idea')
            ->where('idea.author = :author')
            ->setParameter('author', $adherent)
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    public function getIdeaContributionsFromAdherent(
        Adherent $adherent,
        int $page = null,
        int $limit = null
    ): PaginatorInterface {
        $queryBuilder = $this->createQueryBuilder('idea')
            ->innerJoin('idea.answers', 'answer')
            ->innerJoin('answer.threads', 'thread')
            ->leftJoin('thread.comments', 'comment')
            ->where('thread.author = :author')
            ->orWhere('comment.author = :author')
            ->setParameter('author', $adherent)
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }
}
