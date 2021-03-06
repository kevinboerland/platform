<?php

namespace Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\Environment\Entity\TestArticle;
use Oro\Bundle\ApiBundle\Tests\Functional\Environment\Model\TestMagazineModel2;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;

/**
 * @dbIsolationPerTest
 */
class ModelWithAssociationsAsAttributesWithSpecifiedInConfigFieldsTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures(['@OroApiBundle/Tests/Functional/DataFixtures/test_magazine.yml']);
        $this->appendEntityConfig(
            TestMagazineModel2::class,
            [
                'fields' => [
                    'articles'    => [
                        'exclusion_policy' => 'all',
                        'fields'           => [
                            'headline' => [
                                'data_type' => 'string',
                                'form_options' => [
                                    'constraints' => [['NotBlank' => []]]
                                ]
                            ]
                        ]
                    ],
                    'bestArticle' => [
                        'exclusion_policy' => 'all',
                        'fields'           => [
                            'headline' => [
                                'data_type' => 'string',
                                'form_options' => [
                                    'constraints' => [['NotBlank' => []]]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @param string $headline
     */
    private function assertArticleExists(string $headline): void
    {
        /** @var TestArticle|null $article */
        $article = $this->getEntityManager()->getRepository(TestArticle::class)
            ->findOneBy(['headline' => $headline]);
        self::assertTrue(null !== $article, sprintf('The article "%s" does not exist.', $headline));
    }

    public function testGet()
    {
        $magazineId = $this->getReference('magazine1')->getId();
        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Magazine 1',
                        'articles'    => [
                            [
                                'headline' => 'Article 1'
                            ],
                            [
                                'headline' => 'Article 2'
                            ],
                            [
                                'headline' => 'Article 3'
                            ]
                        ],
                        'bestArticle' => [
                            'headline' => 'Article 1'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetWithEmptyData()
    {
        $magazineId = $this->getReference('magazine2')->getId();
        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Magazine 2',
                        'articles'    => [],
                        'bestArticle' => null
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateForToOneAssociation()
    {
        $magazineId = $this->getReference('magazine1')->getId();
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'id'         => (string)$magazineId,
                'attributes' => [
                    'name'        => 'Updated Magazine 1',
                    'bestArticle' => [
                        'headline' => 'Updated Article 1'
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId],
            $data
        );

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Updated Magazine 1',
                        'articles'    => [
                            [
                                'headline' => 'Updated Article 1'
                            ],
                            [
                                'headline' => 'Article 2'
                            ],
                            [
                                'headline' => 'Article 3'
                            ]
                        ],
                        'bestArticle' => [
                            'headline' => 'Updated Article 1'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateForToOneAssociationWhenPreviousValueIsNull()
    {
        $magazineId = $this->getReference('magazine2')->getId();
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'id'         => (string)$magazineId,
                'attributes' => [
                    'bestArticle' => [
                        'headline' => 'Updated New Article'
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId],
            $data
        );

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Magazine 2',
                        'articles'    => [],
                        'bestArticle' => [
                            'headline' => 'Updated New Article'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateForToOneAssociationWhenNewValueIsNull()
    {
        self::markTestSkipped('Need to find a way to set null');
        $magazineId = $this->getReference('magazine1')->getId();
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'id'         => (string)$magazineId,
                'attributes' => [
                    'bestArticle' => null
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId],
            $data
        );

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Magazine 1',
                        'articles'    => [
                            [
                                'headline' => 'Article 1'
                            ],
                            [
                                'headline' => 'Article 2'
                            ],
                            [
                                'headline' => 'Article 3'
                            ]
                        ],
                        'bestArticle' => null
                    ]
                ]
            ],
            $response
        );
        $this->assertArticleExists('Article 1');
    }

    public function testUpdateForToManyAssociation()
    {
        $magazineId = $this->getReference('magazine1')->getId();
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'id'         => (string)$magazineId,
                'attributes' => [
                    'name'     => 'Updated Magazine 1',
                    'articles' => [
                        [
                            'headline' => 'Updated Article 1'
                        ],
                        [
                            'headline' => 'Updated Article 2'
                        ]
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId],
            $data
        );

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Updated Magazine 1',
                        'articles'    => [
                            [
                                'headline' => 'Updated Article 1'
                            ],
                            [
                                'headline' => 'Updated Article 2'
                            ]
                        ],
                        'bestArticle' => [
                            'headline' => 'Updated Article 1'
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertArticleExists('Article 3');
    }

    public function testUpdateForToManyAssociationWhenPreviousValueIsEmpty()
    {
        $magazineId = $this->getReference('magazine2')->getId();
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'id'         => (string)$magazineId,
                'attributes' => [
                    'articles' => [
                        [
                            'headline' => 'Updated New Article 1'
                        ],
                        [
                            'headline' => 'Updated New Article 2'
                        ]
                    ]
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId],
            $data
        );

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Magazine 2',
                        'articles'    => [
                            [
                                'headline' => 'Updated New Article 1'
                            ],
                            [
                                'headline' => 'Updated New Article 2'
                            ]
                        ],
                        'bestArticle' => null
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateForToManyAssociationWhenNewValueIsEmpty()
    {
        $magazineId = $this->getReference('magazine1')->getId();
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'id'         => (string)$magazineId,
                'attributes' => [
                    'articles' => []
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId],
            $data
        );

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'Magazine 1',
                        'articles'    => [],
                        'bestArticle' => [
                            'headline' => 'Article 1'
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertArticleExists('Article 1');
        $this->assertArticleExists('Article 2');
        $this->assertArticleExists('Article 3');
    }

    public function testCreateForToOneAssociation()
    {
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'attributes' => [
                    'name'        => 'New Magazine 1',
                    'bestArticle' => [
                        'headline' => 'New Article 1'
                    ]
                ]
            ]
        ];
        $response = $this->post(
            ['entity' => 'testapimagazinemodel2'],
            $data
        );
        $magazineId = (int)$this->getResourceId($response);

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'New Magazine 1',
                        'articles'    => [],
                        'bestArticle' => [
                            'headline' => 'New Article 1'
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testCreateForToManyAssociation()
    {
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'attributes' => [
                    'name'     => 'New Magazine 1',
                    'articles' => [
                        [
                            'headline' => 'New Article 1'
                        ],
                        [
                            'headline' => 'New Article 2'
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->post(
            ['entity' => 'testapimagazinemodel2'],
            $data
        );
        $magazineId = (int)$this->getResourceId($response);

        $response = $this->get(
            ['entity' => 'testapimagazinemodel2', 'id' => (string)$magazineId]
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'testapimagazinemodel2',
                    'id'         => (string)$magazineId,
                    'attributes' => [
                        'name'        => 'New Magazine 1',
                        'articles'    => [
                            [
                                'headline' => 'New Article 1'
                            ],
                            [
                                'headline' => 'New Article 2'
                            ]
                        ],
                        'bestArticle' => null
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToCreateForToOneAssociationWithValidationError()
    {
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'attributes' => [
                    'bestArticle' => [
                        'headline' => ''
                    ]
                ]
            ]
        ];
        $response = $this->post(
            ['entity' => 'testapimagazinemodel2'],
            $data,
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/bestArticle/headline']
            ],
            $response
        );
    }

    public function testTryToCreateForToManyAssociationWithValidationError()
    {
        $data = [
            'data' => [
                'type'       => 'testapimagazinemodel2',
                'attributes' => [
                    'articles' => [
                        [
                            'headline' => 'New Article 1'
                        ],
                        [
                            'headline' => ''
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->post(
            ['entity' => 'testapimagazinemodel2'],
            $data,
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/articles/1/headline']
            ],
            $response
        );
    }
}
