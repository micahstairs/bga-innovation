<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card162_3E extends AbstractCard
{
  // The Daily Courant (3rd edition):
  //   - Draw a card of any value, then place it on top of the draw pile of its age. You may
  //     execute the effects of one of your other top cards as if they were on this card. Do not
  //     share them.

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseValue();
    } else if (self::isSecondInteraction()) {
      // Autoselection is disabled to give the player the chance to read the card
      return self::youMust()->topDeck()->fromYourHand()->onlyCardsInAuxiliaryArray()->withoutAutoselection();
    } else {
      $excludedCardId = $this->game->getCurrentNestedCardState()['executing_as_if_on_card_id'];
      return self::youMay()->chooseCardFrom(Locations::BOARD)->otherThan($excludedCardId);
    }
  }

  public function handleValueChoice(int $value)
  {
    $card = self::draw($value);
    self::setAuxiliaryArray([self::getId($card)]);
  }

  public function handleCardChoice(array $card)
  {
    if (self::isThirdInteraction()) {
      self::superExecute($card);
    }
  }

}