<?php

namespace redrock\bitset;

use PHPUnit_Framework_TestCase;

class constructTest extends PHPUnit_Framework_TestCase
{
	public function testErrorSize() {
		$bitset = new bitset(-1);
		$this->assertEquals(0, $bitset->size());

		$bitset = new bitset(64);
		$this->assertEquals(0, $bitset->size());
	}

	public function testActualSize() {
		$bitset = new bitset(8);
		$this->assertEquals(8, $bitset->size());
	}

	public function testActualSizeWithAllocator() {
		$bitset = new bitset(8, [0, 1, 1, 0, 1]);
		$this->assertEquals(8, $bitset->size());
		$this->assertEquals('01101000', $bitset->__toString());

		$bitset = new bitset(8, ['A', 'B', 'A', 'B'], 'A', 'B');
		$this->assertEquals(8, $bitset->size());
		$this->assertEquals('10100000', $bitset->__toString());
	}
}