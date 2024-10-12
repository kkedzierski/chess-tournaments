<?php

declare(strict_types=1);

namespace App\Company\Ui;

use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\Company\Application\GusApi\GusDataProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GusCompanyDataController extends AbstractController
{
    public function __construct(
        private readonly GusDataProviderInterface $gusDataProvider,
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/dashboard/company/fetch-gus-data', name: 'app_company_gus_data', methods: ['POST'])]
    public function getCompanyDataFromGus(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (null === $user) {
            throw new AccessDeniedException();
        }

        $gusDataRequest = $this->serializer->deserialize($request->getContent(), GusDataRequestDto::class, 'json');

        $violations = $this->validator->validate($gusDataRequest);
        if (count($violations) > 0) {
            $errorMessages = [];
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $companyDataDto = $this->gusDataProvider->getCompanyDataByTin($gusDataRequest->tin, $request->getClientIp());

        return new JsonResponse($companyDataDto);
    }
}
