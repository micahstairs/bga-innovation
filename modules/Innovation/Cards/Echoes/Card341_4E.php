<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card341_4E extends AbstractCard
{

  // Soap (4th edition):
  //   - Choose a color. You may tuck any number of cards of that color from your hand. If you do,
  //     and your top card of that color is higher than each opponent's, you may achieve (if
  //     eligible) a card from your hand.

  public function initialExecution()
  {
    if (self::countCards(Locations::HAND) > 0) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_color' => true];
    } else if (self::isSecondInteraction()) {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 'all',
        'location_from' => Locations::HAND,
        'tuck_keyword'  => true,
        'color'         => [self::getAuxiliaryValue()],
      ];
    } else {
      return [
        'can_pass'            => true,
        'location_from'       => Locations::HAND,
        'achieve_if_eligible' => true,
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color);
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() >= 1) {
      $color = self::getAuxiliaryValue();
      $topCard = self::getTopCardOfColor($color);
      foreach (self::getOpponentIds() as $opponentId) {
        $opponentTopCard = self::getTopCardOfColor($color, $opponentId);
        if ($opponentTopCard && self::getValue($opponentTopCard) >= self::getValue($topCard)) {
          return; // There is at least one opponent with a top card of that color that is the same or higher than yours
        }
      }
      self::setMaxSteps(3);
    }
  }

}