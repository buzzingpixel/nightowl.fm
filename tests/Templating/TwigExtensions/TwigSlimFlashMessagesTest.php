<?php

declare(strict_types=1);

namespace Tests\Templating\TwigExtensions;

use App\Templating\TwigExtensions\TwigSlimFlashMessages;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigSlimFlashMessagesTest extends TestCase
{
    protected TwigSlimFlashMessages $extension;

    /** @var MockObject&Messages */
    protected $flash;

    /** @var array|string[][] */
    protected array $dummyMessages = [
        'key1' => [
            'my first message',
            'another message',
        ],
        'key2' => ['only one message'],
    ];
    private Environment $view;

    protected function setUp(): void
    {
        $this->flash = $this->getMockBuilder('Slim\Flash\Messages')
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getMessages',
                'getMessage',
            ])
            ->getMock();

        $this->flash->expects(self::any())
            ->method('getMessages')
            ->willReturn($this->dummyMessages);

        $this->flash->expects(self::any())
            ->method('getMessage')
            ->will(self::returnCallback(function ($key): ?array {
                return $this->dummyMessages[$key] ?? null;
            }));

        $this->extension = new TwigSlimFlashMessages($this->flash);

        $this->view = new Environment(
            new FilesystemLoader(__DIR__ . '/Templates')
        );

        $this->view->addExtension($this->extension);
    }

    /**
     * @throws Throwable
     */
    public function testMessagesInTemplateUsingKey(): void
    {
        $result = $this->view->render('with-key.twig');

        $expected = 'my first message<br>another message<br>';

        self::assertSame($expected, $result);
    }

    /**
     * @throws Throwable
     */
    public function testMessagesInTemplateWithoutKey(): void
    {
        $result = $this->view->render('without-key.twig');

        $expected = 'key1: my first messagekey1: another messagekey2: only one message';

        self::assertSame($expected, $result);
    }
}
