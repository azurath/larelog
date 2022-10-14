<?php

namespace Azurath\Larelog\Utils;

class Utils {

    protected float $startTime;

    /**
     * @return void
     */
    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    /**
     * @return float|null
     */
    public function end(): ?float
    {
        return $this->startTime ? microtime(true) - $this->startTime : null;
    }

}
