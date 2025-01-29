<?php

namespace BlueSpice\UniversalExport\Tests\Tag;

use BlueSpice\UniversalExport\Tag\NoExportHandler;
use MediaWiki\Parser\ParserOutput;
use Parser;
use PHPUnit\Framework\TestCase;
use PPFrame;

/**
 * @group Broken
 */
class NoExportHandlerTest extends TestCase {

	/**
	 * @param string $input
	 * @param string $expectedContainerEl
	 * @covers BlueSpice\UniversalExport\Tag\NoExportHandler::handle
	 * @dataProvider provideTestHandleData
	 */
	public function testHandle( $input, $expectedContainerEl ) {
		$mockParser = $this->createMock( Parser::class );
		$mockParserOutput = $this->createMock( ParserOutput::class );
		$mockParser->method( 'getOutput' )->willReturn( $mockParserOutput );
		$mockFrame = $this->createMock( PPFrame::class );
		$handler = new NoExportHandler( $input, [], $mockParser, $mockFrame );
		$output = $handler->handle();

		$this->assertStringStartsWith( "<$expectedContainerEl", $output );
	}

	/**
	 *
	 * @return array
	 */
	public function provideTestHandleData() {
		return [
			'simple-inline' => [ 'Lorem ipsum', 'span' ],
			'complex-inline' => [ 'Lorem [[ipsum]], {{Some}} \'\'Text\'\' and ***, #', 'span' ],
			'simple-wikitext-paragraph' => [ "Lorem\n\nipsum", 'div' ],
			'complex-blocklevel-01' => [ 'Lorem <div class="red">ipsum</div>', 'div' ],
			'complex-blocklevel-02' => [ "Lorem \n:indent me", 'div' ],
			'complex-blocklevel-03' => [ "Lorem \n:indent me", 'div' ],
			'complex-blocklevel-04' => [ "Lorem \n*list me", 'div' ],
			'complex-blocklevel-05' => [ "Lorem \n preformat me", 'div' ],
		];
	}
}
