<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Config;

use Infection\Config\Exception\InvalidConfigException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlConfigurationHelper
 * @internal
 */
final class YamlConfigurationHelper implements YamlConfigurable
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $originalConfig;

    /**
     * @var string[]
     */
    private $srcDirs;

    public function __construct(string $tempDir, string $projectDir, string $originalConfig, array $srcDirs = [])
    {
        $this->tempDir = substr($tempDir, -1) === DIRECTORY_SEPARATOR ? substr($tempDir, 0, -1) : $tempDir;
        $this->originalConfig = $originalConfig;
        $this->projectDir = substr($projectDir, -1) === DIRECTORY_SEPARATOR ? substr($projectDir, 0, -1) : $projectDir;
        $this->srcDirs = $srcDirs;
    }

    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getTransformedConfig(string $outputDir = '.', bool $coverageEnabled = true): string
    {
        $file = realpath($this->tempDir);

        if ($file === false) {
            throw new InvalidConfigException('Temp dir not found.');
        }

        $tempDirPartsCount = substr_count($file, DIRECTORY_SEPARATOR) + 1;
        $pathToProjectDir = str_repeat('../', $tempDirPartsCount) . $this->projectDir . '/';

        $config = Yaml::parse($this->originalConfig);
        if ($config === null) {
            $config = $this->updatePaths([], $pathToProjectDir);
        }

        $config['paths'] = [
            'tests'   => $config['paths']['tests'] ?? $pathToProjectDir . 'tests',
            'output'  => $this->tempDir . '/' . $outputDir,
            'data'    => $config['paths']['data'] ?? $pathToProjectDir . 'tests/_data',
            'support' => $config['paths']['support'] ?? $pathToProjectDir . 'tests/_support',
            'envs'    => $config['paths']['envs'] ?? $pathToProjectDir . 'tests/_envs',
        ];

        $config['coverage'] = [
            'enabled' => $coverageEnabled,
            'include' => $coverageEnabled ? array_map(
                function ($dir) use ($pathToProjectDir) {
                    return $pathToProjectDir . trim($dir, '/') . '/*.php';
                },
                $this->srcDirs
            ) : [],
            'exclude' => [],
        ];

        $file = Yaml::dump($config);

        return $file;
    }

    private function updatePaths(array $config, string $projectPath): array
    {
        $returnConfig = [];
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $value = $this->updatePaths($value, $projectPath);
            } elseif (is_string($value)) {
                if (strpos($value, './') === 0) {
                    $value = substr($value, 2);
                }

                if (file_exists($this->projectDir . '/' . $projectPath . $value)) {
                    $value = str_replace('//', '/', $projectPath . $value);
                }
            }

            $returnConfig[$key] = $value;
        }

        return $returnConfig;
    }
}
