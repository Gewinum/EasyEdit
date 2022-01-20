<?php

namespace platz1de\EasyEdit\world;

use platz1de\EasyEdit\selection\Selection;
use pocketmine\block\Block;
use pocketmine\world\World;

class HeightMapCache
{
	//TODO: don't just delete this Blocks
	/**
	 * @var int[] these mess up the height calculation in different ways
	 */
	private static array $ignore;

	private static bool $loaded;
	/**
	 * @var int[][][] starting height -> thickness (downwards)
	 */
	private static array $heightMap = [];
	/**
	 * @var int[][][] starting height -> thickness (upwards)
	 */
	private static array $reverseHeightMap = [];

	private static ?int $currentX = null;
	private static ?int $currentZ = null;
	/**
	 * @var int[]
	 */
	private static array $current = [];
	/**
	 * @var int[]
	 */
	private static array $currentReverse = [];

	/**
	 * @param SafeSubChunkExplorer $iterator
	 * @param Selection            $selection
	 */
	public static function load(SafeSubChunkExplorer $iterator, Selection $selection): void
	{
		if (!self::$loaded) {
			$min = $selection->getCubicStart()->subtract(1, 1, 1);
			$max = $selection->getCubicEnd()->add(1, 1, 1);
			for ($x = $min->getFloorX(); $x <= $max->getX(); $x++) {
				for ($z = $min->getFloorZ(); $z <= $max->getZ(); $z++) {
					$y = World::Y_MAX - 1;
					while ($y >= World::Y_MIN) {
						while ($y >= World::Y_MIN && in_array($iterator->getBlockAt($x, $y, $z) >> Block::INTERNAL_METADATA_BITS, self::$ignore, true)) {
							$y--;
						}
						$c = $y;
						while ($y >= World::Y_MIN && !in_array($iterator->getBlockAt($x, $y, $z) >> Block::INTERNAL_METADATA_BITS, self::$ignore, true)) {
							$y--;
						}
						self::$heightMap[$x][$z][$c] = $c - $y + 1;
						self::$reverseHeightMap[$x][$z][$y] = $c - $y + 1;
					}
				}
			}
			self::$loaded = true;
		}
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return int blocks upwards until next air-like block
	 */
	public static function searchUpwards(int $x, int $y, int $z): int
	{
		self::moveTo($x, $z);
		$search = $y;
		while ($search < World::Y_MAX && !isset(self::$current[$search])) {
			$search++;
		}
		$depth = $search - $y + 1;
		return $depth > 0 ? $depth : 0;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return int blocks downwards until next air-like block
	 */
	public static function searchDownwards(int $x, int $y, int $z): int
	{
		self::moveTo($x, $z);
		$search = $y;
		while ($search < World::Y_MAX && !isset(self::$currentReverse[$search])) {
			$search++;
		}
		$depth = $y - $search + 1;
		return $depth > 0 ? $depth : 0;
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @return int[]
	 */
	public static function generateFullDepthMap(int $x, int $z): array
	{
		self::moveTo($x, $z);
		$depth = array_fill(0, World::Y_MAX - World::Y_MIN, 0);
		foreach (self::$current as $y => $value) {
			for ($i = 0; $i < $value; $i++) {
				$depth[$y - $i + 1] = min($i, $value - $i);
			}
		}
		return $depth;
	}

	public static function prepare(): void
	{
		self::$loaded = false;
		self::$heightMap = [];
		self::$reverseHeightMap = [];
		self::$current = [];
		self::$currentReverse = [];
		self::$currentX = null;
		self::$currentZ = null;
	}

	/**
	 * @param int[] $ignore
	 */
	public static function setIgnore(array $ignore): void
	{
		self::$ignore = $ignore;
	}

	/**
	 * @return int[]
	 */
	public static function getIgnore(): array
	{
		return self::$ignore;
	}

	private static function moveTo(int $x, int $z): void
	{
		if ($x !== self::$currentX || $z !== self::$currentZ) {
			self::$currentX = $x;
			self::$currentZ = $z;
			self::$current = self::$heightMap[$x][$z] ?? [];
			self::$currentReverse = self::$reverseHeightMap[$x][$z] ?? [];
		}
	}
}