<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card173 extends Card
{
  // Moonlight Sonata
  // - 3rd edition:
  //   - Choose a color on your board having the highest top card. Meld the bottom card on your
  //     board of that color. Claim an achievement, ignoring eligibility.
  // - 4th edition:
  //   - Choose a color on your board having the highest top card. Meld your bottom card of that color.
  //   - Claim a standard achievement, ignoring eligibility. Junk an available standard achievement.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      self::setMaxSteps(2);
      if (self::countCards(Locations::BOARD) === 0) {
        self::setNextStep(2);
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      $colors = [];
      $topCards = self::getTopCards();
      $maxValue = self::getMaxValue($topCards);
      foreach ($topCards as $card) {
        if ($card['faceup_age'] == $maxValue) {
          $colors[] = $card['color'];
        }
      }
      return [
        'choose_color' => true,
        'color'        => $colors,
      ];
    } else if (self::isFirstNonDemand()) {
      return ['achieve_keyword' => true];
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
      ];
    }
  }

  public function handleColorChoice(int $color)
  {
    self::meld(self::getBottomCardOfColor($color));
  }

}