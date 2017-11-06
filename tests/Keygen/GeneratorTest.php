<?php

use Keygen\Generator;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\NumericGenerator;

/**
 * @coversDefaultClass Generator
 * @covers GeneratorInterface
 */
final class GeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new NumericGenerator;
	}

	/**
	 * @covers ::generate
	 * @covers ::getKeygenLength
	 * @covers ::resolveTransformationsFromGenerationArguments
	 * @covers ::resolveInclusiveAffixFromGenerationArguments
	 * @covers ::applyTransformationsToGeneratedKey
	 * @covers ::applyAffixesToGeneratedKey
	 * @covers ::getGeneratedKey
	 * @covers ::finishKeyGeneration
	 */
	public function testGenerateMethod()
	{
		$ga = $this->generator->generate();
		$this->assertSame($this->generator->length, strlen($ga));

		$gb = $this->generator->prefix('TM-')->generate();
		$this->assertSame($this->generator->length, strlen($gb));
		$this->assertRegExp('/^TM-/', $gb);

		$gc = $this->generator->affix('token::', '::123')->generate(true);
		$this->assertSame($this->generator->length + strlen($this->generator->prefix) + strlen($this->generator->suffix), strlen($gc));
		$this->assertRegExp('/^token::\d+?::123$/', $gc);

		$callable = function($s) { return strtoupper(substr($s, 8)); };

		$gd = $this->generator->transformation('md5')->generate($callable);
		$this->assertSame(24 + strlen($this->generator->prefix) + strlen($this->generator->suffix), strlen($gd));
		$this->assertRegExp('/^token::[A-F0-9]+?::123$/', $gd);

		$ge = $this->generator->affix(false, false)->transformations($callable)->generate(true);
		$this->assertSame($this->generator->length - 8, strlen($ge));
	}

	/**
	 * @covers ::overloadGenerateMethod
	 * @expectedException Keygen\Exceptions\UnknownMethodCallKeygenException
	 * @expectedExceptionMessage Call to unknown method: Keygen\Generators\NumericGenerator::generateUnique().
	 */
	public function testOverloadedGenerateMethod() {
		$this->assertCount(10, $this->generator->generate10());
		$this->assertCount(20, $this->generator->generate_20());
		
		$this->assertCount(100, $this->generator->generateUnique100());
		$this->assertCount(200, $this->generator->generate_unique_200());

		$keys = $this->generator->generateUnique();
	}
}
