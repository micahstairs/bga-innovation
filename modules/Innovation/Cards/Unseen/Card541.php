<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card541 extends AbstractCard
{

  // Attic:
  //   - You may score or safeguard a card from your hand.
  //   - Return a card from your score pile.
  //   - Draw and score a card of value equal to a card in your score pile.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      if (self::countCards(Locations::HAND)) {
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isThirdNonDemand()) {
      if (self::countCards(Locations::SCORE)) {
        self::setMaxSteps(1);
      } else {
        self::drawAndScore(0);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::getFirstInteractionOptions();
    } else if (self::isSecondNonDemand()) {
      return self::youMust()->return()->fromYourScore();
    } else {
      $values = self::getUniqueValuesInLocation(Locations::SCORE);
      return self::youMust()->chooseValue($values);
    }
  }

  private function getFirstInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->choose([0, 1]);
    } else if (self::getAuxiliaryValue() == 1) {
      return self::youMust()->score()->fromYourHand();
    } else {
      return self::youMust()->safeguard()->fromYourHand();
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => clienttranslate('Safeguard a card from your hand'),
      1 => clienttranslate('Score a card from your hand'),
    ]);
  }

  public function handleListChoice(int $choice)
  {
    self::setAuxiliaryValue($choice);
    self::setMaxSteps(2);
  }

  public function handleValueChoice(int $value)
  {
    self::drawAndScore($value);
  }

}