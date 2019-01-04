<?php

namespace Infection\TestFramework\Codeception;

use Infection\TestFramework\TestFrameworkExtraOptions;

final class CodeceptionExtraOptions extends TestFrameworkExtraOptions
{
    protected function getInitialRunOnlyOptions(): array
    {
        return [];
    }
}
