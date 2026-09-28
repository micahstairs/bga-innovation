<?php

namespace Integration\Cards\Base;

use Innovation\Enums\Colors;
use Integration\Cards\BaseCardIntegrationTest;

class Card31Test extends BaseCardIntegrationTest
{
  // Machinery 4th edition:
  //   - Score a card from your hand with [AUTHORITY].
  //   - You may splay your red cards left.

  public function test_whenSplayIsOffered_fourthEdition_doesNotPromptSplayASecondTime()
  {
    $redOnBoard = 0;
    foreach ([3, 4, 5, 6, 19, 27, 37] as $id) {
      $card = self::getCard($id);
      if (intval($card['color']) !== Colors::RED) {
        continue;
      }
      self::debugTransfer($id, 'tuck');
      $redOnBoard = 0;
      foreach (self::getCards('board') as $boardCard) {
        if (intval($boardCard['color']) === Colors::RED) {
          $redOnBoard++;
        }
      }
      if ($redOnBoard >= 2) {
        break;
      }
    }
    $redOnBoard = 0;
    foreach (self::getCards('board') as $card) {
      if (intval($card['color']) === Colors::RED) {
        $redOnBoard++;
      }
    }
    self::assertGreaterThanOrEqual(2, $redOnBoard, 'Need two red cards so the splay is actually offered');

    $launcherId = self::getActivePlayerId();
    self::dogma();

    $launcherSplayPasses = 0;
    while (self::getCurrentStateName() === 'selectionMove') {
      $canPass = intval(self::getGlobalVariable('can_pass')) === 1;
      if ($canPass) {
        if (self::getActivePlayerId() === $launcherId) {
          $launcherSplayPasses++;
          self::assertSame(1, $launcherSplayPasses, '4E should offer the red splay to the launcher once');
        }
        self::pass();
      } else if (count(self::getSelectedCards()) > 0) {
        self::selectRandomCard();
      } else {
        self::pass();
      }
    }

    self::assertDogmaComplete();
    self::assertSame(1, $launcherSplayPasses, 'Launcher should be offered the splay exactly once');
  }
}
