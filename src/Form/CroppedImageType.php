<?php

namespace App\Form;

use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CroppedImageType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('croppedImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();

            if (false !== strpos($data['croppedImage'], 'base64,')) {
                $imageData = explode('base64,', $data['croppedImage'], 2);
                $content = $imageData[1];
                $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
                file_put_contents($tmpFile, base64_decode($content));

                $data['image'] = new UploadedFile(
                    $tmpFile,
                    Uuid::uuid4()->toString().'.png',
                    str_replace([';', 'data:'], '', $imageData[0]),
                    null,
                    null,
                    true
                );

                unset($data['croppedImage']);
            } elseif (-1 == $data['croppedImage']) {
                unset($data['croppedImage'], $data['image']);
                /** @var BaseCandidacy $model */
                $model = $event->getForm()->getData();
                $model->setRemoveImage(true);
            }

            $event->setData($data);
        });

        $builder->addModelTransformer($this);
    }


    public function transform($value)
    {
        return dump($value);
    }

    public function reverseTransform($value)
    {
        return dump($value);
    }
}
