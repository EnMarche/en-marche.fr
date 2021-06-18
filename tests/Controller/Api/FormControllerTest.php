<?php

namespace Tests\App\Controller\Api;

use App\Form\AdherentRegistrationType;
use App\Form\BecomeAdherentType;
use App\Form\DonationRequestType;
use App\Form\UserRegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class FormControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function validateProvider(): iterable
    {
        yield AdherentRegistrationType::class => [
            urlencode(AdherentRegistrationType::class),
            [
                'adherent_registration' => [
                    'firstName' => '123',
                    'lastName' => 't',
                    'emailAddress' => [
                        'first' => 'toto@too.fr',
                        'second' => 'titi',
                    ],
                    'phone' => [
                        'country' => 'FR',
                        'number' => 'fail',
                    ],
                ],
            ],
            [
                'children' => [
                    'lastName' => [
                        'errors' => [
                            'Votre prénom doit comporter au moins 2 caractères.',
                        ],
                    ],
                    'emailAddress' => [
                        'children' => [
                            'second' => [
                                'errors' => [
                                    'Les adresses email ne correspondent pas.',
                                ],
                            ],
                        ],
                    ],
                    'phone' => [
                        'errors' => [
                            'Cette valeur n\'est pas un numéro de téléphone valide.',
                        ],
                    ],
                ],
            ],
        ];
        yield BecomeAdherentType::class => [
            urlencode(BecomeAdherentType::class),
            [
                'become_adherent' => [
                    'phone' => [
                        'country' => 'FR',
                        'number' => 'fail',
                    ],
                ],
            ],
            [
                'children' => [
                    'phone' => [
                        'errors' => [
                            'Cette valeur n\'est pas un numéro de téléphone valide.',
                        ],
                    ],
                ],
            ],
        ];
        yield UserRegistrationType::class => [
            urlencode(UserRegistrationType::class),
            [
                'user_registration' => [
                    'firstName' => '123',
                    'lastName' => 't',
                    'emailAddress' => [
                        'first' => 'toto@too.fr',
                        'second' => 'titi',
                    ],
                ],
            ],
            [
                'children' => [
                    'lastName' => [
                        'errors' => [
                            'Votre prénom doit comporter au moins 2 caractères.',
                        ],
                    ],
                    'emailAddress' => [
                        'children' => [
                            'second' => [
                                'errors' => [
                                    'Les adresses email ne correspondent pas.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        yield DonationRequestType::class => [
            urlencode(DonationRequestType::class),
            [
                'app_donation' => [
                    'firstName' => '123',
                    'lastName' => 't',
                    'emailAddress' => 'fail',
                ],
            ],
            [
                'children' => [
                    'lastName' => [
                        'errors' => [
                            'Votre prénom doit comporter au moins 2 caractères.',
                        ],
                    ],
                    'emailAddress' => [
                        'errors' => [
                            'Ceci n\'est pas une adresse e-mail valide.',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate(string $urlKey, array $requestParams, array $expectedResult): void
    {
        $this->client->request(
            Request::METHOD_POST,
            "/api/form/validate/$urlKey",
            $requestParams,
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertJson($this->client->getResponse()->getContent());
        $result = \GuzzleHttp\json_decode($this->client->getResponse()->getContent(), true);

        static::assertArrayValues($expectedResult, $result);
    }

    public static function assertArrayValues(array $expected, array $actual): void
    {
        foreach ($expected as $key => $item) {
            static::assertArrayHasKey($key, $actual);

            if (\is_array($item)) {
                static::assertArrayValues($item, $actual[$key]);
            }

            if (\is_string($item)) {
                static::assertSame($item, $actual[$key]);
            }
        }
    }
}
