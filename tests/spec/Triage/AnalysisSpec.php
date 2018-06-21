<?php

namespace spec\Triage\Triage;

use Triage\Triage\Analysis;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AnalysisSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Analysis::class);
    }
	
	function it_captures_the_number_of_files_and_directories_scanned_by_the_picker()
	{
		$directories = (int)floor(mt_rand(5, 50));
		$files = (int)floor(mt_rand(15, 1500));

		$this->setPickerStatus(
			array(
				'directories' => $directories,
				'files' => $files
			)
		);
		
		$this->getDirectoryScanCount()->shouldBe($directories);
		$this->getFileScanCount()->shouldBe($files);
	}
	
	function it_captures_brief_info_on_files_that_are_not_normal_influencers_on_semantic_compatibility()
	{
		$this->addUnsupportedFile(
			array(
				'mime_type' => 'text/plain',
				'scan_path' => './test.txt',
				'filename' => 'test.txt',
				'path' => PACKAGE_ROOT . 'test.txt'
			)
		);
		
		$this->getDetails()->shouldBe(
			array(
				'text/plain' => array(
					'./test.txt' => "test.txt"
				)
			)
		);
	}
}
