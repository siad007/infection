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

namespace Infection\Tests\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Codeception\Config\Builder\InitialConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InitialConfigBuilderTest
 * @internal
 */
final class InitialConfigBuilderTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $workspace;
    /**
     * @var string
     */
    private $tempDir;
    /**
     * @var string
     */
    private $projectDir;

    protected function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->workspace = sys_get_temp_dir() . '/infection-test' . \microtime(true) . \random_int(100, 999);
        $this->tempDir = (new TmpDirectoryCreator($this->filesystem))->createAndGet($this->workspace);
        $this->projectDir = __DIR__ . '/../../../../Fixtures/Files/codeception/project-path';
    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->workspace);
    }

    public function test_it_can_build_initial_config(): void
    {
        $originalContent = '';
        $initialConfigBuilder = new InitialConfigBuilder($this->tempDir, $this->projectDir, $originalContent, ['src']);
        $config = Yaml::parseFile($initialConfigBuilder->build('2.5'));
        $this->assertSame(realpath($this->projectDir . '/tests'), realpath($config['paths']['tests']));
        $this->assertSame(realpath($this->tempDir . '/.'), realpath($config['paths']['output']));
        $this->assertSame(realpath($this->projectDir . '/tests/_data'), realpath($config['paths']['data']));
        $this->assertSame(realpath($this->projectDir . '/tests/_support'), realpath($config['paths']['support']));
        $this->assertSame(realpath($this->projectDir . '/tests/_envs'), realpath($config['paths']['envs']));
        $this->assertTrue($config['coverage']['enabled']);
        $this->assertSame([self::rp($this->projectDir . '/src/*')],
            array_map(self::class . '::rp', $config['coverage']['include']));
        $this->assertSame([], $config['coverage']['exclude']);
    }

    private static function rp(string $path): string
    {
        return realpath(substr($path, 0, -1)) . '*';
    }
}
