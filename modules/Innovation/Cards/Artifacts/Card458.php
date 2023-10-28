<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card458 extends AbstractCard
{

  // Jumbo Kingdom
  //   - Choose a color on your board. Junk all cards of that color from all boards.
  //   - Score all valued cards of lowest value in the junk. If you do, and you score fewer than 11
  //     points, repeat this effect.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      do {
        $numPointsScored = 0;
        $junkedCards = self::getCards(Locations::JUNK);
        $minValue = self::getMinValue($junkedCards);
        foreach ($junkedCards as $card) {
          if ($card['age'] == $minValue) {
            self::score($card);
            $numPointsScored += $card['age'];
          }
        }
      } while ($numPointsScored < 11);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'choose_color' => true,
      'color'        => self::getUniqueColors(Locations::BOARD),
    ];
  }

  public function handleColorChoice(int $color)
  {
    $cards = [];
    foreach (self::getPlayerIds() as $playerId) {
      $cards = array_merge($cards, self::getStack($color, $playerId));
    }
    $args = ['i18n' => ['color'], 'color' => Colors::render($color)];
    if (self::junkCards($cards)) {
      self::notifyPlayer(clienttranslate('${You} junked all ${color} cards from all boards.'), $args);
      self::notifyOthers(clienttranslate('${player_name} junked all ${color} cards from all boards.'), $args);
    } else {
      self::notifyAll(clienttranslate('None of the boards had any ${color} cards to junk.'), $args);
    }
  }

}