<?php

declare(strict_types=1);

namespace App\Kernel\YamlParser;

interface YamlParserInterface
{
    public function getDataFromFile(string $filePath): mixed;
}
