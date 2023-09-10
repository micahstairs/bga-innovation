<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;

class Card371 extends Card
{

  // Barometer
  // - 3rd edition:
  //   - ECHO: Transfer a [5] from your forecast to your hand.
  //   - Draw and foreshadow a card of value two higher than a bonus on any board.
  //   - You may return all cards in your forecast. If any were blue, claim the Destiny achievement.
  // - 4th edition:
  //   - ECHO: Transfer a [5] from your forecast to your hand.
  //   - Draw and foreshadow a card of value two higher than a bonus on any board, if there is one.
  //   - You may return all cards in your forecast. If any are blue, claim the Destiny achievement.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $bonuses = [];
      foreach (self::getPlayerIds() as $playerId) {
        $bonuses = array_merge($bonuses, self::getBonuses($playerId));
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
    } else if (self::hasCards('forecast')) {
      $destinyCard = self::getCard(436);
      $destinyIsAvailable = $destinyCard['owner'] == 0 && $destinyCard['location'] == 'achievements';
      self::setAuxiliaryValue($destinyIsAvailable ? 1 : 0); // Track whether to prompt to reveal a blue card
      self::setAuxiliaryValue2(0); // Tracks whether a blue card was returned
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
    } else if (self::getAuxiliaryValue() === 0) {
      return [
        'can_pass'       => true,
        'n'              => 'all',
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'choose_from' => 'forecast',
        'color'       => [Colors::BLUE],
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'forecast',
        'return_keyword' => true,
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Return all cards in forecast'),
    ]);
  }

  public function handleSpecialChoice(int $choice)
  {
    if (self::isFirstNonDemand()) {
      self::drawAndForeshadow($choice);
    } else {
      self::setMaxSteps(3);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand()) {
      if (self::isFirstInteraction() && self::getAuxiliaryValue() === 1 && self::getNumChosen() === 1) {
        self::setMaxSteps(3);
      } else if (self::isSecondInteraction()) {
        if (self::getNumChosen() === 0) {
          // Prove that there were no blue cards in the forecast
          self::revealForecast();
        } else {
          $this->game->revealCardWithoutMoving(self::getPlayerId(), self::getLastSelectedCard());
          self::setAuxiliaryValue2(1);
        }
      } else if (self::isThirdInteraction() && self::getAuxiliaryValue2() === 1) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), CardIds::DESTINY);
      }
    }
  }

}