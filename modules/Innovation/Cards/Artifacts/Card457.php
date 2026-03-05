<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card457 extends AbstractCard
{
  // Tasmanian Tiger
  //   - Choose a card in your score pile. Choose a top card of the same color on any player's
  //     board. Exchange the two cards. You may return two cards from your hand. If you do, repeat
  //     this effect.

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(-1); // Track card chosen from the score pile
      return self::youMust()->chooseCardFrom(Locations::SCORE);
    } else if (self::isSecondInteraction()) {
      if (self::getAuxiliaryValue() === -1) {
        // Skip this interaction if no card was chosen from the score pile
        return [];
      }
      return self::youMust()->chooseCardFrom(Locations::BOARD)->fromAnyPlayer()->withColor(self::getLastSelectedColor());
    } else {
      return self::youMay()->return()->exactly(2)->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(self::getId($card)); // Track card selected from score pile
    } else if (self::isSecondInteraction()) {
      self::transferToBoard(self::getCard(self::getAuxiliaryValue()), self::getOwner($card));
      self::transferToScorePile($card, self::getPlayerId());
    }
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction() && self::getNumChosen() === 2) {
      self::setNextStep(1);
    }
  }

}