<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;


class Card92 extends AbstractCard
{
  // Suburbia:
  // - 3rd edition:
  //   - You may tuck any number of cards from your hand. Draw and score a [1] for each card you tucked.
  // - 4th edition:
  //   - You may tuck any number of cards from your hand. Draw and score a [1] for each card you tuck.
  //   - You may junk all cards in the [9] deck.

  public function initialExecution()
  {
    if (self::isFirstNonDemand() || self::getBaseDeckCount(9) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->tuck()->anyNumber()->fromYourHand();
    } else {
      return self::youMay()->choose([9]);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      $numCardsToTuck = self::getNumChosen();
      for ($i = 0; $i < $numCardsToTuck; $i++) {
        self::drawAndScore(1);
      }
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      9 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(9, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    self::junkBaseDeck(9);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFourthEdition() && self::getBaseDeckCount(9) > 0) {
      return true;
    }
    return self::hasCards(Locations::HAND);
  }

}