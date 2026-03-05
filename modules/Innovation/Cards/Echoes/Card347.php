<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;

class Card347 extends AbstractCard
{

  // Crossbow
  // - 3rd edition:
  //   - I DEMAND you transfer a card with a bonus from your hand to my score pile!
  //   - Transfer a card from your hand to any other player's board.
  // - 4th edition:
  //   - I DEMAND you transfer an expansion card from your hand to my score pile!
  //   - Transfer a card from your hand to any opponent's board.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      if (self::isFirstOrThirdEdition()) {
        return self::youMust()->withBonus()->fromYourHand()->toMyScore();
      } else {
        $types = CardTypes::getAllTypesOtherThan(CardTypes::BASE);
        return self::youMust()->withTypes($types)->fromYourHand()->toMyScore();
      }
    } else if (self::isFirstInteraction()) {
      $players = self::isFirstOrThirdEdition() ? $this->game->getOtherActivePlayers(self::getPlayerId()) : $this->game->getActiveOpponents(self::getPlayerId());
      return self::youMust()->choosePlayer($players);
    } else {
      return self::youMust()->fromMyHand()->toBoard(self::getAuxiliaryValue());
    }
  }

  public function handlePlayerChoice(int $otherPlayerId)
  {
    self::setAuxiliaryValue($otherPlayerId);
  }

}