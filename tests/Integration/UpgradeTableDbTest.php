<?php

namespace Integration;

use Doctrine\DBAL\Connection;

class UpgradeTableDbTest extends BaseIntegrationTest
{
  // The auxiliary_value_table.nesting_index column used to be UNSIGNED. The migration
  // migrateAuxiliaryValueTableNestingIndex() must migrate every existing copy of the table (including a savepoint
  // copy, when one exists) and must not touch copies which are already migrated, so that
  // tables upgraded once stay quiet on later deploys.

  private function getColumnType(string $tableName): string
  {
    $type = '';
    $this->tableInstance->withDbConnection(function (Connection $db) use ($tableName, &$type) {
      $type = strtolower(strval($db->fetchOne(
        "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'nesting_index'",
        [$tableName]
      )));
    });
    return $type;
  }

  private function forceUnsigned(string $tableName): void
  {
    $this->tableInstance->withDbConnection(function (Connection $db) use ($tableName) {
      $db->executeStatement("ALTER TABLE `$tableName` MODIFY COLUMN `nesting_index` SMALLINT UNSIGNED NOT NULL");
    });
  }

  private function dropSavepointCopy(): void
  {
    $this->tableInstance->withDbConnection(function (Connection $db) {
      $db->executeStatement("DROP TABLE IF EXISTS `zz_savepoint_auxiliary_value_table`");
    });
  }

  private function createUnsignedSavepointCopy(): void
  {
    $this->tableInstance->withDbConnection(function (Connection $db) {
      $db->executeStatement("DROP TABLE IF EXISTS `zz_savepoint_auxiliary_value_table`");
      $db->executeStatement("CREATE TABLE `zz_savepoint_auxiliary_value_table` LIKE `auxiliary_value_table`");
      $db->executeStatement("ALTER TABLE `zz_savepoint_auxiliary_value_table` MODIFY COLUMN `nesting_index` SMALLINT UNSIGNED NOT NULL");
    });
  }

  public function test_unsignedMainCopyIsMigrated()
  {
    $this->forceUnsigned('auxiliary_value_table');
    $this->dropSavepointCopy();

    $this->tableInstance->getTable()->migrateAuxiliaryValueTableNestingIndex();

    self::assertStringNotContainsString('unsigned', $this->getColumnType('auxiliary_value_table'));

    // A -1 nesting index (used by the search icon interaction) must fit after the migration.
    $this->tableInstance->withDbConnection(function (Connection $db) {
      $db->executeStatement("INSERT INTO `auxiliary_value_table` (`nesting_index`, `array_index`, `value`) VALUES (-1, 1, 5)");
    });
  }

  public function test_unsignedSavepointCopyIsMigratedToo()
  {
    $this->forceUnsigned('auxiliary_value_table');
    $this->createUnsignedSavepointCopy();

    $this->tableInstance->getTable()->migrateAuxiliaryValueTableNestingIndex();

    self::assertStringNotContainsString('unsigned', $this->getColumnType('auxiliary_value_table'));
    self::assertStringNotContainsString('unsigned', $this->getColumnType('zz_savepoint_auxiliary_value_table'));
  }

  public function test_migratedTableIsLeftAlone()
  {
    // Main copy is already signed (fresh database), and there is no savepoint copy. Running
    // the upgrade again must be a no-op and must not error.
    $this->dropSavepointCopy();

    $this->tableInstance->getTable()->migrateAuxiliaryValueTableNestingIndex();
    $this->tableInstance->getTable()->migrateAuxiliaryValueTableNestingIndex();

    self::assertStringNotContainsString('unsigned', $this->getColumnType('auxiliary_value_table'));
  }
}
