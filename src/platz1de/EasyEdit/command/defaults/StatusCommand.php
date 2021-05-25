<?php

namespace platz1de\EasyEdit\command\defaults;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\EasyEdit;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\worker\WorkerAdapter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class StatusCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/status", "Check on the EditThread", "easyedit.command.thread");
	}

	/**
	 * @param Player $player
	 * @param array  $args
	 * @param array  $flags
	 */
	public function process(Player $player, array $args, array $flags): void
	{
		//TODO: restart, shutdown, start, kill (other command?)
		if (EasyEdit::getWorker()->isTerminated()) {
			$status = TextFormat::RED . "CRASHED" . TextFormat::RESET;
		} elseif (EasyEdit::getWorker()->isShutdown()) {
			$status = TextFormat::RED . "STOPPED" . TextFormat::RESET;
		} elseif (EasyEdit::getWorker()->isRunning()) {
			$status = TextFormat::GOLD . "RUNNING" . TextFormat::RESET . ": ";
			$last = microtime(true) - EasyEdit::getWorker()->getLastResponse();
			if ($last < 1) {
				$status .= TextFormat::GREEN . round($last * 1000) . "ms" . TextFormat::RESET;
			} elseif ($last < 10) {
				$status .= TextFormat::GOLD . round($last * 1000) . "ms" . TextFormat::RESET;
			} else {
				$status .= TextFormat::RED . round($last * 1000) . "ms" . TextFormat::RESET;
			}
		} else {
			$status = TextFormat::GREEN . "OK" . TextFormat::RESET;
		}
		Messages::send($player, "worker-status", [
			"{task}" => WorkerAdapter::getCurrentTask(),
			"{queue}" => WorkerAdapter::getQueueLength(),
			"{status}" => $status
		]);
	}
}