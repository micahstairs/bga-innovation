<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card487 extends AbstractCard
{
  // Rumor
  //   - Return a card from your score pile. If you do, draw a card of value one higher than the card you return.
  //   - Transfer a card from your hand to the hand of the player on your left.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->fromYourScore();
    } else {
      $playerOnLeft = $this->game->getActivePlayerIdsInTurnOrderStartingToLeftOfActingPlayer()[0];
      return self::youMust()->fromYourHand()->toPlayer($playerOnLeft);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::draw(self::getValue($card) + 1);
    }
  }

}