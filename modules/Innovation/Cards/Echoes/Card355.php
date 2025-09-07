<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;

class Card355 extends AbstractCard
{

  // Almanac
  // - 3rd edition:
  //   - ECHO: Draw and foreshadow a [4].
  //   - You may return a card from your forecast with a bonus. If you do, draw and score a card
  //     of value one higher than that bonus.
  // - 4th edition:
  //   - ECHO: Draw and foreshadow an Echoes [4].
  //   - You may return a card from your forecast with a bonus. If you do, draw and score a card
  //     of value one higher than that bonus.
  //   - If Almanac was foreseen, foreshadow all cards in another player's forecast.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(4);
      } else {
        self::foreshadow(self::drawType(4, CardTypes::ECHOES));
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::wasForeseen()) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->revealAndReturn()->withBonus()->fromYourForecast()->build();
    } else if (self::isFirstInteraction()) {
      return self::youMust()->choosePlayer(self::getOtherPlayers())->build();
    } else {
      return self::youMust()->foreshadow()->all()->fromForecast(self::getAuxiliaryValue())->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(self::getBonusIcon($card) + 1);
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId);
  }

}