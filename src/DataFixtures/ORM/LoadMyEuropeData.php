<?php

namespace App\DataFixtures\ORM;

use App\Entity\MyEuropeChoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMyEuropeData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $choices = self::createChoices();

        // Persist all the things
        foreach ($choices as $choice) {
            $manager->persist($choice);
        }

        $manager->flush();
    }

    public static function createChoices(): array
    {
        // Step 0
        $choices[] = self::createNoStepChoice(
            '28ceb6d3-ec64-4a58-99a4-71357600d07c',
            MyEuropeChoice::MAIL_INTRODUCTION_KEY,
            'Introduction',
            file_get_contents(__DIR__.'/../interactive/mail-introduction-my-europe.txt')
        );

        $choices[] = self::createNoStepChoice(
            '3d735d18-348c-4d02-8046-7976f86e5ecc',
            MyEuropeChoice::MAIL_CONCLUSION_KEY,
            'Conclusion',
            'La République En Marche a publié des documents plus précis à ce sujet (HYPERLIEN) et je reste pour ma part à ta disposition pour en reparler!'
        );

        $choices[] = self::createNoStepChoice(
            'a642dbc7-aba5-49e4-877a-06bc1ef23168',
            MyEuropeChoice::MAIL_COMMON_KEY,
            'Mesures communes',
            file_get_contents(__DIR__.'/../interactive/mail-common-my-europe.txt')
        );

        // Step 1
        $choices[] = self::createFirstStepChoice(
            '52b738ad-c078-4952-bea5-caba65b688f6',
            'S01C01',
            'Il bénéficie peut-être de l\'allocation adulte handicapé',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c01.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '642527cd-7427-41fa-959b-ab64ab50f0f5',
            'S01C02',
            'Il est en situation de précarité énergétique',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c02.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '1d5762da-220c-4a0f-8abc-d2f5b155748d',
            'S01C03',
            'Il bénéficie peut-être du minimum vieillesse',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c03.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '04236bb1-7a00-481e-ab18-1900e8d3344c',
            'S01C04',
            'Il s\'occupe seul d\'un enfant et bénéfice peut-être du complément mode de garde',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c04.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '0ebfffcc-1ea2-476c-bd8b-b7b32efe27cf',
            'S01C05',
            'Il bénéficie de la prime d\'activité',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c05.txt')
        );

        $choices[] = self::createFirstStepChoice(
            'eeb85893-ef5c-4e7f-ad64-a4f640a0a7ad',
            'S01C06',
            'Il souhaite changer une veille voiture',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c06.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '9b75b13c-06c9-437e-bc88-726c19ca7050',
            'S01C07',
            'Il a besoin d\'aides chez lui ou chez un proche',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c07.txt')
        );

        $choices[] = self::createFirstStepChoice(
            '0612691b-d6c6-4ed8-8d35-fac7f00e7046',
            'S01C08',
            'Il veut créer une entreprise',
            file_get_contents(__DIR__.'/../interactive/mail-me-s02c08.txt')
        );

        // Step 2
        $choices[] = self::createSecondStepChoice(
            '7fc4e370-1b81-47de-93d9-7e001213ceb6',
            'S02C01',
            'Le travail',
            file_get_contents(__DIR__.'/../interactive/mail-me-s03c01.txt')
        );

        $choices[] = self::createSecondStepChoice(
            'b966e9c5-7afe-49fe-945b-780fb9439e47',
            'S02C02',
            'La solidarité',
            file_get_contents(__DIR__.'/../interactive/mail-me-s03c02.txt')
        );

        $choices[] = self::createSecondStepChoice(
            'bcc44956-fa6c-4b32-b53f-0e843c42f2a4',
            'S02C03',
            'L\'écologie',
            file_get_contents(__DIR__.'/../interactive/mail-me-s03c03.txt')
        );

        $choices[] = self::createSecondStepChoice(
            '9d3972e4-bb24-4754-8909-5c15ec968279',
            'S02C04',
            'La responsabilité ',
            file_get_contents(__DIR__.'/../interactive/mail-me-s03c04.txt')
        );

        return $choices;
    }

    private static function createNoStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): MyEuropeChoice {
        return static::createChoice($uuid, 0, $key, $label, $content);
    }

    private static function createFirstStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): MyEuropeChoice {
        return static::createChoice($uuid, 1, $key, $label, $content);
    }

    private static function createSecondStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): MyEuropeChoice {
        return static::createChoice($uuid, 2, $key, $label, $content);
    }

    private static function createThirdStepChoice(
        string $uuid,
        string $key,
        string $label,
        string $content
    ): MyEuropeChoice {
        return static::createChoice($uuid, 3, $key, $label, $content);
    }

    private static function createChoice(
        string $uuid,
        int $step,
        string $key,
        string $label,
        string $content
    ): MyEuropeChoice {
        return new MyEuropeChoice(Uuid::fromString($uuid), $step, $key, $label, $content);
    }
}
