<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;

class Card440 extends Card
{

  // Climatology:
  //   - I DEMAND you return two top cards from your board each with the icon of my choice other
  //     than [HEALTH]!
  //   - Return a top card from your board. Return all cards in your score pile of equal or higher
  //     value than the top card.

  public function initialExecution()
  {
    self::setMaxSteps(self::isDemand() ? 3 : 2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::getCurrentStep() == 1) {
        return [
          'player_id'        => self::getLauncherId(),
          'choose_icon_type' => true,
          'icon'             => [1, 3, 4, 5, 6, 7],
        ];
      } else {
        return [
          'location_from' => 'board',
          'location_to'   => 'deck',
          'with_icon'     => self::getAuxiliaryValue(),
        ];
      }
    }
    if (self::getCurrentStep() == 1) {
      return [
        'location_from' => 'board',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'location_from' => 'score',
        'location_to'   => 'deck',
        'age_min'       => self::getAuxiliaryValue(),
        'n'             => 'all',
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isNonDemand() && self::getCurrentStep() == 1) {
      $minAgeToReturn = 0;
      if (self::getNumChosen() > 0) {
        $minAgeToReturn = self::getLastSelectedAge();
      }
      self::setAuxiliaryValue($minAgeToReturn);
    }
  }

  public function handleSpecialChoice(int $chosenIcon): void
  {
    $this->notifications->notifyIconChoice($chosenIcon, self::getPlayerId());
    self::setAuxiliaryValue($chosenIcon);
  }
}