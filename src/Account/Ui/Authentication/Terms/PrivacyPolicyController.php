<?php

declare(strict_types=1);

namespace App\Account\Ui\Authentication\Terms;

use App\Account\Ui\AbstractBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PrivacyPolicyController extends AbstractBaseController
{
    #[Route('/dashboard/privacy-policy', name: 'app_privacy_policy')]
    public function showPrivacyPolicy(TranslatorInterface $translator): Response
    {
        $locale = $translator->getLocale();

        return $this->render($this->getTemplate($locale));
    }

    private function getTemplate(string $locale): string
    {
        return sprintf('dashboard/authentication/terms/privacyPolicy/privacy-policy_%s.html.twig', $locale);
    }
}
