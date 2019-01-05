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

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\Mutant\MutantInterface;
use Infection\TestFramework\Codeception\Config\YamlConfigurationHelper;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;

/**
 * Class MutationConfigBuilder
 * @internal
 */
class MutationConfigBuilder extends ConfigBuilder
{
    /**
     * @var YamlConfigurationHelper
     */
    private $configurationHelper;

    public function __construct(string $tempDir, string $projectDir, string $originalConfig, array $srcDirs = [])
    {
        $this->configurationHelper = new YamlConfigurationHelper($tempDir, $projectDir, $originalConfig, $srcDirs);
    }

    public function build(MutantInterface $mutant): string
    {
        $_SERVER['INFECTION_CODECEPTION_CUSTOM_AUTOLOAD_FILE_PATH'] = $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->configurationHelper->getTempDir(),
            $mutant->getMutation()->getHash()
        );

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant));

        $pathToMutationConfigFile = $this->configurationHelper->getTempDir() . DIRECTORY_SEPARATOR . sprintf('codeception.%s.infection.xml', $mutant->getMutation()->getHash());

        file_put_contents($pathToMutationConfigFile, $this->configurationHelper->getTransformedConfig($mutant->getMutation()->getHash(), false));

        return $pathToMutationConfigFile;
    }

    private function createCustomAutoloadWithInterceptor(MutantInterface $mutant): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();
        $interceptorPath = dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $autoload = sprintf('%s/vendor/autoload.php', $this->configurationHelper->getProjectDir());

        $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';
%s

AUTOLOAD;

        return sprintf($customAutoload, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath));
    }
}
