<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card518 extends AbstractCard
{

  // Spanish Inquisition:
  //   - I DEMAND you return all but the highest cards from your hand and all but the highest cards
  //     from your score pile!
  //   - If Spanish Inquisition is a top card on your board, return all red cards from your board.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $cardIds = [];
      $maxValueInHand = self::getMaxValueInLocation(Locations::HAND);
      foreach (self::getCards(Locations::HAND) as $card) {
        if (self::getValue($card) < $maxValueInHand) {
          $cardIds[] = self::getId($card);
        }
      }
      $maxValueInScore = self::getMaxValueInLocation(Locations::SCORE);
      foreach (self::getCards(Locations::SCORE) as $card) {
        if (self::getValue($card) < $maxValueInScore) {
          $cardIds[] = self::getId($card);
        }
      }
      if (count($cardIds) > 0) {
        self::setMaxSteps(2);
        self::setAuxiliaryArray($cardIds);
      }
    } else {
      $topCard = self::getTopCardOfColor(Colors::RED);
      if ($topCard && self::getId($topCard) == CardIds::SPANISH_INQUISITION) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->return()->all()->fromYourHandOrScore()->onlyCardsInAuxiliaryArray();
    } else {
      return self::youMust()->return()->withColor(Colors::RED)->fromAnywhereInStack();
    }
  }

}