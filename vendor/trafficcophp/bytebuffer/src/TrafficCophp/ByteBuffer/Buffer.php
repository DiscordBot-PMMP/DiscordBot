<?php

namespace TrafficCophp\ByteBuffer;

/**
 * ByteBuffer
 */
class Buffer extends AbstractBuffer {

	const DEFAULT_FORMAT = 'x';

	/**
	 * @var \SplFixedArray
	 */
	protected $buffer;

	/**
	 * @var LengthMap
	 */
	protected $lengthMap;

	public function __construct($argument) {
		$this->lengthMap = new LengthMap();
		if (is_string($argument)) {
			$this->initializeStructs(strlen($argument), $argument);
		} else if (is_int($argument)) {
			$this->initializeStructs($argument, pack(self::DEFAULT_FORMAT.$argument));
		} else {
			throw new \InvalidArgumentException('Constructor argument must be an binary string or integer');
		}
	}

	protected function initializeStructs($length, $content) {
		$this->buffer = new \SplFixedArray($length);
		for ($i = 0; $i < $length; $i++) {
			$this->buffer[$i] = $content[$i];
		}
	}

	protected function insert($format, $value, $offset, $length) {
		$bytes = pack($format, $value);
		for ($i = 0; $i < strlen($bytes); $i++) {
			$this->buffer[$offset++] = $bytes[$i];
		}
	}

	protected function extract($format, $offset, $length) {
		$encoded = '';
		for ($i = 0; $i < $length; $i++) {
			$encoded .= $this->buffer->offsetGet($offset + $i);
		}
		if ($format == 'N'&& PHP_INT_SIZE <= 4) {
			list(, $h, $l) = unpack('n*', $encoded);
			$result = ($l + ($h * 0x010000));
		} else if ($format == 'V' && PHP_INT_SIZE <= 4) {
			list(, $h, $l) = unpack('v*', $encoded);
			$result = ($h + ($l * 0x010000));
		} else {
			list(, $result) = unpack($format, $encoded);
		}
		return $result;
	}

	protected function checkForOverSize($excpected_max, $actual) {
		if ($actual > $excpected_max) {
			throw new \InvalidArgumentException(sprintf('%d exceeded limit of %d', $actual, $excpected_max));
		}
	}

	public function __toString() {
		$buf = '';
		foreach ($this->buffer as $bytes) {
			$buf .= $bytes;
		}
		return $buf;
	}

	public function length() {
		return $this->buffer->getSize();
	}

	public function write($string, $offset) {
		$length = strlen($string);
		$this->insert('a' . $length, $string, $offset, $length);
	}

	public function writeInt8($value, $offset) {
		$format = 'C';
		$this->checkForOverSize(0xff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt16BE($value, $offset) {
		$format = 'n';
		$this->checkForOverSize(0xffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt16LE($value, $offset) {
		$format = 'v';
		$this->checkForOverSize(0xffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt32BE($value, $offset) {
		$format = 'N';
		$this->checkForOverSize(0xffffffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function writeInt32LE($value, $offset) {
		$format = 'V';
		$this->checkForOverSize(0xffffffff, $value);
		$this->insert($format, $value, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function read($offset, $length) {
		$format = 'a' . $length;
		return $this->extract($format, $offset, $length);
	}

	public function readInt8($offset) {
		$format = 'C';
		return $this->extract($format, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function readInt16BE($offset) {
		$format = 'n';
		return $this->extract($format, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function readInt16LE($offset) {
		$format = 'v';
		return $this->extract($format, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function readInt32BE($offset) {
		$format = 'N';
		return $this->extract($format, $offset, $this->lengthMap->getLengthFor($format));
	}

	public function readInt32LE($offset) {
		$format = 'V';
		return $this->extract($format, $offset, $this->lengthMap->getLengthFor($format));
	}

}