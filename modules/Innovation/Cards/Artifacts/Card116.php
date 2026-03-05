<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card116 extends AbstractCard
{

  // Priest-King
  // - 3rd edition:
  //   - Score a card from your hand. If you have a top card matching its color, execute each of
  //     the top card's non-demand dogma effects. Do not share them.
  //   - Claim an achievement, if eligible.
  // - 4th edition:
  //   - Score a card from your hand. If you have a top card matching its color, super-execute
  //     that top card it is your turn, otherwise self-execute it.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->revealAndScore()->fromYourHand();
    } else {
      return self::youMust()->achieveIfEligible();
    }
  }

  public function handleCardChoice(array $card)
  {
    $topCard = self::getTopCardOfColor(self::getColor($card));
    if (self::isFourthEdition() && self::isTheirTurn()) {
      self::superExecute($topCard);
    } else {
      self::selfExecute($topCard);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFirstOrThirdEdition() && count($this->game->getClaimableStandardAchievementValues(self::getPlayerId())) > 0) {
      return true;
    }
    return self::hasCards(Locations::HAND);
  }

}