<?php

namespace redrock\bitset;

use PHPUnit_Framework_TestCase;

class operatorTest extends PHPUnit_Framework_TestCase
{
	public function testValidOperator() {
		$bitset = new bitset(8);
		$bitset[1] = 1;
		$bitset[4] = 1;
		$this->assertEquals('01001000', $bitset->__toString());
	}

	/**
	 * @expectedException ErrorException
	 */
	public function testInvalidRange() {
		$bitset = new bitset(1);
		$bitset[2] = 1;
	}

	public function testCountOperator() {
		$bitset = new bitset(5, [1, 0, 1, 0, 1]);
		$this->assertEquals(3, count($bitset));
	}

	public function testTestOperator() {
		$bitset = new bitset(8, ['A', 'B', 'A', 'A', 'B', 'A', 'A', 'A'], 'A', 'B');
		$this->assertTrue($bitset->any());
		$this->assertFalse($bitset->all());
		$this->assertFalse($bitset->none());

		$bitset = new bitset(5, [1, 1, 1, 1, 1]);
		$this->assertTrue($bitset->all());
		$this->assertTrue($bitset->any());
		$this->assertFalse($bitset->none());

		$bitset = new bitset(3);
		$this->assertTrue($bitset->none());
		$this->assertFalse($bitset->all());
		$this->assertFalse($bitset->any());
	}

	public function testToInt() {
		$bitset = new bitset(10);
		$this->assertEquals(0, $bitset->toInt());

		$bitset = new bitset(10, [1, 1, 0, 0, 1, 1, 0, 0, 1, 1]);
		$this->assertEquals(819, $bitset->toInt());
	}
}