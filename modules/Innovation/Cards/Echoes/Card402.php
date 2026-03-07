<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card402 extends AbstractCard
{

  // Fertilizer
  // - 3rd edition
  //   - You may return a card from your hand. If you do, transfer all cards from all score piles to your hand of value equal to the returned card.
  //   - Draw and foreshadow a card of any value.
  // - 4th edition
  //   - You may return a card from your hand. If you do, transfer all cards from all score piles to your hand of value equal to the returned card.
  //   - Draw and foreshadow a card of value equal to the number of cards in your hand.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::isFirstOrThirdEdition()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndForeshadow(self::countCards(Locations::HAND));
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->return()->fromYourHand();
    } else {
      return self::youMust()->chooseValue();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      $value = self::getValue($card);
      foreach (self::getPlayerIds() as $playerId) {
        foreach (self::getCardsKeyedByValue(Locations::SCORE, $playerId)[$value] as $scoreCard) {
          self::transferToHand($scoreCard);
        }
      }
    }
  }

  public function handleValueChoice($value)
  {
    self::drawAndForeshadow($value);
  }

}