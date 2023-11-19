<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card458 extends AbstractCard
{

  // Jumbo Kingdom
  //   - Choose a color on your board. Junk all cards of that color from all boards.
  //   - Choose a card in the junk. Score all cards of the chosen card's value in the junk. If you
  //     do, and you score fewer than eleven points, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'choose_color' => true,
        'color'        => self::getUniqueColors(Locations::BOARD),
      ];
    } else {
      return [
        'choose_from' => Locations::JUNK,
      ];
    }
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

  public function handleCardChoice(array $card)
  {
    $numPointsScored = 0;
    foreach (self::getCardsKeyedByValue(Locations::JUNK)[self::getValue($card)] as $card) {
      self::score($card);
      $numPointsScored += self::getValue($card);
    }
    if ($numPointsScored < 11) {
      self::setNextStep(1);
    }
  }

}