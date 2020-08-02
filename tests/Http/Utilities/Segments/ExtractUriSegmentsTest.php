<?php

declare(strict_types=1);

namespace Tests\Http\Utilities\Segments;

use App\Http\Utilities\Segments\ExtractUriSegments;
use App\Http\Utilities\Segments\UriSegments;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class ExtractUriSegmentsTest extends TestCase
{
    private ExtractUriSegments $extractUriSegments;

    /** @var MockObject&UriInterface */
    private $uriInterface;

    public function testDoubleInstantiation(): void
    {
        $uriSegments = new UriSegments(
            [],
            [],
            123
        );

        self::expectException(LogicException::class);

        self::expectExceptionMessage(
            'UriSegments can only be initialized once'
        );

        $uriSegments->__construct(
            [],
            [],
            123
        );
    }

    public function testGetSegmentInvalidSegmentNum(): void
    {
        $this->uriInterface->method('getPath')->willReturn(
            '/test/url/path/foo'
        );

        $uriSegments = ($this->extractUriSegments)($this->uriInterface);

        self::expectException(LogicException::class);

        self::expectExceptionMessage(
            'Segment number must be greater than 0'
        );

        $uriSegments->getSegment(0);
    }

    public function testNoSegments(): void
    {
        $this->uriInterface->method('getPath')->willReturn('');

        $uriSegments = ($this->extractUriSegments)($this->uriInterface);

        self::assertSame(
            [],
            $uriSegments->getSegments()
        );

        self::assertNull($uriSegments->getSegment(1));
        self::assertNull($uriSegments->getSegment(2));

        self::assertNull($uriSegments->getLastSegment());

        self::assertSame(0, $uriSegments->getTotalSegments());

        self::assertSame('', $uriSegments->getPath());

        self::assertSame(
            '',
            $uriSegments->getPathFromSegmentSlice(2, 1)
        );

        self::assertSame(
            [],
            $uriSegments->getSegmentsSansPagination()
        );

        self::assertNull($uriSegments->getLastSegmentSansPagination());

        self::assertSame(0, $uriSegments->getTotalSegmentsSansPagination());

        self::assertSame('', $uriSegments->getPathSansPagination());

        self::assertSame(1, $uriSegments->getPageNum());
    }

    public function testPagination(): void
    {
        $this->uriInterface->method('getPath')->willReturn('/page/3');

        $uriSegments = ($this->extractUriSegments)($this->uriInterface);

        self::assertSame(
            [
                'page',
                '3',
            ],
            $uriSegments->getSegments()
        );

        self::assertSame('page', $uriSegments->getSegment(1));
        self::assertSame('3', $uriSegments->getSegment(2));
        self::assertNull($uriSegments->getSegment(3));
        self::assertNull($uriSegments->getSegment(4));

        self::assertSame('3', $uriSegments->getLastSegment());

        self::assertSame(2, $uriSegments->getTotalSegments());

        self::assertSame('page/3', $uriSegments->getPath());

        self::assertSame(
            '3',
            $uriSegments->getPathFromSegmentSlice(2, 1)
        );

        self::assertSame(
            [],
            $uriSegments->getSegmentsSansPagination()
        );

        self::assertNull($uriSegments->getLastSegmentSansPagination());

        self::assertSame(0, $uriSegments->getTotalSegmentsSansPagination());

        self::assertSame('', $uriSegments->getPathSansPagination());

        self::assertSame(3, $uriSegments->getPageNum());
    }

    public function testFourSegments(): void
    {
        $this->uriInterface->method('getPath')->willReturn(
            '/test/url/path/foo'
        );

        $uriSegments = ($this->extractUriSegments)($this->uriInterface);

        self::assertSame(
            [
                'test',
                'url',
                'path',
                'foo',
            ],
            $uriSegments->getSegments()
        );

        self::assertSame('test', $uriSegments->getSegment(1));
        self::assertSame('url', $uriSegments->getSegment(2));
        self::assertSame('path', $uriSegments->getSegment(3));
        self::assertSame('foo', $uriSegments->getSegment(4));
        self::assertNull($uriSegments->getSegment(5));
        self::assertNull($uriSegments->getSegment(6));

        self::assertSame('foo', $uriSegments->getLastSegment());

        self::assertSame(4, $uriSegments->getTotalSegments());

        self::assertSame('test/url/path/foo', $uriSegments->getPath());

        self::assertSame(
            'url/path',
            $uriSegments->getPathFromSegmentSlice(2, 1)
        );

        self::assertSame(
            'url/path/foo',
            $uriSegments->getPathFromSegmentSlice(3, 1)
        );

        self::assertSame(
            'path/foo',
            $uriSegments->getPathFromSegmentSlice(3, 2)
        );

        self::assertSame(
            [
                'test',
                'url',
                'path',
                'foo',
            ],
            $uriSegments->getSegmentsSansPagination()
        );

        self::assertSame('foo', $uriSegments->getLastSegmentSansPagination());

        self::assertSame(4, $uriSegments->getTotalSegmentsSansPagination());

        self::assertSame('test/url/path/foo', $uriSegments->getPathSansPagination());

        self::assertSame(1, $uriSegments->getPageNum());
    }

    public function testFourSegmentsWithPagination(): void
    {
        $this->uriInterface->method('getPath')->willReturn(
            '/test/url/path/foo/page/987'
        );

        $uriSegments = ($this->extractUriSegments)($this->uriInterface);

        self::assertSame(
            [
                'test',
                'url',
                'path',
                'foo',
                'page',
                '987',
            ],
            $uriSegments->getSegments()
        );

        self::assertSame('test', $uriSegments->getSegment(1));
        self::assertSame('url', $uriSegments->getSegment(2));
        self::assertSame('path', $uriSegments->getSegment(3));
        self::assertSame('foo', $uriSegments->getSegment(4));
        self::assertSame('page', $uriSegments->getSegment(5));
        self::assertSame('987', $uriSegments->getSegment(6));
        self::assertNull($uriSegments->getSegment(7));
        self::assertNull($uriSegments->getSegment(8));

        self::assertSame('987', $uriSegments->getLastSegment());

        self::assertSame(6, $uriSegments->getTotalSegments());

        self::assertSame('test/url/path/foo/page/987', $uriSegments->getPath());

        self::assertSame(
            'url/path',
            $uriSegments->getPathFromSegmentSlice(2, 1)
        );

        self::assertSame(
            'url/path/foo',
            $uriSegments->getPathFromSegmentSlice(3, 1)
        );

        self::assertSame(
            'path/foo/page',
            $uriSegments->getPathFromSegmentSlice(3, 2)
        );

        self::assertSame(
            [
                'test',
                'url',
                'path',
                'foo',
            ],
            $uriSegments->getSegmentsSansPagination()
        );

        self::assertSame('foo', $uriSegments->getLastSegmentSansPagination());

        self::assertSame(4, $uriSegments->getTotalSegmentsSansPagination());

        self::assertSame('test/url/path/foo', $uriSegments->getPathSansPagination());

        self::assertSame(987, $uriSegments->getPageNum());
    }

    protected function setUp(): void
    {
        $this->extractUriSegments = new ExtractUriSegments();

        $this->uriInterface = $this->createMock(
            UriInterface::class
        );
    }
}
