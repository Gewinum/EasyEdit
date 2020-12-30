<?php

namespace platz1de\EasyEdit\pattern;

use pocketmine\level\utils\SubChunkIteratorManager;

class Below extends Pattern
{
	/**
	 * @param int                     $x
	 * @param int                     $y
	 * @param int                     $z
	 * @param SubChunkIteratorManager $iterator
	 * @return bool
	 */
	public function isValidAt(int $x, int $y, int $z, SubChunkIteratorManager $iterator): bool
	{
		$y++;
		$iterator->moveTo($x, $y, $z);
		return ($iterator->currentSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f) === $this->args[0]->getId()) && ($iterator->currentSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f) === $this->args[0]->getDamage());
	}

	public function check(): void
	{
		try {
			$this->args[0] = Pattern::getBlock($this->args[0] ?? "");
		} catch (ParseError $error) {
			throw new ParseError("Below needs a block as first Argument, " . ($this->args[0] ?? "") . " given");
		}
	}
}