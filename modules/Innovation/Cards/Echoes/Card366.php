<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card366 extends AbstractCard
{

  // Telescope
  // - 3rd edition:
  //   - ECHO: Draw and foreshadow a [5].
  //   - You may place a card from your forecast on top of its deck. If you do, achieve a card from
  //     your forecast if you meet the requirements to do so.
  // - 4th edition:
  //   - ECHO: Draw and foreshadow an Echoes [5].
  //   - You may place a card from your forecast on top of its deck. If you do, exchange all cards
  //     in your forecast with an equal number of available standard achievements, and junk all the
  //     cards in the [5] deck.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(5);
      } else {
        self::foreshadow(self::drawType(5, CardTypes::ECHOES));
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->topDeck()->fromYourForecast();
    } else if (self::isFirstOrThirdEdition()) {
      return self::youMust()->achieveIfEligible()->fromYourForecast();
    } else {
      self::setAuxiliaryArray(self::getAvailableStandardAchievementIds());
      $forecastCards = self::getCards(Locations::FORECAST);
      foreach ($forecastCards as $card) {
        $this->game->transferCardFromTo($card, 0, Locations::ACHIEVEMENTS);
      }
      $numCards = count($forecastCards);
      return self::youMay()->exactly($numCards)->onlyCardsInAuxiliaryArray()->fromAvailableAchievements()->toForecast();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() > 0) {
      self::setMaxSteps(2);
    } else if (self::isFourthEdition() && self::isSecondInteraction()) {
      self::junkBaseDeck(5);
    }
  }

  public function handleAbortedInteraction()
  {
    // Still junk the cards in the [5] deck even if there were no cards to exchange
    if (self::isFourthEdition() && self::isSecondInteraction()) {
      self::junkBaseDeck(5);
    }
  }

  private function getAvailableStandardAchievementIds(): array
  {
    $cardIds = [];
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
      if (self::isValuedCard($card)) {
        $cardIds[] = self::getId($card);
      }
    }
    return $cardIds;
  }

}