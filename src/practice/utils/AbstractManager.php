<?php

declare(strict_types=1);

namespace practice\utils;


use pocketmine\Server;
use practice\PracticeCore;

abstract class AbstractManager
{

    /** @var Server */
    protected $server;
    /** @var PracticeCore */
    protected $core;

    public function __construct(PracticeCore $core)
    {
        $this->core = $core;
        $this->server = $core->getServer();

        $this->load();
    }

    /**
     * Loads the data needed for the manager.
     *
     * @param bool $async
     */
    abstract protected function load(bool $async = false): void;

    /**
     * Saves the data from the manager.
     *
     * @param bool $async
    */
    abstract public function save(bool $async = false): void;
}