<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card91 extends AbstractCard
{
  // Ecology:
  // - 3rd edition:
  //   - You may return a card from your hand. If you do, score a card from your hand and draw two [10].
  // - 4th edition:
  //   - You may return a card from your hand. If you do, score a card from your hand and draw two [10].
  //   - You may junk all cards in the [10] deck.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMay()->return()->fromYourHand();
      } else {
        return self::youMay()->score()->fromYourHand();
      }
    } else {
      return self::youMay()->choose([1]);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand() && self::isSecondInteraction()) {
      self::draw(10);
      self::draw(10);
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(10, CardTypes::BASE)],
    ]);
  }

  public function handleListChoice(int $choice): void
  {
    self::junkBaseDeck(10);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFourthEdition() && self::getBaseDeckCount(10) > 0) {
      return true;
    }
    return self::hasCards(Locations::HAND);
  }

}