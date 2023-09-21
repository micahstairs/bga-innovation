<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card176 extends AbstractCard
{
  // Corvette Challenger
  // - 3rd edition:
  //   - Draw and tuck an [8]. Splay up the color of the tucked card. Draw and score a card of
  //     value equal to the number of cards of that color visible on your board.
  // - 4th edition:
  //   - Draw and tuck an [8]. Splay up the color of the tucked card. Draw and score a card of
  //     value equal to the number of cards of that color on your board.  Junk all cards in the
  //     deck of that value.

  public function initialExecution()
  {
    $card = self::drawAndTuck(8);
    self::splayUp($card['color']);

    $numCards = self::countVisibleCardsInStack($card['color']);
    $args = ['i18n' => ['color'], 'number' => $numCards, 'color' => Colors::render($card['color'])];
    self::notifyPlayer(clienttranslate('There are ${number} ${color} card(s) visible on ${your} board.'), $args);
    self::notifyOthers(clienttranslate('There are ${number} ${color} card(s) visible on ${player_name}\'s board.'), $args);
    self::drawAndScore($numCards);

    if (self::isFourthEdition()) {
      self::junkBaseDeck($numCards);
    }
  }

}