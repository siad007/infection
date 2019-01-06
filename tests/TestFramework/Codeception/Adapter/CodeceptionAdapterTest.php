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

namespace Infection\Tests\TestFramework\Codeception\Adapter;

use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\Codeception\Adapter\CodeceptionAdapter;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\Utils\VersionParser;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class CodeceptionAdapterTest
 * @internal
 */
final class CodeceptionAdapterTest extends MockeryTestCase
{
    /**
     * @dataProvider passProvider
     */
    public function test_it_determines_whether_tests_pass_or_not(string $output, bool $expectedResult)
    {
        $executableFined = $this->getMockForAbstractClass(AbstractExecutableFinder::class);
        $initialConfigBuilder = $this->getMockBuilder(InitialConfigBuilder::class)->getMock();
        $mutationConfigBuilder = $this->getMockForAbstractClass(MutationConfigBuilder::class);
        $cliArgumentsBuilder = $this->getMockForAbstractClass(CommandLineArgumentsAndOptionsBuilder::class);
        $versionParser = $this->getMockBuilder(VersionParser::class);

        $adapter = new CodeceptionAdapter(
            $executableFined,
            $initialConfigBuilder,
            $mutationConfigBuilder,
            $cliArgumentsBuilder,
            $versionParser
        );

        $result = $adapter->testsPass($output);
        $this->assertSame($expectedResult, $result);
    }

    public function passProvider()
    {
        yield ['OK, but incomplete, skipped, or risky tests!', true];
        yield ['OK (5 tests, 3 assertions)', true];
        yield ['FAILURES!', false];
        yield ['ERRORS!', false];
    }
}
