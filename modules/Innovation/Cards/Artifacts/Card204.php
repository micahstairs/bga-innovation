<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card204 extends AbstractCard
{
  // Marilyn Diptych
  // - 3rd edition:
  //   - You may score a card from your hand. You may transfer any card from your score pile to
  //     your hand. If you have exactly 25 points, you win.
  // - 4th edition:
  //   - You may score a card from your hand. You may transfer any card from your score pile to
  //     your hand. If you have exactly 25 points, you win.
  //   - Junk an available standard achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'can_pass'      => true,
          'location_from' => Locations::HAND,
          'score_keyword' => true,
        ];
      } else {
        return [
          'can_pass'      => true,
          'location_from' => Locations::SCORE,
          'location_to'   => Locations::HAND,
        ];
      }
    } else {
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand() && self::isSecondInteraction()) {
      if (self::getScore() === 25) {
        self::notifyPlayer(clienttranslate('${You} have exactly 25 points.'));
        self::notifyOthers(clienttranslate('${player_name} has exactly 25 points.'));
        self::win();
      }
    }
  }

}