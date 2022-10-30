<?php

namespace Unit;

use BGAWorkbench\Test\TableInstanceBuilder;
use Helpers\TestHelpers;
use Helpers\WithTable;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    use TestHelpers;
    use WithTable;

    protected function createGameTableInstanceBuilder() : TableInstanceBuilder
    {
        return $this->gameTableInstanceBuilder()
            ->setPlayersWithIds([66, 77])
            ->overridePlayersPostSetup([
                66 => ['player_color' => 'ff0000'],
                77 => ['player_color' => '00ff00']
            ]);
    }

    /**
     * Return \Innovation class, but as the user-specific name (to allow for easy dev workflow)
     *
     * @return \Innovation
     */
    public function getInnovationInstance()
    {
        $klass = BGA_GAME_CLASS;
        $this->table = $this->createGameTableInstanceBuilder()->build();
        $this->table->createDatabase();
        $klass::setDbConnection($this->table->getDbConnection());
        return new $klass();
    }
}
