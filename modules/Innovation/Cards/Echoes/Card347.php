<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
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

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      $options = [
        'location_from' => 'hand',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'score',
      ];
      if (self::isFirstOrThirdEdition()) {
        $options['with_bonus'] = true;
      } else {
        $options['type'] = CardTypes::getAllTypesOtherThan(CardTypes::BASE);
      }
      return $options;
    } else if (self::isFirstInteraction()) {
      $players = self::isFirstOrThirdEdition() ? $this->game->getOtherActivePlayers(self::getPlayerId()) : $this->game->getActiveOpponents(self::getPlayerId());
      return [
        'choose_player' => true,
        'players'       => $players,
      ];
    } else {
      return [
        'location_from' => 'hand',
        'owner_to'      => self::getAuxiliaryValue(),
        'location_to'   => 'board',
      ];
    }
  }

  public function handlePlayerChoice(int $otherPlayerId)
  {
    self::setAuxiliaryValue($otherPlayerId);
  }

}