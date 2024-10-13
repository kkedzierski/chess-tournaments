<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Application;

use App\Company\Application\Exception\CannotGetGusDataException;
use App\Company\Application\GusApi\CompanyDataDto;
use App\Company\Application\GusApi\GusApiService;
use App\Company\Domain\GusApiSearchResult;
use App\Company\Domain\GusApiSearchResultFactory;
use App\Company\Domain\GusApiSearchResultRepositoryInterface;
use App\Tests\Unit\ConsecutiveParamsTrait;
use App\Tests\Unit\Stub\CacheStub;
use GusApi\Adapter\AdapterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GusApiServiceTest extends TestCase
{
    use ConsecutiveParamsTrait;

    private CacheStub $cacheStub;

    private MockObject&TagAwareCacheInterface $cache;

    private MockObject&ItemInterface $item;

    private MockObject&GusApiSearchResultFactory $gusApiSearchResultFactory;

    private MockObject&GusApiSearchResultRepositoryInterface $gusApiSearchResultRepository;

    private MockObject&LoggerInterface $logger;

    private MockObject&AdapterInterface $adapter;

    private GusApiService $gusApiService;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->cacheStub = new CacheStub($this->cache);
        $this->item = $this->createMock(ItemInterface::class);
        $this->gusApiSearchResultFactory = $this->createMock(GusApiSearchResultFactory::class);
        $this->gusApiSearchResultRepository = $this->createMock(GusApiSearchResultRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->adapter = $this->createMock(AdapterInterface::class);

        $this->gusApiService = new GusApiService(
            $this->cacheStub,
            $this->gusApiSearchResultFactory,
            $this->gusApiSearchResultRepository,
            $this->logger,
            'gusApiKey',
            86400,
            $this->adapter
        );
    }

    public static function providerForSearchResulStdClass(): \Generator
    {
        $baseSearchResultStdClass = new SearchResultResponseDto();
        $baseSearchResultStdClass->Regon = 'regon';
        $baseSearchResultStdClass->Nip = '1234567890';
        $baseSearchResultStdClass->Nazwa = 'name';
        $baseSearchResultStdClass->Wojewodztwo = 'province';
        $baseSearchResultStdClass->Ulica = 'street';
        $baseSearchResultStdClass->KodPocztowy = 'zipCode';

        $searchResultStdClassWithoutCity = clone $baseSearchResultStdClass;
        $searchResultStdClassWithoutCity->Gmina = 'city';

        $searchResultStdClassWithCity = clone $baseSearchResultStdClass;
        $searchResultStdClassWithCity->Miejscowosc = 'city';

        yield [$searchResultStdClassWithoutCity];
        yield [$searchResultStdClassWithCity];
    }

    #[DataProvider('providerForSearchResulStdClass')]
    public function testLogExceptionAndThrowExceptionWhenSaveDataByCacheFails(SearchResultResponseDto $searchResultStdClass): void
    {
        $tin = '1234567890';
        $userIp = 'userIp';
        $gusApiSearchResult = new GusApiSearchResult(Uuid::v4(), $tin, $userIp);

        $this->cacheStub->setItem($this->item);
        $this->cacheStub
            ->get(sprintf('gus_data_%s_%s', $tin, $userIp), function () use ($gusApiSearchResult, $searchResultStdClass): void {
                $this->item
                    ->expects($this->once())
                    ->method('expiresAfter')
                    ->with(86400)
                    ->willReturnSelf();
                $this->adapter
                    ->expects($this->once())
                    ->method('login')
                    ->with('gusApiKey')
                    ->willReturn('sid');
                $this->adapter
                    ->expects($this->once())
                    ->method('search')
                    ->with('sid', ['Nip' => 1234567890])
                    ->willReturn([$searchResultStdClass]);
                $this->gusApiSearchResultFactory
                    ->expects($this->once())
                    ->method('createByCompanyDataDto')
                    ->with($this->callback(
                        static fn (CompanyDataDto $companyDataDto) =>
                        '1234567890' === $companyDataDto->tin
                        && 'name' === $companyDataDto->name
                        && 'regon' === $companyDataDto->regon
                        && 'province' === $companyDataDto->province
                        && 'street' === $companyDataDto->street
                        && 'city' === $companyDataDto->city
                    ), 'userIp')
                    ->willReturn($gusApiSearchResult);
                $this->gusApiSearchResultRepository
                    ->expects($this->once())
                    ->method('save')
                    ->with($gusApiSearchResult)
                    ->willThrowException($exception = new \Exception('exceptionMessage'));
                $this->logger
                    ->expects($this->exactly(2))
                    ->method('error')
                    ->with(...$this->consecutiveParams(
                        ['Error while saving gusApiSearchResult.', ['exception' => $exception]],
                    ));
            });

        $this->expectException(CannotGetGusDataException::class);

        $this->gusApiService->getCompanyDataByTin($tin, $userIp);
    }
}

class SearchResultResponseDto
{
    public function __construct(
        public string $Regon = '',
        public string $Nip = '',
        public string $Nazwa = '',
        public string $Wojewodztwo = '',
        public string $Ulica = '',
        public string $KodPocztowy = '',
        public string $Miejscowosc = '',
        public string $Gmina = '',
        public string $Powiat = '',
        public string $SilosID = '',
        public string $Typ = '',
    ) {
    }
}
