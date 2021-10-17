<?php

namespace platz1de\EasyEdit\command\defaults;

use platz1de\EasyEdit\cache\HistoryCache;
use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\Messages;
use pocketmine\player\Player;

class UndoCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/undo", "Revert your latest change", "easyedit.command.undo", "//undo <count>");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		if (!HistoryCache::canUndo($player->getName())) {
			Messages::send($player, "no-history");
		}

		$count = min(100, (int) ($args[0] ?? 1));

		for ($i = 0; $i < $count; $i++) {
			HistoryCache::undoStep($player->getName());
		}
	}
}