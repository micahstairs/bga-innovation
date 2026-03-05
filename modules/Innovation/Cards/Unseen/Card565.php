<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card565 extends AbstractCard
{

  // Consulting:
  //   - Choose an opponent. Draw and meld two [10]. Self-execute the top card on your board of
  //     that player's choice.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choosePlayer(self::getOpponents());
    } else {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->ofPlayersChoice(self::getAuxiliaryValue());
    }
  }

  public function handleCardChoice(array $card)
  {
    self::selfExecute($card);
  }

  public function handlePlayerChoice(int $playerId): void
  {
    self::notifyPlayerChoice($playerId);
    self::setAuxiliaryValue($playerId);
    self::drawAndMeld(10);
    self::drawAndMeld(10);
  }

}