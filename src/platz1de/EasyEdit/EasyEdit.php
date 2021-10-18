<?php

namespace platz1de\EasyEdit;

use platz1de\EasyEdit\command\CommandManager;
use platz1de\EasyEdit\command\defaults\BenchmarkCommand;
use platz1de\EasyEdit\command\defaults\BrushCommand;
use platz1de\EasyEdit\command\defaults\CancelCommand;
use platz1de\EasyEdit\command\defaults\CenterCommand;
use platz1de\EasyEdit\command\defaults\CopyCommand;
use platz1de\EasyEdit\command\defaults\CountCommand;
use platz1de\EasyEdit\command\defaults\CylinderCommand;
use platz1de\EasyEdit\command\defaults\ExtendCommand;
use platz1de\EasyEdit\command\defaults\ExtinguishCommand;
use platz1de\EasyEdit\command\defaults\FirstPositionCommand;
use platz1de\EasyEdit\command\defaults\HollowCylinderCommand;
use platz1de\EasyEdit\command\defaults\HollowSphereCommand;
use platz1de\EasyEdit\command\defaults\InsertCommand;
use platz1de\EasyEdit\command\defaults\MoveCommand;
use platz1de\EasyEdit\command\defaults\NaturalizeCommand;
use platz1de\EasyEdit\command\defaults\PasteCommand;
use platz1de\EasyEdit\command\defaults\RedoCommand;
use platz1de\EasyEdit\command\defaults\ReplaceCommand;
use platz1de\EasyEdit\command\defaults\SecondPositionCommand;
use platz1de\EasyEdit\command\defaults\SetCommand;
use platz1de\EasyEdit\command\defaults\SidesCommand;
use platz1de\EasyEdit\command\defaults\SmoothCommand;
use platz1de\EasyEdit\command\defaults\SphereCommand;
use platz1de\EasyEdit\command\defaults\StackCommand;
use platz1de\EasyEdit\command\defaults\StatusCommand;
use platz1de\EasyEdit\command\defaults\UndoCommand;
use platz1de\EasyEdit\command\defaults\WallCommand;
use platz1de\EasyEdit\thread\EditAdapter;
use platz1de\EasyEdit\thread\EditThread;
use platz1de\EasyEdit\utils\CompoundTile;
use platz1de\EasyEdit\utils\ConfigManager;
use pocketmine\block\tile\TileFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class EasyEdit extends PluginBase
{
	private static EasyEdit $instance;

	public function onEnable(): void
	{
		self::$instance = $this;

		Messages::load();
		ConfigManager::load();

		$worker = new EditThread(Server::getInstance()->getLogger());
		$worker->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);

		$this->getScheduler()->scheduleRepeatingTask(new EditAdapter(), 1);

		Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $this);

		CommandManager::registerCommands([
			new SetCommand(),
			new FirstPositionCommand(),
			new SecondPositionCommand(),
			new UndoCommand(),
			new RedoCommand(),
			new CopyCommand(),
			new PasteCommand(),
			new ReplaceCommand(),
			new InsertCommand(),
			new CenterCommand(),
			new ExtendCommand(),
			new MoveCommand(),
			new SphereCommand(),
			new HollowSphereCommand(),
			new StackCommand(),
			new BrushCommand(),
			new NaturalizeCommand(),
			new SmoothCommand(),
			new CylinderCommand(),
			new HollowCylinderCommand(),
			new WallCommand(),
			new SidesCommand(),
			new CountCommand(),
			new StatusCommand(),
			new CancelCommand(),
			new BenchmarkCommand(),
			new ExtinguishCommand()
		]);

		//Just for sending block data without using the protocol directly
		TileFactory::getInstance()->register(CompoundTile::class);
	}

	/**
	 * @return EasyEdit
	 */
	public static function getInstance(): self
	{
		return self::$instance;
	}
}