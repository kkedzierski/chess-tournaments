<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\Infrastructure;

use App\Kernel\YamlParser\YamlParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class YamlParserTest extends TestCase
{
    private YamlParser $yamlParser;

    protected function setUp(): void
    {
        $this->yamlParser = new YamlParser();
    }

    public function testGetDataFromFile(): void
    {
        $this->assertNotEmpty($this->yamlParser->getDataFromFile(sprintf('%s/%s', __DIR__, '../../../../config/services.yaml')));
        $this->assertEmpty($this->yamlParser->getDataFromFile(sprintf('%s/%s', __DIR__, 'emptyFile.yaml')));
    }

    public function testFileNotFoundInGetDataFromFile(): void
    {
        $this->expectExceptionMessage('File path not found in unknown path.');
        $this->expectException(FileNotFoundException::class);
        $this->yamlParser->getDataFromFile('unknown');
    }
}
