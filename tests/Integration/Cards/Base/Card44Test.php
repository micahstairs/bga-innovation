<?php

namespace Integration\Cards\Base;

use Integration\Cards\BaseCardIntegrationTest;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card44Test extends BaseCardIntegrationTest
{
  // Reformation (4th edition):
  //   - You may splay your yellow or purple cards right.
  //   - You may tuck a card from your hand for every splayed color on your board.

  public function test_whenTucking_fourthEdition_canStopBeforeTuckingEverySplayedColor()
  {
    self::setGlobalVariable('debug_mode', 1);
    self::debugTransfer(44, 'meld');

    $playerId = self::getActivePlayerId();

    // Splay two colors so the tuck asks for up to two cards.
    foreach ([Colors::BLUE, Colors::RED] as $color) {
      $needed = 2;
      foreach (self::getCards('board', $playerId) as $card) {
        if (intval($card['color']) === $color) {
          $needed--;
        }
      }
      foreach (self::getCards('deck') as $card) {
        if ($needed <= 0) {
          break;
        }
        if (intval($card['color']) === $color && intval($card['type']) === 0) {
          self::debugTransfer(intval($card['id']), 'meld');
          $needed--;
        }
      }
      self::debugSplay($color, Directions::LEFT);
    }

    $splayedColors = [];
    foreach (self::getCards('board', $playerId) as $card) {
      if (intval($card['splay_direction']) > 0) {
        $splayedColors[intval($card['color'])] = true;
      }
    }
    self::assertCount(2, $splayedColors, 'Need two splayed colors so the tuck is for two cards');

    self::setHandSize(3);
    self::assertEquals(3, self::countCards('hand'));

    self::dogma();
    if (self::getCurrentStateName() === 'selectionMove') {
      $handIds = [];
      foreach (self::getCards('hand') as $card) {
        $handIds[intval($card['id'])] = true;
      }
      $offeringHandTuck = false;
      foreach (self::getSelectedCards() as $card) {
        if (isset($handIds[intval($card['id'])])) {
          $offeringHandTuck = true;
          break;
        }
      }
      if (!$offeringHandTuck) {
        self::pass(); // decline the yellow/purple splay
      }
    }

    self::assertEquals('selectionMove', self::getCurrentStateName());
    $first = self::getSelectedCards()[0];
    self::selectCard(intval($first['id']));

    self::assertEquals('selectionMove', self::getCurrentStateName(), 'Should still be able to stop after tucking one of two');
    self::pass();

    self::assertDogmaComplete();
    self::assertEquals(2, self::countCards('hand'), 'Only one card should have been tucked');
  }
}
