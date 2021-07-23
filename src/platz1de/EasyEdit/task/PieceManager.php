<?php

namespace platz1de\EasyEdit\task;

use platz1de\EasyEdit\EasyEdit;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\task\queued\QueuedCallbackTask;
use platz1de\EasyEdit\task\queued\QueuedEditTask;
use platz1de\EasyEdit\utils\AdditionalDataManager;
use platz1de\EasyEdit\utils\LoaderManager;
use platz1de\EasyEdit\utils\MixedUtils;
use platz1de\EasyEdit\worker\WorkerAdapter;

class PieceManager
{
	/**
	 * @var QueuedEditTask
	 */
	private $task;
	/**
	 * @var Selection[]
	 */
	private $pieces;
	/**
	 * @var EditTask
	 */
	private $currentPiece;
	/**
	 * @var int
	 */
	private $totalLength;
	/**
	 * @var EditTaskResult
	 */
	private $result;

	/**
	 * PieceManager constructor.
	 * @param QueuedEditTask $task
	 */
	public function __construct(QueuedEditTask $task)
	{
		$this->task = $task;
		$this->pieces = $task->getSelection()->split($task->getSplitOffset());
		$this->totalLength = count($this->pieces);
	}

	/**
	 * @return bool whether all pieces are done
	 */
	public function continue(): bool
	{
		if ($this->currentPiece->isFinished()) {
			$result = $this->currentPiece->getResult();
			$data = $this->currentPiece->getAdditionalData();

			if ($result instanceof EditTaskResult && $data instanceof AdditionalDataManager) {
				if ($data->getBoolKeyed("edit")) {
					LoaderManager::setChunks($result->getManager()->getWorld(), $result->getManager()->getChunks(), $result->getTiles());
				}

				if ($this->result === null) {
					$this->result = $result;
				} else {
					$this->result->merge($result);
					$result->getUndo()->free();
				}

				$result->free();

				if (count($this->pieces) > 0) {
					$data->setDataKeyed("firstPiece", false);
					if (count($this->pieces) === 1) {
						$data->setDataKeyed("finalPiece", true);
					}
					$task = $this->task->getTask();

					//reduce load by not setting and loading on the same tick
					WorkerAdapter::priority(new QueuedCallbackTask(function () use ($data, $task): void {
						$this->currentPiece = new $task(array_pop($this->pieces), $this->task->getPattern(), $this->task->getPlace(), $data);
						EasyEdit::getWorker()->stack($this->currentPiece);
					}));
					return false; //more to go
				}

				$this->task->finishWith($this->result);

				$this->currentPiece->notifyUser($this->task->getSelection(), round($this->result->getTime(), 2), MixedUtils::humanReadable($this->result->getChanged()), $data);
			}
			return true;
		}
		return false; //not finished yet
	}

	public function start(): void
	{
		$this->task->getData()->setDataKeyed("firstPiece", true);
		if (count($this->pieces) === 1) {
			$this->task->getData()->setDataKeyed("finalPiece", true);
		}
		$task = $this->task->getTask();
		$this->currentPiece = new $task(array_pop($this->pieces), $this->task->getPattern(), $this->task->getPlace(), $this->task->getData(), $this->task->getSelection());
		EasyEdit::getWorker()->stack($this->currentPiece);
	}

	/**
	 * @return EditTask
	 */
	public function getCurrent(): EditTask
	{
		return $this->currentPiece;
	}

	/**
	 * @return QueuedEditTask
	 */
	public function getQueued(): QueuedEditTask
	{
		return $this->task;
	}

	/**
	 * @return int
	 */
	public function getTotalLength(): int
	{
		return $this->totalLength;
	}

	/**
	 * @return int
	 */
	public function getLength(): int
	{
		return count($this->pieces) + 1;
	}

	/**
	 * @return EditTaskResult Current result of task, may not be finished yet
	 */
	public function getResult(): ?EditTaskResult
	{
		return $this->result ?? null;
	}
}