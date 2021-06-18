<?php

namespace Tests\App\Feed;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Feed\ArticleFeedGenerator;
use App\Feed\Exception\FeedGeneratorException;
use League\CommonMark\CommonMarkConverter;
use PHPUnit\Framework\TestCase;
use Suin\RSSWriter\FeedInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleFeedGeneratorTest extends TestCase
{
    protected $locale = 'fr';
    protected $ttl = 120;
    protected $urlGenerator;
    protected $markdownParser;

    /**
     * @var ArticleFeedGenerator
     */
    protected $feedGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->markdownParser = $this->createMock(CommonMarkConverter::class);

        $this->feedGenerator = new ArticleFeedGenerator(
            $this->locale,
            $this->ttl,
            $this->urlGenerator,
            $this->markdownParser
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->urlGenerator = null;
        $this->markdownParser = null;
        $this->feedGenerator = null;
    }

    /**
     * @dataProvider dataProviderGenerateInvalidInput
     */
    public function testGenerateInvalidInput($input)
    {
        $this->expectException(FeedGeneratorException::class);
        $this->feedGenerator->buildFeed($input);
    }

    /**
     * Return a feed without any content if no articles are available.
     */
    public function testGenerateEmptyArticles()
    {
        $this->urlGenerator->expects($this->never())
            ->method('generate')
        ;

        $this->assertInstanceOf(FeedInterface::class, $this->feedGenerator->buildFeed([]));
    }

    /**
     * Return a feed without any content if no articles are available.
     */
    public function testGenerate()
    {
        $article = $this->createMock(Article::class);
        $articleSlug = 'some-random-slug';
        $articleContent = 'kjdfskfjsdjd';
        $articlePublishDate = $this->createMock('DateTime');
        $articlePublishDateTimeStamp = 42;
        $category = $this->createMock(ArticleCategory::class);
        $categorySlug = 'category-slug';

        $article->expects($this->once())
            ->method('getSlug')
            ->will($this->returnValue($articleSlug))
        ;
        $article->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($articleContent))
        ;
        $article->expects($this->exactly(2)) // once for the channel + once for the item itself
            ->method('getPublishedAt')
            ->will($this->returnValue($articlePublishDate))
        ;
        $articlePublishDate->expects($this->exactly(2))
            ->method('format')
            ->with($this->equalTo('U'))
            ->will($this->returnValue($articlePublishDateTimeStamp))
        ;
        $article->expects($this->any())
            ->method('getCategory')
            ->will($this->returnValue($category))
        ;
        $category->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Category name'))
        ;
        $category->expects($this->once())
            ->method('getSlug')
            ->will($this->returnValue($categorySlug))
        ;

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->will($this->returnValueMap([
                ['homepage', [], UrlGeneratorInterface::ABSOLUTE_URL, 'https://en-marche.fr'],
                ['article_view', ['categorySlug' => $categorySlug, 'articleSlug' => $articleSlug], UrlGeneratorInterface::ABSOLUTE_URL, sprintf('https://en-marche.fr/articles/%s/%s', $categorySlug, $articleSlug)],
            ]))
        ;
        $this->markdownParser->expects($this->once())
            ->method('convertToHtml')
            ->with($this->equalTo($articleContent))
        ;

        $this->assertInstanceOf(FeedInterface::class, $this->feedGenerator->buildFeed([$article]));
    }

    public function dataProviderGenerateInvalidInput(): array
    {
        return [
            [null],
            [0],
            ['abcd'],
            [$this->createMock(Article::class)],
        ];
    }
}
