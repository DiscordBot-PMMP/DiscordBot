<?php

namespace TrafficCophp\ByteBuffer;

/**
 * LengthMap
 */
class LengthMap {

	protected $map;

	public function __construct() {
		$this->map = array(
			 'n' => 2,
			 'N' => 4,
			 'v' => 2,
			 'V' => 4,
			 'c' => 1,
			 'C' => 1
		);
	}

	public function getLengthFor($format) {
		return $this->map[$format];
	}

}