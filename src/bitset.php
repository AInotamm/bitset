<?php

namespace redrock\bitset;

use ArrayAccess;
use Countable;
use Serializable;

class bitset implements ArrayAccess, Countable, Serializable
{
	/**
	 * 位图长度，应规定不大于PHP_INT_SIZE
	 * @var integer
	 */
	protected $size;
	/**
	 * 位图大小
	 * @var integer
	 */
	protected $value;
	/**
	 * 存放位图的数组
	 * @var array
	 */
	private $set = [];
	/**
	 * 该值会被转换为规则的true
	 */
	private $true = 1;
	/**
	 * 该值会被转换为规则的false
	 */
	private $false = 0;

	/**
	 * 构造size长度的位图，可以传入固定长度的
	 * @param [type] $size    [description]
	 * @param array  $boolean [description]
	 */
	public function __construct($size, array $alloc = [], $true = 1, $false = 0) {
		if ($size < 0 || $size > \PHP_INT_SIZE)
			$this->size = 0;

		if ($size > 0) {
			if (!is_null($alloc)) {
				if ($this->size != count($alloc))
					throw \ErrorException('Invalid Allocate Size!');

				$this->setBoolValue($true, $false);
				$this->set = $alloc;
			} else {
				// 初始化所有位点的值为0
				for ($i = 0; $i < $size; $i++)
					$this->set[$i] = $this->false;
			}
		}
	}

	/**
	 * 检查对应下标的值是否设置为true
	 * @param  integer $offset
	 * @return bool
	 * @throw ErrorException
	 */
	public function test($offset) {
		if (!$this->offsetExists($offset))
			throw \ErrorException('Out of range.');

		return $this->set[$offset] === $this->true;
	}

	/**
	 * 判断位图的所有位置是否都被设置为true
	 * @return bool
	 */
	public function all() {

	}

	/**
	 * 判断位图的是否有被设置过true
	 * @return bool
	 */
	public function any() {

	}

	/**
	 * 判断位图的所有位置是否都被设置为false
	 * @return bool
	 */
	public function none() {

	}

	/**
	 * 将位图转换为合理的整型数值
	 * @return integer 位图的实际值
	 */
	public function toInt() {

	}

	/**
	 * 通过该函数，value会被转换成规则的bool值返回
	 * @param  mixed $value
	 * @return bool
	 * @throw ErrorException
	 */
	private function bool($value) {
		if ($true === $value)
			return 1;
		else if ($false === $value)
			return 0;
		else
			throw \ErrorException("Cannot change this value: " . $value);
	}

	private function setBoolValue($true, $false) {
		// 满足该条件时不更改
		if (1 == intval($true) || 0 === intval($false))
			return ;

		$this->true = $true;
		$this->false = $false;
	}

	/**
	 * 返回位图的实际长度
	 * @return integer
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * 实现count()函数对bitset类的应用
	 * 仅统计bitset中的bit值为1的个数
	 * @return integer 
	 */
	public function count() {
		$count = 0;

		for ($i = 0; $i < $this->size; $i++) {
			if ($this->test($i))
				$count++;
		}

		return $count;
	}

	/**
	 * 判断对应下标索引是否存在
	 * @param  integer $offset bitset容器的偏移值
	 * @return bool
	 */
	public function offsetExists($offset) {
		return is_int($offset) && (
			$offset < 0 || $offset > $this->size || isset($this->set[$offset])
		);
	}

	/**
	 * 返回对应下标的bit值
	 * @param  integer $offset
	 * @return bool
	 */
	public function offsetGet($offset) {
		if (!is_int($offset))
			throw \ErrorException('The offset type must be integer.');

		return $this->bool($this->set[$offset]);
	}

	/**
	 * 设置对应下标的bit值
	 * @param  integer $offset
	 * @param  bool|integer|string $value  
	 * 可以通过内置bool()方法转换为规则的bool值
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if (!is_int($offset))
			throw \ErrorException('The offset type must be integer.');

		if ($offset > \PHP_INT_SIZE - 1)
			throw \ErrorException('Out of range.');

		$this->set[$offset] = $value;

		if (!$this->offsetExists($offset)) 
			$this->size++;
	}

	public function offsetUnset($offset) {
		if ($this->offsetExists($offset)) {
			unset($this->set[$offset]);
			$this->size--;
		}
	}

	public function serialize() {

	}

	public function unserialize($serialized) {

	}
}

if (!function_exists('bitset_and')) {
	function bitset_and(bitset $x, bitset $y) {

	}
}

if (!function_exists('bitset_not')) {
	function bitset_not(bitset $x, bitset $y) {
		
	}
}

if (!function_exists('bitset_or')) {
	function bitset_or(bitset $x, bitset $y) {
		
	}
}

if (!function_exists('bitset_xor')) {
	function bitset_xor(bitset $x, bitset $y) {
		
	}
}

