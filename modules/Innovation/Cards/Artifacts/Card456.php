<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card456 extends AbstractCard
{
  // What Does The Fox Say
  //   - Draw two [11]s. Meld one of them, then meld the other and if it is your turn, super-execute
  //     it, otherwise self-execute it.

  public function initialExecution()
  {
    $card1 = self::draw(11);
    $card2 = self::draw(11);
    self::setAuxiliaryArray([self::getId($card1), self::getId($card2)]);
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->meld()->onlyCardsInAuxiliaryArray()->fromYourHand();
  }

  public function handleCardChoice(array $card)
  {
    self::removeFromAuxiliaryArray(self::getId($card));
    $other_card_id = self::getAuxiliaryArray()[0];
    $other_card = self::meld(self::getCard($other_card_id));
    if (self::isTheirTurn()) {
      self::superExecute($other_card);
    } else {
      self::selfExecute($other_card);
    }
  }

}