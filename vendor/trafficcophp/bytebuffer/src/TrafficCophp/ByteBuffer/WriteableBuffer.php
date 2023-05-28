<?php

namespace TrafficCophp\ByteBuffer;

interface WriteableBuffer {
	public function write($string, $offset);
	public function writeInt8($value, $offset);
	public function writeInt16BE($value, $offset);
	public function writeInt16LE($value, $offset);
	public function writeInt32BE($value, $offset);
	public function writeInt32LE($value, $offset);
}
