<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card455 extends AbstractCard
{
  // Sanskrit
  //   - Junk all cards in all score piles. If you don't, for each player, choose the highest top
  //     card on their board, then for each other different color, transfer their top card of that
  //     color to their score pile.

  public function initialExecution()
  {
    $cards = [];
    foreach (self::getPlayerIds() as $playerId) {
      $cards = array_merge($cards, self::getCards(Locations::SCORE, $playerId));
    }
    if (self::junkCards($cards)) {
      self::notifyPlayer(clienttranslate('${You} junked all cards from all score piles.'));
      self::notifyOthers(clienttranslate('${player_name} junked all cards from all score piles.'));
    } else {
      self::setAuxiliaryArray(self::getPlayerIds()); // Track players who still must be chosen
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      $players = [];
      foreach (self::getAuxiliaryArray() as $playerId) {
        $players[] = $this->game->playerIdToPlayerIndex($playerId);
      }
      return self::youMust()->choosePlayer($players)->build();
    } else {
      $playerId = self::getAuxiliaryValue();
      $value = self::getMaxValue(self::getTopCards($playerId));
      return self::youMust()->value($value)->fromBoard($playerId)->build();
    }
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::removeFromAuxiliaryArray($playerId);
    self::setAuxiliaryValue($playerId); // Track chosen player
  }

  public function handleCardChoice(array $card)
  {
    $playerId = self::getAuxiliaryValue();
    foreach (self::getTopCards($playerId) as $topCard) {
      if (self::getColor($topCard) != self::getColor($card)) {
        self::transferToScorePile($topCard, $playerId);
      }
    }

    if (self::getAuxiliaryArray()) {
      self::setNextStep(1);
    }
  }

}