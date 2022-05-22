<?php

declare(strict_types=1);

namespace spec\Triage\Triage;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Picker;

class PickerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(Picker::class);
    }

    function it_stages_files_for_later_use_by_scanning_a_source(): void
    {
        $this->scan(FIXTURES_ROOT . "picker-test/empty-directory/");
        $this->callOnWrappedObject('hasPicks')->shouldBe(false);

        $this->scan(FIXTURES_ROOT . "picker-test/directory-with-20-files/");
        $this->callOnWrappedObject('hasPicks')->shouldBe(true);
    }

    function it_provides_picks_sorted_in_natural_order(): void
    {
        $this->scan(FIXTURES_ROOT . "picker-test/directory-with-20-files/");
        $this->callOnWrappedObject('hasPicks')->shouldBe(true);

        $picks = 0;

        while ($this->getWrappedObject()->hasPicks()) {
            $this->nextPick()->shouldBe(
                [
                    'path' => FIXTURES_ROOT . "picker-test/directory-with-20-files",
                    'scan_path' => "/" . ++$picks,
                    'filename' => "$picks",
                    'mime_type' => "text/plain",
                ]
            );
        }
    }
}
