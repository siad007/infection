<?php

namespace Infection\TestFramework\Codeception\Config;

/**
 * Class YamlConfigurationHelper
 * @internal
 */
interface YamlConfigurable
{
    public function getTempDir(): string;

    public function getProjectDir(): string;

    public function getTransformedConfig(string $outputDir = '.', bool $coverageEnabled = true): string;
}