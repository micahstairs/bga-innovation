<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card42_4E extends AbstractCard
{
  // Perspective (4th edition):
  //   - You may return a card from your hand. If you do, score a card from your hand for every
  //     color on your board with [CONCEPT].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    } else {
      $numCardsToScore = 0;
      foreach (Colors::ALL as $color) {
        if (self::getIconCountInStack($color, Icons::CONCEPT) > 0) {
          $numCardsToScore++;
        }
      }
      return [
        'can_pass'      => true,
        'n'             => $numCardsToScore,
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

}