<?php

namespace App\Kernel\Infrastructure;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

class YamlParser
{
    /**
     * @return array<mixed, mixed>
     */
    public function getDataFromFile(string $filePath): array
    {
        if (false === file_exists($filePath)) {
            throw new FileNotFoundException(sprintf('File path not found in %s path.', $filePath));
        }
        $fileContent = file_get_contents($filePath);

        return $fileContent ? Yaml::parse($fileContent) : [];
    }
}
