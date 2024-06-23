<?php

namespace App\Dashboard\Ui\Authentication;

use App\Dashboard\Ui\AbstractBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TermsController extends AbstractBaseController
{
    #[Route('/dashboard/terms', name: 'app_terms')]
    public function showTerms(TranslatorInterface $translator): Response
    {
        $locale = $translator->getLocale();

        return $this->render($this->getTemplate($locale));
    }

    private function getTemplate(string $locale): string
    {
        return sprintf('dashboard/authentication/terms_%s.html.twig', $locale);
    }
}
