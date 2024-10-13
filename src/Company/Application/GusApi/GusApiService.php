<?php

declare(strict_types=1);

namespace App\Company\Application\GusApi;

use App\Company\Application\Exception\CannotGetGusDataException;
use App\Company\Domain\GusApiSearchResultFactory;
use App\Company\Domain\GusApiSearchResultRepositoryInterface;
use GusApi\Adapter\AdapterInterface;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\SearchReport;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class GusApiService extends GusApi implements GusDataProviderInterface
{
    private const CACHE_EXPIRATION_TIME = 86400;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
        private readonly GusApiSearchResultFactory $gusApiSearchResultFactory,
        private readonly GusApiSearchResultRepositoryInterface $gusApiSearchResultRepository,
        private readonly LoggerInterface $logger,
        private readonly string $gusApiKey,
        private readonly int $cacheExpirationTime = self::CACHE_EXPIRATION_TIME,
        protected $adapter = null,
    ) {
        parent::__construct($this->gusApiKey, $this->adapter);
    }

    /**
     * @throws NotFoundException
     *
     * @return SearchReport[]
     */
    private function getGusDataByTin(string $tin): array
    {
        return $this->getByNip($this->login(), $tin);
    }

    /**
     * @throws CannotGetGusDataException
     */
    public function getCompanyDataByTin(string $tin, ?string $userIp = null): CompanyDataDto
    {
        try {
            return $this->saveDataAndGetByCache($tin, $userIp);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Error while getting gusData from cache.',
                ['exception' => $exception]
            );

            throw new CannotGetGusDataException();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function saveDataAndGetByCache(string $tin, ?string $userIp): CompanyDataDto
    {
        $key = sprintf('gus_data_%s_%s', $tin, $userIp);

        return $this->cache->get($key, function (ItemInterface $item) use ($tin, $userIp) {
            $item->expiresAfter($this->cacheExpirationTime);

            $searchReport = $this->getGusDataByTin($tin)[0];

            $companyData = new CompanyDataDto(
                $tin,
                $searchReport->getName(),
                $searchReport->getRegon(),
                $searchReport->getProvince(),
                $searchReport->getStreet(),
                $searchReport->getZipCode(),
                empty($searchReport->getCity()) ? $searchReport->getCommunity() : $searchReport->getCity(),
            );

            $this->saveGusApiSearchResult($companyData, $userIp);

            return $companyData;
        });
    }

    private function saveGusApiSearchResult(CompanyDataDto $companyDataDto, ?string $userIp): void
    {
        try {
            $gusApiSearchResult = $this->gusApiSearchResultFactory->createByCompanyDataDto($companyDataDto, $userIp);

            $this->gusApiSearchResultRepository->save($gusApiSearchResult);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Error while saving gusApiSearchResult.',
                ['exception' => $exception]
            );
        }
    }
}
