<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card513 extends AbstractCard
{
  // Masquerade
  //   - Safeguard an available achievement of value equal to the number of cards in your hand. If
  //     you do, return all the highest cards from your hand. If you return a [4], claim the
  //     Anonymity achievement.
  //   - You may splay your purple cards left.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'location_from'     => Locations::AVAILABLE_ACHIEVEMENTS,
          'safeguard_keyword' => true,
          'age'               => self::countCards(Locations::HAND),
        ];
      } else {
        return [
          'n'              => 'all',
          'location_from'  => Locations::HAND,
          'return_keyword' => true,
          'age'            => self::getMaxValueInLocation(Locations::HAND),
        ];
      }
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [Colors::PURPLE],
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        self::setMaxSteps(2);
      } else if (self::isSecondInteraction() && self::getValue($card) === 4) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), CardIds::ANONYMITY);
      }
    }
  }

}