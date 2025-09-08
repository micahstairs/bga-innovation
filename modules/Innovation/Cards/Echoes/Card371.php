<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card371 extends AbstractCard
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
    } else if (self::isSecondNonDemand() && self::hasCards(Locations::FORECAST)) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return self::youMust()->value(5)->fromYourForecast()->toHand()->build();
    } else if (self::isFirstNonDemand()) {
      // TODO(#472): The value here could be as high as 14 with a visible bonus of 12 which
      // would end the game. This could be presented as a game-ending option like with Evolution.
      return self::youMust()->chooseValue($this->getAuxiliaryArray())->build();
    } else if (self::isFirstInteraction()) {
      return self::youMay()->choose([1])->build();
    } else {
      return self::youMust()->return()->all()->fromYourForecast()->build();
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => clienttranslate('Return all cards in forecast'),
    ]);
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndForeshadow($value);
  }

  public function handleListChoice(int $choice)
  {
    if (self::filterByColor(self::getCards(Locations::FORECAST), Colors::BLUE)) {
      $destinyCard = self::getCard(CardIds::DESTINY);
      $destinyIsAvailable = $destinyCard['owner'] == 0 && $destinyCard['location'] == Locations::ACHIEVEMENTS;
      if ($destinyIsAvailable) {
        self::revealForecast();
        self::setAuxiliaryValue(1); // Remember that we should claim the Destiny achievement
      }
    }

    self::setMaxSteps(2);
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand() && self::isSecondInteraction() && self::getAuxiliaryValue() === 1) {
      self::claim(CardIds::DESTINY);
    }
  }

}