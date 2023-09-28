<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card209 extends AbstractCard
{
  // Maastricht Treaty (3rd edition):
  //   - If you have the most cards in your score pile, you win.
  // Musk's Tesla Roadster (4th edition):
  //   - If you have the most cards in your score pile, you win. Otherwise, draw and reveal an [11].
  //     Score all cards on your board of the color of the drawn card. If you do, return the drawn
  //     card. Otherwise, meld it.

  public function initialExecution()
  {
    $hasMostCards = true;
    $numCards = self::countCards(Locations::SCORE);
    foreach (self::getOtherPlayerIds() as $playerId) {
      if (self::countCards(Locations::SCORE, $playerId) > $numCards) {
        $hasMostCards = false;
        break;
      }
    }

    if ($hasMostCards) {
      self::notifyPlayer(clienttranslate('${You} have the most cards in your score pile.'));
      self::notifyOthers(clienttranslate('${player_name} has the most cards in his score pile.'));
      self::win();
    } else {
      self::notifyPlayer(clienttranslate('${You} do not have the most cards in your score pile.'));
      self::notifyOthers(clienttranslate('${player_name} does not have the most cards in his score pile.'));
      if (self::isFourthEdition()) {
        $card = self::drawAndReveal(11);
        $color = $card['color'];
        $stack = self::getStack($color);
        if ($stack) {
          self::scoreCards($stack);
          $args = ['i18n' => ['color'], 'color' => Colors::render($color)];
          self::notifyPlayer(clienttranslate('${You} scored all ${color} cards on your board.'), $args);
          self::notifyOthers(clienttranslate('${player_name} scored all ${color} cards on his board.'), $args);
          self::return($card);
        } else {
          self::meld($card);
        }
      }
    }
  }

}