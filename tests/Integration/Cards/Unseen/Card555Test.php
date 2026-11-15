<?php

namespace Integration\Cards\Unseen;

use Integration\Cards\BaseCardIntegrationTest;

class Card555Test extends BaseCardIntegrationTest
{
  // Blacklight:
  //   - Choose to either unsplay one color of your cards, or splay up an unsplayed color on your
  //     board and draw a [9].

  public function test_whenSplayUpIsImpossible_unseen_fourthEdition_doesNotDraw9()
  {
    $playerId = self::getActivePlayerId();
    $handBefore = self::countCards('hand', $playerId);

    self::dogma();
    self::assertEquals('choose_from_list', self::getSpecialChoiceType());
    self::chooseSpecial(2);

    self::assertDogmaComplete();
    self::assertEquals($handBefore, self::countCards('hand', $playerId), 'Should not draw a 9 if no unsplayed color can be splayed');
  }

  public function test_whenSplayingAnUnsplayedColor_unseen_fourthEdition_draws9()
  {
    $playerId = self::getActivePlayerId();
    $blacklight = self::getCard(555);
    foreach (self::getCards('deck') as $card) {
      if (intval($card['color']) === intval($blacklight['color']) && intval($card['type']) === 0) {
        self::debugTransfer(intval($card['id']), 'tuck', $playerId);
        break;
      }
    }
    self::assertGreaterThan(1, count(self::getCards('board', $playerId)));

    $handBefore = self::countCards('hand', $playerId);

    self::dogma();
    self::chooseSpecial(2);
    if (self::getCurrentStateName() === 'selectionMove') {
      self::selectRandomCard();
    }

    self::assertDogmaComplete();
    self::assertEquals($handBefore + 1, self::countCards('hand', $playerId), 'Splaying an unsplayed color should draw a 9');
    self::assertEquals(9, self::getMaxAge('hand', $playerId));
  }
}
