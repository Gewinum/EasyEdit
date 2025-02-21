<?php

namespace platz1de\EasyEdit\command\exception;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\session\Session;
use pocketmine\lang\Translatable;
use UnexpectedValueException;

class InvalidUsageException extends CommandException
{
	public function __construct(EasyEditCommand $command)
	{
		if ($command->getUsage() instanceof Translatable) {
			throw new UnexpectedValueException("EasyEdit commands should not use translatable usages");
		}
		parent::__construct($command->getUsage());
	}

	public function sendWarning(Session $session): void
	{
		$session->sendMessage("wrong-usage", ["{usage}" => $this->getMessage()]);
	}
}