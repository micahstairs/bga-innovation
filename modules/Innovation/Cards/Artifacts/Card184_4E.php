<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card184_4E extends AbstractCard
{
  // The Communist Manifesto (4th edition):
  //   - For each player, draw and reveal a [7]. Transfer one of the drawn cards to each other
  //     player's board. Meld the last, and self-execute it.

  public function initialExecution()
  {
    foreach (self::getPlayerIds() as $playerId) {
      self::drawAndReveal(7);
    }
    $players = $this->game->getOtherActivePlayers(self::getPlayerId());
    self::setAuxiliaryArray($players); // Track indexes (not IDs) of players which are selectable
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->choosePlayer(self::getAuxiliaryArray());
    } else {
      return self::youMust()->fromYourRevealed()->toBoard(self::getAuxiliaryValue());
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId); // Track player that the card is transferring to
  }

  public function handleCardChoice(array $card)
  {
    if (count(self::getAuxiliaryArray()) > 1) {
      // Remove the selected player from the list of options
      $playerIndex = $this->game->playerIdToPlayerIndex(self::getAuxiliaryValue());
      self::removeFromAuxiliaryArray($playerIndex);
      self::setNextStep(1);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::selfExecute(self::meld(self::getRevealedCard()));
    }
  }

}