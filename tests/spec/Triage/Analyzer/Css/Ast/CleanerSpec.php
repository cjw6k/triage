<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Analyzer\Css\Ast;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Analyzer\Css\Ast\Cleaner;

class CleanerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(Cleaner::class);
    }
}
