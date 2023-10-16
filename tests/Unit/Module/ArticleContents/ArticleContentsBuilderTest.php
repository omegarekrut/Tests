<?php

namespace Tests\Unit\Module\ArticleContents;

use App\Module\ArticleContents\ArticleContentsBuilder;
use Tests\Unit\TestCase;

class ArticleContentsBuilderTest extends TestCase
{
    /**
     * @dataProvider getCases
     */
    public function testBuildArticleContentsFromHeadersArray(array $linear, array $expectedRecursive): void
    {
        $builder = new ArticleContentsBuilder();

        $this->assertEquals(
            $expectedRecursive,
            $builder->buildArticleContentsFromHeadersArray($linear)
        );
    }

    public function getCases(): array
    {
        return [
            'try to convert with partial wrong headers structure' => [
                [
                    [
                        'level' => 3,
                        'header' => 'word',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'siberian',
                        'id' => ''
                    ],
                    [
                        'level' => 2,
                        'header' => 'some',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'think',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'about',
                        'id' => ''
                    ],
                    [
                        'level' => 2,
                        'header' => 'river',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'forest',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'and',
                        'id' => 'and-id'
                    ],
                    [
                        'level' => 2,
                        'header' => 'fishing',
                        'id' => ''
                    ],
                ],
                [
                    0 => [
                        'header' => 'some',
                        'id' => '',
                        'subheaders' => [
                            0 => [
                                'header' => 'think',
                                'id' => ''
                            ],
                            1 => [
                                'header' => 'about',
                                'id' => ''
                            ]
                        ]
                    ],
                    1 => [
                        'header' => 'river',
                        'id' => ''
                    ],
                    2 => [
                        'header' => 'fishing',
                        'id' => ''
                    ]
                ],
            ],
            'try to convert with excess h1 h5 headers' => [
                [
                    [
                        'level' => 1,
                        'header' => 'header h1',
                        'id' => ''
                    ],
                    [
                        'level' => 2,
                        'header' => 'some',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'same',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'about',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'siberian',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'and',
                        'id' => 'and-id'
                    ],
                    [
                        'level' => 5,
                        'header' => 'header h5',
                        'id' => ''
                    ],
                    [
                        'level' => 2,
                        'header' => 'fishing',
                        'id' => ''
                    ],
                ],
                [
                    0 => [
                        'header' => 'some',
                        'id' => '',
                        'subheaders' => [
                            0 => [
                                'header' => 'same',
                                'id' => ''
                            ],
                            1 => [
                                'header' => 'about',
                                'id' => '',
                                'subheaders' => [
                                    0 => [
                                        'header' => 'siberian',
                                        'id' => ''
                                    ],
                                    1 => [
                                        'header' => 'and',
                                        'id' => 'and-id'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    1 => [
                        'header' => 'fishing',
                        'id' => ''
                    ]
                ],
            ],
            'convert normal headers contents array' => [
                [
                    [
                        'level' => 2,
                        'header' => 'some',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'same',
                        'id' => ''
                    ],
                    [
                        'level' => 3,
                        'header' => 'about',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'siberian',
                        'id' => ''
                    ],
                    [
                        'level' => 4,
                        'header' => 'and',
                        'id' => 'and-id'
                    ],
                    [
                        'level' => 2,
                        'header' => 'fishing',
                        'id' => ''
                    ],
                ],
                [
                    0 => [
                        'header' => 'some',
                        'id' => '',
                        'subheaders' => [
                            0 => [
                                'header' => 'same',
                                'id' => ''
                            ],
                            1 => [
                                'header' => 'about',
                                'id' => '',
                                'subheaders' => [
                                    0 => [
                                        'header' => 'siberian',
                                        'id' => ''
                                    ],
                                    1 => [
                                        'header' => 'and',
                                        'id' => 'and-id'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    1 => [
                        'header' => 'fishing',
                        'id' => ''
                    ]
                ],
            ],
        ];
    }
}
