<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Serializer\ItemNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ItemNormalizerDecorator implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;

    public function __construct(ItemNormalizer $normalizer)
    {
        $this->decorated = $normalizer;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (
            isset($context[$this->decorated::OBJECT_TO_POPULATE], $context['api_allow_update'])
            && true !== $context['api_allow_update']
        ) {
            throw new InvalidArgumentException('Update is not allowed for this operation.');
        }

        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context['iri'] = true;

        return $this->decorated->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->decorated->setSerializer($serializer);
    }
}
