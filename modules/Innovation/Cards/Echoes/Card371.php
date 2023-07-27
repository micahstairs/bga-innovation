<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card371 extends Card
{

  // Barometer
  // - 3rd edition:
  //   - ECHO: Transfer a [5] from your forecast to your hand.
  //   - Draw and foreshadow a card of value two higher than a bonus on any board.
  //   - You may reveal and return all cards in your forecast. If any were blue, claim the Destiny achievement.
  // - 4th edition:
  //   - ECHO: Transfer a [5] from your forecast to your hand.
  //   - Draw and foreshadow a card of value two higher than a bonus on any board, if there is one.
  //   - You may return all cards in your forecast. If any are blue, claim the Destiny achievement.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $bonuses = $this->game->getVisibleBonusesOnBoard(self::getPlayerId());
      if (self::isFirstOrThirdEdition()) {
        foreach (self::getOtherPlayerIds() as $playerId) {
          $bonuses = array_merge($bonuses, $this->game->getVisibleBonusesOnBoard($playerId));
        }
      }
      if ($bonuses) {
        $valuesToDraw = [];
        foreach ($bonuses as $bonus) {
          $valuesToDraw[] = $bonus + 2;
        }
        self::setMaxSteps(1);
        self::setAuxiliaryArray($valuesToDraw);
      } else if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(2);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'forecast',
        'location_to'   => 'hand',
        'age'           => 5,
      ];
    } else if (self::isFirstNonDemand()) {
      // TODO(#472): The value here could be as high as 14 with a visible bonus of 12 which
      // would end the game. This could be presented as a game-ending option like with Evolution.
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
    } else {
      if (self::getCurrentStep() === 1) {
        self::setAuxiliaryArray([]); // Repurpose auxiliary array to track IDs of returned blue cards
        return [
          'can_pass'      => true,
          'n'             => 'all',
          'location_from' => 'forecast',
          'location_to'   => self::isFirstOrThirdEdition() ? 'revealed,deck' : 'deck',
        ];
      } else {
        return ['choices' => range(1, count(self::getAuxiliaryArray()))];
      }
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondNonDemand() && $card['color'] == $this->game::BLUE) {
      if (self::isFirstOrThirdEdition()) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), 436);
      } else {
        self::setAuxiliaryArray(array_merge(self::getAuxiliaryArray(), [$card['id']]));
      }
    }
  }

  public function handleSpecialChoice(int $value)
  {
    if (self::isFirstNonDemand()) {
      self::drawAndForeshadow($value);
    } else {
      $this->game->revealCardWithoutMoving(self::getPlayerId(), self::getCard($value), false);
      $this->game->claimSpecialAchievement(self::getPlayerId(), 436);
    }
  }

  public function afterInteraction(array $card)
  {
    if (self::isFourthEdition() && self::isSecondNonDemand()) {
      self::setMaxSteps(2);
    }
  }

}