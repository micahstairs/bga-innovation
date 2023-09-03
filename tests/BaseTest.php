<?php

use Helpers\TestHelpers;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase {
  use TestHelpers;

  /**
   * Return \Innovation class, but as the user-specific name (to allow for easy dev workflow)
   *
   * @return object
   */
  public function getInnovationInstance() {
    $klass = BGA_GAME_CLASS;
    return new $klass();
  }
}
