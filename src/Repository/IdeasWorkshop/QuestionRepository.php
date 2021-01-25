<?php

namespace App\Repository\IdeasWorkshop;

use App\Entity\IdeasWorkshop\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findMandatoryQuestions()
    {
        return $this->createQueryBuilder('question')
            ->innerJoin('question.guideline', 'guideline')
            ->where('question.required = true')
            ->getQuery()
            ->getResult()
        ;
    }
}
