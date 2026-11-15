<?php

namespace Integration\Cards\Unseen;

use Integration\Cards\BaseCardIntegrationTest;

class Card583Test extends BaseCardIntegrationTest
{
  // 3D Printing:
  //   - Return a top or bottom card on your board. ...

  public function test_whenTwoCardsShareAColor_unseen_fourthEdition_offersTopOrBottom()
  {
    $printing = self::getCard(583);
    foreach (self::getCards('deck') as $card) {
      if (intval($card['color']) === intval($printing['color']) && intval($card['id']) !== 583) {
        self::debugTransfer(intval($card['id']), 'tuck');
        break;
      }
    }
    self::assertGreaterThan(1, count(self::getCards('board')));

    self::dogma();

    self::assertEquals('selectionMove', self::getCurrentStateName());
    self::assertEquals('choose_from_list', self::getSpecialChoiceType());
    $choices = self::getGlobalVariableAsArray('choice_array');
    sort($choices);
    self::assertEquals([1, 2], $choices, 'A stack with more than one card should offer top or bottom');
  }

  public function test_whenTwoTopsShareAnAgeButNotAColor_unseen_fourthEdition_doesNotOfferBottom()
  {
    $printing = self::getCard(583);
    foreach (self::getCards('deck') as $card) {
      if (intval($card['age']) === intval($printing['age']) && intval($card['color']) !== intval($printing['color']) && intval($card['type']) === 0) {
        self::debugTransfer(intval($card['id']), 'meld');
        break;
      }
    }

    self::dogma();

    self::assertEquals('selectionMove', self::getCurrentStateName());
    self::assertNotEquals('choose_from_list', self::getSpecialChoiceType(), 'Singleton stacks should skip the top/bottom prompt and return a top card');
  }
}
