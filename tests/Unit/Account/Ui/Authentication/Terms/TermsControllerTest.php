<?php

namespace App\Tests\Unit\Account\Ui\Authentication\Terms;

use App\Account\Ui\Authentication\Terms\TermsController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TermsControllerTest extends TestCase
{
    private MockObject&TranslatorInterface $translator;

    private MockObject&ContainerInterface $container;

    private MockObject&Environment $twig;

    private TermsController $controller;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->twig = $this->createMock(Environment::class);

        $this->controller = new TermsController();
        $this->controller->setContainer($this->container);
    }

    public function testRenderTermsDependsOnLocaleLanguage(): void
    {
        $this->translator->expects($this->once())
            ->method('getLocale')
            ->willReturn('en');
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('twig')
            ->willReturn(true);
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('twig')
            ->willReturn($this->twig);
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('dashboard/authentication/terms/terms_en.html.twig');

        $this->controller->showTerms($this->translator);
    }
}
