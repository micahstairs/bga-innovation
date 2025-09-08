<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card146 extends AbstractCard
{

  // Delft Pocket Telescope
  // - 3rd edition:
  //   - Return a card from your score pile. If you do, draw a [5] and a [6], then reveal one of
  //     the drawn cards that has an icon in common with the returned card. If you cannot, return
  //     the drawn cards and repeat this effect.
  // - 4th edition:
  //   - Return a card from your score pile. If you do, draw a [5] and a [6], then reveal one of
  //     the drawn cards that has a symbol in common with the returned card. If you cannot, return
  //     the drawn cards and repeat this effect.


  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->revealAndReturn()->fromYourScore()->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->return()->exactly(2)->fromYourHand()->onlyCardsInAuxiliaryArray()->build();
    } else {
      // Using autoselection here would always reveals hidden info
      return self::youMust()->revealAndPlaceInHand()->fromYourHand()->onlyCardsInAuxiliaryArray()->withoutAutoselection()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      $card1 = self::draw(5);
      $card2 = self::draw(6);

      $card1Matches = self::hasIconInCommon($card, $card1);
      $card2Matches = self::hasIconInCommon($card, $card2);

      if (!$card1Matches && !$card2Matches) {
        $this->game->revealCardWithoutMoving(self::getPlayerId(), $card1);
        $this->game->revealCardWithoutMoving(self::getPlayerId(), $card2);
        self::notifyAll(clienttranslate('Neither card has a icon in common with the returned card.'));
        self::setAuxiliaryArray([self::getId($card1), self::getId($card2)]);
        self::setMaxSteps(2);
      } else {
        self::setMaxSteps(3);
        self::setNextStep(3);
        $cardIds = [];
        if ($card1Matches) {
          $cardIds[] = self::getId($card1);
        }
        if ($card2Matches) {
          $cardIds[] = self::getId($card2);
        }
        self::setAuxiliaryArray($cardIds);
      }
    } else if (self::isSecondInteraction()) {
      self::setNextStep(1);
      self::setMaxSteps(1);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }
}
