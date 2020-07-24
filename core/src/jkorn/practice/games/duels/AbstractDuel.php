<?php

declare(strict_types=1);

namespace jkorn\practice\games\duels;


use jkorn\practice\kits\IKit;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Server;
use jkorn\practice\games\IGame;

abstract class AbstractDuel implements IGame
{

    const STATUS_STARTING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_ENDING = 2;
    const STATUS_ENDED = 3;

    /** @var int - Constant used to determine the number of
     * ticks passed before we add the player to the duel. */
    const PUT_PLAYER_TICKS = 5;

    /** @var int - The current status of the duel. */
    protected $status = self::STATUS_STARTING;

    /** @var int */
    protected $countdownSeconds = 5, $endingSeconds = 2;
    /** @var int */
    protected $durationSeconds = 0;
    /** @var int */
    protected $currentTicks = 0;

    /** @var Server */
    protected $server;

    /** @var IKit */
    protected $kit;

    /** @var array */
    protected $results = [];

    /**
     * AbstractDuel constructor.
     * @param IKit $kit
     */
    public function __construct(IKit $kit)
    {
        $this->server = Server::getInstance();
        $this->kit = $kit;
        $this->results = [
            "winner" => null,
            "loser" => null
        ];
    }

        /**
     * Puts the players in the duel.
     */
    abstract protected function putPlayersInDuel(): void;

    /**
     * Updates the game.
     */
    public function update(): bool
    {
        $checkSeconds = $this->currentTicks % 20 === 0 && $this->currentTicks !== 0;

        if($this->status === self::STATUS_STARTING)
        {
            // After 5 ticks, put players in duel.
            if($this->currentTicks === self::PUT_PLAYER_TICKS)
            {
                $this->putPlayersInDuel();
                $this->currentTicks++;
                return true;
            }

            // Calls the in starting tick & updates the countdown.
            $result = $this->inStartingTick($checkSeconds);
            if($checkSeconds)
            {
                $this->countdownSeconds--;
            }

            $this->currentTicks++;
            return $result;
        }
        elseif ($this->status === self::STATUS_IN_PROGRESS)
        {
            $this->inProgressTick($checkSeconds);

            if($checkSeconds)
            {
                $this->durationSeconds++;
            }
        }
        elseif ($this->status === self::STATUS_ENDING)
        {
            if($checkSeconds && $this->endingSeconds > 0)
            {
                $this->endingSeconds--;

                if($this->endingSeconds === 0)
                {
                    $this->status = self::STATUS_ENDED;
                }
            }
        }
        elseif ($this->status === self::STATUS_ENDED)
        {
            $this->onEnd();
            $this->die();
            return false;
        }

        $this->currentTicks++;
        return true;
    }

    /**
     * @param bool $checkSeconds
     * @return bool - Whether or not the duel should continue to tick.
     *
     * Called in update function when duel is starting, doesn't run on
     * the tick where the players are being added.
     */
    abstract protected function inStartingTick(bool $checkSeconds): bool;

    /**
     * @param bool $checkSeconds
     *
     * Called when the duel is in progress.
     */
    abstract protected function inProgressTick(bool $checkSeconds): void;

    /**
     * Called when the duel has officially ended.
     */
    abstract protected function onEnd(): void;

    /**
     * @return Position
     *
     * Gets the center position of the duel.
     */
    abstract protected function getCenterPosition(): Position;
    
    /**
     * @return string
     *
     * Gets the countdown message of the duel.
     */
    protected function getCountdownMessage(): string
    {
        // TODO
        return "";
    }

    /**
     * @return Level
     *
     * Gets the level of the duel.
     */
    abstract protected function getLevel(): Level;
}