<?php

namespace redrock\bitset;

use ArrayAccess;
use Countable;
use Serializable;
use ErrorException;
use Closure;

class bitset implements ArrayAccess, Countable, Serializable
{
	/** 位图的最大长度 */
	const MAX_SIZE = 8 * \PHP_INT_SIZE - 3;

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
	private $set_ = [];
	/**
	 * 该值会被转换为规则的true
	 */
	private $true_;
	/**
	 * 该值会被转换为规则的false
	 */
	private $false_;

	/**
	 * 构造size长度的位图，可以传入固定长度的位图分配器
	 * @param integer $size    位图长度
	 * @param array   $boolean 用以分配位图的数组
	 * @param mixed   $true
	 * @param mixed   $false
	 */
	public function __construct($size, array $alloc = [], $true = 1, $false = 0) {
		if ($size <= 0 || $size > self::MAX_SIZE)
			$this->size = 0;
		else {
			$this->setBoolValue($true, $false);

			if (!empty($alloc)) 
				if ($size < count($alloc))
					throw new ErrorException('Invalid Allocate Size!');

			for ($i = 0; $i < $size; $i++) {
				$this->set_[$i] = isset($alloc[$i]) ? $alloc[$i] : $this->false_;
			}

			$this->size = $size;
		}
	}

	/**
	 * 检查对应下标的值是否设置为true
	 * 可以加入全局判断条件，指定的下标将作为偏移值
	 * @param  integer $offset
	 * @param  string  $predicate 
	 * 允许的值有'any', 'all'以及'none'
	 * @return bool
	 * @throw ErrorException
	 */
	public function test($offset, $predicate = '') {
		if (!$this->offsetExists($offset))
			throw new ErrorException('Out of range.');

		$mark = $this->true_;

		if ($predicate !== '') {
			if ($predicate === 'none')
				$mark = $this->false_;

			// 检测真值
			$test_true = Closure::bind(function ($value) use ($mark) {
				return $value === $this->true_;
			}, $this);

			for ($i = $offset; $i >= 0; --$i) {
				$value = $this->set_[$i];
				$bool = $test_true($value);

				if (!$bool) {
					if ($predicate === 'all') return false;
				} else {
					if ($predicate === 'any') return true;
					if ($predicate === 'none') return false;
				}
			}
		}

		return $this->set_[$offset] === $mark;
	}

	/**
	 * 判断位图的所有位置是否都被设置为true
	 * @return bool
	 */
	public function all() {
		return $this->test($this->size - 1, 'all');
	}

	/**
	 * 判断位图的是否有被设置过true
	 * @return bool
	 */
	public function any() {
		return $this->test($this->size - 1, 'any');
	}

	/**
	 * 判断位图的所有位置是否都被设置为false
	 * @return bool
	 */
	public function none() {
		return $this->test($this->size - 1, 'none');
	}

	/**
	 * 将位图转换为合理的整型数值
	 * @return integer 位图的实际值
	 */
	public function toInt() {
		$value = 0; $exponent = 1;

		for ($i = $this->size - 1; $i >= 0; $i--, $exponent <<= 1) 
			if ($this->bool($this->set_[$i]))
				$value += $exponent;

		return $value;
	}

	/**
	 * 将位图转换为规范的01位图
	 * @return string
	 */
	public function __toString() {
		$str = '';

		for ($i = 0; $i < $this->size; $i++)
			$str .= $this->bool($this->set_[$i]);

		return $str;
	}

	/**
	 * 通过该函数，value会被转换成规则的bool值返回
	 * @param  mixed $value
	 * @return bool
	 * @throw ErrorException
	 */
	private function bool($value) {
		if ($this->true_ === $value)
			return 1;
		else if ($this->false_ === $value)
			return 0;

		throw new ErrorException('Cannot change this value: ' . $value . ', Now the TRUE as ' . $this->true_ . ', the FALSE as ' . $this->false_);
	}

	private function setBoolValue($true, $false) {
		if (!is_numeric($false)) {
			if (is_null($false) || empty($false)) {
				throw new ErrorException('Cannot set this invalid value: ' . $false);
			}
		} else if (!is_int($false)) {
			// 满足该条件时不更改
			if (1 === intval($true) || 0 === intval($false))
				return ;
		}

		$this->true_ = $true;
		$this->false_ = $false;
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
			$offset < 0 || $offset > $this->size || isset($this->set_[$offset])
		);
	}

	/**
	 * 返回对应下标的bit值
	 * @param  integer $offset
	 * @return bool
	 */
	public function offsetGet($offset) {
		if (!is_int($offset))
			throw new ErrorException('The offset type must be integer.');

		return $this->bool($this->set_[$offset]);
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
			throw new ErrorException('The offset type must be integer.');

		if ($offset > $this->size)
			throw new ErrorException('Out of range.');

		$this->set_[$offset] = $this->bool($value);

		if (!$this->offsetExists($offset)) 
			$this->size++;
	}

	public function offsetUnset($offset) {
		if ($this->offsetExists($offset)) {
			unset($this->set_[$offset]);
			$this->size--;
		}
	}

	public function serialize() {
		return $this->__toString();
	}

	public function unserialize($serialized) {

	}
}

if (!function_exists('bitset_and')) {
	function bitset_and(bitset &$x, bitset $y) {

	}
}

if (!function_exists('bitset_not')) {
	function bitset_not(bitset &$x) {

	}
}

if (!function_exists('bitset_or')) {
	function bitset_or(bitset &$x, bitset $y) {
		
	}
}

if (!function_exists('bitset_xor')) {
	function bitset_xor(bitset &$x, bitset $y) {
		
	}
}

