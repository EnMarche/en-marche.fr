<?php

namespace App\Normalizer;

use App\Entity\FollowedInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FollowersCountNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const NORMALIZATION_GROUP = 'followers_count';
    private const ALREADY_CALLED = 'FOLLOWERS_COUNT_NORMALIZER_ALREADY_CALLED';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var FollowedInterface $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array(self::NORMALIZATION_GROUP, $context['groups'])) {
            $data['followers_count'] = (int) $this->entityManager->getRepository(\get_class($object))
                ->createQueryBuilder('o')
                ->select('COUNT(DISTINCT f.id)')
                ->join('o.followers', 'f')
                ->andWhere('o.id = :id')
                ->setParameter('id', $object->getId())
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof FollowedInterface;
    }
}
