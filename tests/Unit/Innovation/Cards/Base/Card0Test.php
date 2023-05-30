<?php

namespace Unit\Innovation\Cards\Base;

use Helpers\FakeGame;
use Innovation\Cards\Base\Card0;
use Unit\BaseTest;
use BGAWorkbench\Utils;
use Doctrine\DBAL\Connection;

class Card0Test extends BaseTest {

  public function testAction()
    {
        $action = $this->table
            ->setupNewGame()
            ->withDbConnection(function (Connection $db) {
                $db->exec('INSERT battlefield_card (player_id, type, x, y) VALUES (' .
                    join('), (', [
                        [77, '"infantry"', 0, -1],  
                        [66, '"infantry"', 0, 1],  
                        [66, '"artillery"', 6, 1],  
                    ])
                . ')');
            })
            ->createActionInstanceForCurrentPlayer(66)
            ->stubActivePlayerId(66)
            ->stubArgs(['x' => 5, 'y' => 5]);

        $action->chooseAttack();
        
        // TODO: Run some asserts on the db
    }

  public function test() {
    // TODO(LATER): Add a test here.
  }
}
