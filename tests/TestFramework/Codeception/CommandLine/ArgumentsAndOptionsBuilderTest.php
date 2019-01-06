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

namespace Infection\Tests\TestFramework\Codeception\CommandLine;

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\TestFramework\Codeception\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\Coverage\CodeCoverageData;
use PHPUnit\Framework\TestCase;

/**
 * Class ArgumentsAndOptionsBuilderTest
 * @internal
 */
final class ArgumentsAndOptionsBuilderTest extends TestCase
{
    public function test_it_builds_correct_command()
    {
        $tempPath = '/temp/path';

        $configPath = '/config/path';

        $builder = new ArgumentsAndOptionsBuilder($tempPath);

        $command = $builder->build($configPath, '--verbose');

        $this->assertContains('run', $command);
        $this->assertContains('--no-colors', $command);
        $this->assertContains('--config=' . $configPath, $command);
        $this->assertContains('--coverage-phpunit=' . CodeCoverageData::CODECEPTION_COVERAGE_DIR, $command);
    }

    public function test_it_builds_correct_command_with_mutant()
    {
        $tempPath = '/temp/path';

        $configPath = '/config/path';

        $mutation = $this->getMockBuilder(MutationInterface::class)
            ->setMethods(
                [
                    'getHash',
                    'getMutator',
                    'getAttributes',
                    'getOriginalFilePath',
                    'getMutatedNodeClass',
                    'getOriginalFileAst',
                    'isOnFunctionSignature',
                    'isCoveredByTest',
                    'getMutatedNode',
                ]
            )->getMock();
        $mutation->method('getHash')->willReturn('a1b2c3');

        $mutant = $this->getMockBuilder(MutantInterface::class)
            ->setMethods(
                [
                    'getMutation',
                    'getMutatedFilePath',
                    'getDiff',
                    'isCoveredByTest',
                    'getCoverageTests',
                ]
            )->getMock();
        $mutant->method('getMutation')->willReturn($mutation);

        $builder = new ArgumentsAndOptionsBuilder($tempPath);

        $command = $builder->build($configPath, '--verbose', $mutant);

        $this->assertContains('run', $command);
        $this->assertContains('--no-colors', $command);
        $this->assertContains('--config=' . $configPath, $command);
        $this->assertContains('--fail-fast', $command);
        $this->assertContains('--verbose', $command);
    }
}
