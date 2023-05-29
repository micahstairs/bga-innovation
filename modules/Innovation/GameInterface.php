<?php

namespace Innovation;

interface GameInterface {
    public function executeDraw(int $playerId, int $age);
    public function executeDrawAndScore(int $playerId, int $age);
}
