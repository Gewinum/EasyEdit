<?php

namespace platz1de\EasyEdit\selection\piece;

use Closure;
use pocketmine\math\Vector3;
use pocketmine\utils\Utils;

class HollowSpherePiece extends SpherePiece
{
	/**
	 * HollowSpherePiece constructor.
	 * @param string       $player
	 * @param string       $world
	 * @param Vector3|null $pos1
	 * @param Vector3|null $min
	 * @param Vector3|null $max
	 * @param int          $radius
	 * @param int          $thickness
	 */
	public function __construct(string $player, string $world = "", ?Vector3 $pos1 = null, ?Vector3 $min = null, ?Vector3 $max = null, int $radius = 0, int $thickness = 1)
	{
		parent::__construct($player, $world, $pos1, $min, $max, $radius);
		$this->setThickness($thickness);
	}


	/**
	 * @param Vector3 $place
	 * @param Closure $closure
	 * @return void
	 */
	public function useOnBlocks(Vector3 $place, Closure $closure): void
	{
		Utils::validateCallableSignature(static function (int $x, int $y, int $z): void { }, $closure);
		$radius = $this->pos2->getX();
		$radiusSquared = $radius ** 2;
		$thicknessSquared = ($radius - $this->pos2->getY()) ** 2;
		$minX = max($this->min->getX() - $this->pos1->getX(), -$radius);
		$maxX = min($this->max->getX() - $this->pos1->getX(), $radius);
		$minY = $this->min->getY() - $this->pos1->getY();
		$maxY = $this->max->getY() - $this->pos1->getY();
		$minZ = max($this->min->getZ() - $this->pos1->getZ(), -$radius);
		$maxZ = min($this->max->getZ() - $this->pos1->getZ(), $radius);
		for ($x = $minX; $x <= $maxX; $x++) {
			for ($z = $minZ; $z <= $maxZ; $z++) {
				for ($y = $minY; $y <= $maxY; $y++) {
					if (($x ** 2) + ($y ** 2) + ($z ** 2) <= $radiusSquared && ($y === $minY || $y === $maxY || ($x ** 2) + ($y ** 2) + ($z ** 2) > $thicknessSquared)) {
						$closure($this->pos1->getX() + $x, $this->pos1->getY() + $y, $this->pos1->getZ() + $z);
					}
				}
			}
		}
	}

	/**
	 * @return int
	 */
	public function getThickness(): int
	{
		return $this->pos2->getFloorY();
	}

	/**
	 * @param int $thickness
	 */
	public function setThickness(int $thickness): void
	{
		$this->pos2->y = $thickness;
	}
}