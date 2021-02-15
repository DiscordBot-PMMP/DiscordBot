<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets;

class Resolution extends Packet{

	/** @var int */
	private $pid;

	/** @var bool */
	private $successful = true;

	/** @var string|null */
	private $rejectReason = null;

	/** @var array */
	private $successData = [];

	public function getPid(): int{
		return $this->pid;
	}

	public function setPid(int $pid): void{
		$this->pid = $pid;
	}

	public function wasSuccessful(): bool{
		return $this->successful;
	}

	public function setSuccessful(bool $successful): void{
		$this->successful = $successful;
	}


	public function getRejectReason(): ?string{
		return $this->rejectReason;
	}

	public function setRejectReason(?string $rejectReason): void{
		$this->rejectReason = $rejectReason;
	}

	public function getSuccessData(): array{
		return $this->successData;
	}

	public function setSuccessData(array $successData): void{
		$this->successData = $successData;
	}

	public function serialize(): ?string{
		return serialize([$this->pid, $this->successful, $this->rejectReason, $this->successData]);
	}

	public function unserialize($serialized): void{
		[$this->pid, $this->successful, $this->rejectReason, $this->successData] = unserialize($serialized);
	}
}