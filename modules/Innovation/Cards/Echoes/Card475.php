<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card475 extends AbstractCard
{

  // Robocar
  //   - Choose an opponent. That player chooses a card (unrevealed) in your hand. Meld the chosen
  //     card. If you do, and it is your turn, self-execute the card, then repeat this effect.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choosePlayer(self::getOpponents())->build();
    } else {
      return self::youMust()->meld()->fromHand(self::getAuxiliaryValue())->build();
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId); // Track opponent chosen
  }

  public function handleCardChoice(array $card)
  {
    if (self::isTheirTurn()) {
      if (!self::selfExecute($card)) {
        self::setNextStep(1);
      }
    }
  }

}