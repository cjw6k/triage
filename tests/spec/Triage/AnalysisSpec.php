<?php

declare(strict_types=1);

namespace spec\Triage\Triage;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Analysis;

use function floor;
use function mt_rand;

class AnalysisSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(Analysis::class);
    }

    function it_captures_the_number_of_files_and_directories_scanned_by_the_picker(): void
    {
        $directories = (int)floor(mt_rand(5, 50));
        $files = (int)floor(mt_rand(15, 1500));

        $this->setPickerStatus(
            [
                'directories' => $directories,
                'files' => $files,
            ]
        );

        $this->getDirectoryScanCount()->shouldBe($directories);
        $this->getFileScanCount()->shouldBe($files);
    }

    function it_captures_brief_info_on_files_that_are_not_normal_influencers_on_semantic_compatibility(): void
    {
        $this->addUnsupportedFile(
            [
                'mime_type' => 'text/plain',
                'scan_path' => './test.txt',
                'filename' => 'test.txt',
                'path' => PACKAGE_ROOT . 'test.txt',
            ]
        );

        $this->getDetails()->shouldBe(
            [
                'text/plain' => [
                    './test.txt' => "test.txt",
                ],
            ]
        );
    }
}
