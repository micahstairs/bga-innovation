<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card126 extends AbstractCard
{

  // Rosetta Stone
  //   - Choose a set. Draw two [2] from that set. Meld one and transfer the other to an
  //     opponent's board.

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseType();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->meld()->fromYourHand()->onlyCardsInAuxiliaryArray();
    } else {
      return self::youMust()->choosePlayer(self::getOpponents());
    }
  }

  public function handleTypeChoice(int $type)
  {
    $card1 = self::drawType(2, $type);
    $card2 = self::drawType(2, $type);
    self::setAuxiliaryArray([self::getId($card1), self::getId($card2)]);
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::transferToBoard(self::getCard(self::getAuxiliaryArray()[0]), $playerId);
  }

  public function handleCardChoice(array $card)
  {
    self::removeFromAuxiliaryArray(self::getId($card));
  }

}