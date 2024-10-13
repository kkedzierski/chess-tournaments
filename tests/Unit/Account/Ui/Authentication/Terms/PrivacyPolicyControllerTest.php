<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Ui\Authentication\Terms;

use App\Account\Ui\Authentication\Terms\PrivacyPolicyController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PrivacyPolicyControllerTest extends TestCase
{
    private MockObject&TranslatorInterface $translator;

    private MockObject&ContainerInterface $container;

    private MockObject&Environment $twig;

    private PrivacyPolicyController $controller;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->twig = $this->createMock(Environment::class);

        $this->controller = new PrivacyPolicyController();
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
            ->with('dashboard/authentication/terms/privacyPolicy/privacy-policy_en.html.twig');

        $this->controller->showPrivacyPolicy($this->translator);
    }
}
