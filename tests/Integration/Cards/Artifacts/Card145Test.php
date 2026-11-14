<?php

namespace Integration\Cards\Artifacts;

use Doctrine\DBAL\Connection;
use Innovation\Enums\Directions;
use Integration\Cards\BaseCardIntegrationTest;

class Card145Test extends BaseCardIntegrationTest
{
  public function test_authorityOnlyVisibleInSplayedStack_fourthEdition_compelHasEffect()
  {
    $launcherId = self::getActivePlayerId();
    $opponentId = self::getNonActivePlayerId();
    // Launcher melds Petition of Right (two concept icons) and a card with an
    // authority icon (so the non-demand effect needs a value-1 achievement, which is
    // removed below, making it genuinely ineffective).
    self::debugTransfer(145, 'meld', $launcherId);
    self::debugTransfer(12, 'meld', $launcherId);
    // Opponent's red stack: Archery (authority and concept on spots 1-2) buried under
    // a card whose only relevant icon is concept, splayed right so the authority icon
    // is visible but no top card has an authority icon.
    self::debugTransfer(3, 'meld', $opponentId);
    self::debugTransfer(37, 'meld', $opponentId);
    self::debugSplay(1, Directions::RIGHT, $opponentId);
    // Opponent needs a card in their score pile for the compel to transfer.
    self::debugTransfer(6, 'score', $opponentId);
    // Hide the value-1 achievement so the non-demand effect is genuinely ineffective
    // (the opponent's one authority icon would otherwise make it look effective).
    $this->tableInstance->withDbConnection(function (Connection $db) {
      $db->executeStatement("UPDATE card SET location = 'removed' WHERE location IN ('achievements', 'available achievements') AND owner = 0 AND age = 1");
    });
    $game = $this->tableInstance->getTable();
    $info = $game->getDogmaEffectInfo(self::getCard(145), $launcherId);
    self::assertFalse($info['no_effect'], 'Compel is effective: authority icon is visible in the opponent\'s splayed stack');
  }
}
