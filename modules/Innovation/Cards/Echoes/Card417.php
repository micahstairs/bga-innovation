<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;

class Card417 extends AbstractCard
{

  // Helicopter
  // - 3rd edition
  //   - Transfer a top card other than Helicopter from any player's board to its owner's score
  //     pile. You may return a card from your hand which shares an icon with the transferred
  //     card. If you do, repeat this dogma effect.
  // - 4th edition
  //   - Transfer a top card other than Helicopter from any player's board to its owner's score
  //     pile. You may return a card from your hand which shares an icon type with the transferred
  //     card. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseCardFrom('board')->otherThan(CardIds::HELICOPTER)->fromAnyPlayer()->build();
    } else {
      return self::youMay()->return()->onlyCardsInAuxiliaryArray()->fromYourHand()->withoutAutoselection()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::transferToScorePile($card, self::getOwner($card));
      $cardIds = [];
      foreach (self::getCards('hand') as $cardInHand) {
        if (self::hasIconInCommon($cardInHand, $card)) {
          $cardIds[] = self::getId($cardInHand);
        }
      }
      self::setAuxiliaryArray($cardIds);
    } else {
      self::setNextStep(1);
    }
  }

}