<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Ui;

use App\Account\Domain\User;
use App\Company\Application\Exception\CannotGetGusDataException;
use App\Company\Application\GusApi\CompanyDataDto;
use App\Company\Application\GusApi\GusDataProviderInterface;
use App\Company\Ui\GusCompanyDataController;
use App\Company\Ui\GusDataRequestDto;
use App\Kernel\Flasher\FlasherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GusCompanyDataControllerTest extends TestCase
{
    private MockObject&GusDataProviderInterface $gusDataProvider;

    private MockObject&Security $security;

    private MockObject&ValidatorInterface $validator;

    private MockObject&SerializerInterface $serializer;

    private MockObject&FlasherInterface $flasher;

    private MockObject&Request $request;

    private GusCompanyDataController $controller;

    protected function setUp(): void
    {
        $this->gusDataProvider = $this->createMock(GusDataProviderInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->flasher = $this->createMock(FlasherInterface::class);
        $this->request = $this->createMock(Request::class);

        $this->controller = new GusCompanyDataController(
            $this->gusDataProvider,
            $this->security,
            $this->validator,
            $this->serializer,
            $this->flasher,
        );
    }

    public function testThrowAccessDeniedExceptionWhenUserNotFound(): void
    {
        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn(null);
        $this->serializer->expects($this->never())
            ->method('deserialize');

        $this->expectException(AccessDeniedException::class);

        $this->controller->getCompanyDataFromGus($this->request);
    }

    public function testReturnUnprocessableEntityResponseWhenValidationFailed(): void
    {
        $user = new User();
        $gusDataRequestDto = new GusDataRequestDto('1234567890');
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolationList = $this->createConfiguredMock(
            ConstraintViolationListInterface::class,
            [
                'count' => 1,
                'get'   => $constraintViolation,
            ]
        );

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"tin":"1234567890"}');
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"tin":"1234567890"}', GusDataRequestDto::class, 'json')
            ->willReturn($gusDataRequestDto);
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($gusDataRequestDto)
            ->willReturn($constraintViolationList);
        $this->gusDataProvider
            ->expects($this->never())
            ->method('getCompanyDataByTin');

        $response = $this->controller->getCompanyDataFromGus($this->request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCatchCannotGetGusDataExceptionAndThrowServiceUnavailable(): void
    {
        $user = new User();
        $gusDataRequestDto = new GusDataRequestDto('1234567890');
        $constraintViolationList = $this->createConfiguredMock(
            ConstraintViolationListInterface::class,
            [
                'count' => 0,
            ]
        );

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"tin":"1234567890"}');
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"tin":"1234567890"}', GusDataRequestDto::class, 'json')
            ->willReturn($gusDataRequestDto);
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($gusDataRequestDto)
            ->willReturn($constraintViolationList);
        $this->gusDataProvider
            ->expects($this->once())
            ->method('getCompanyDataByTin')
            ->with('1234567890', null)
            ->willThrowException(new CannotGetGusDataException());
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.cannotGetGusData');

        $response = $this->controller->getCompanyDataFromGus($this->request);

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }

    public function testCatchUnknownExceptionAndThrowInternalServerError(): void
    {
        $user = new User();
        $gusDataRequestDto = new GusDataRequestDto('1234567890');
        $constraintViolationList = $this->createConfiguredMock(
            ConstraintViolationListInterface::class,
            [
                'count' => 0,
            ]
        );

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"tin":"1234567890"}');
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"tin":"1234567890"}', GusDataRequestDto::class, 'json')
            ->willReturn($gusDataRequestDto);
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($gusDataRequestDto)
            ->willReturn($constraintViolationList);
        $this->gusDataProvider
            ->expects($this->once())
            ->method('getCompanyDataByTin')
            ->with('1234567890', null)
            ->willThrowException(new \Exception());
        $this->flasher
            ->expects($this->once())
            ->method('error')
            ->with('exception.fetchingGusData');

        $response = $this->controller->getCompanyDataFromGus($this->request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testGetCompanyDataByTin(): void
    {
        $user = new User();
        $gusDataRequestDto = new GusDataRequestDto('1234567890');
        $companyDataDto = new CompanyDataDto('1234567890', 'Company Name', '123456789', 'Province', 'Street', '00-000', 'City');
        $constraintViolationList = $this->createConfiguredMock(
            ConstraintViolationListInterface::class,
            [
                'count' => 0,
            ]
        );

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"tin":"1234567890"}');
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"tin":"1234567890"}', GusDataRequestDto::class, 'json')
            ->willReturn($gusDataRequestDto);
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($gusDataRequestDto)
            ->willReturn($constraintViolationList);
        $this->gusDataProvider
            ->expects($this->once())
            ->method('getCompanyDataByTin')
            ->with('1234567890', null)
            ->willReturn($companyDataDto);
        $this->flasher
            ->expects($this->never())
            ->method('error');

        $response = $this->controller->getCompanyDataFromGus($this->request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
