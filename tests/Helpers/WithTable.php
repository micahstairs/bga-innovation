<?php

namespace Helpers;

use BGAWorkbench\External\WorkbenchProjectConfigSerialiser;
use BGAWorkbench\Project\WorkbenchProjectConfig;
use BGAWorkbench\Test\TableInstance;
use BGAWorkbench\Test\TableInstanceBuilder;

trait WithTable
{
    /** @var TableInstance */
    protected $table;
    protected static $cwdConfig = null;

    protected function setUp(): void
    {
        $this->table = $this->createGameTableInstanceBuilder()
            ->build()
            ->createDatabase();
    }

    protected function tearDown(): void
    {
        if ($this->table !== null) {
            $this->table->dropDatabaseAndDisconnect();
        }
    }

    /**
     * @return WorkbenchProjectConfig
     */
    private static function getCwdProjectConfig() : WorkbenchProjectConfig
    {
        if (self::$cwdConfig === null) {
            self::$cwdConfig = WorkbenchProjectConfigSerialiser::readFromCwd();
        }

        return self::$cwdConfig;
    }

    /**
     * @return TableInstanceBuilder
     */
    protected function gameTableInstanceBuilder() : TableInstanceBuilder
    {
        return TableInstanceBuilder::create(self::getCwdProjectConfig());
    }
}
