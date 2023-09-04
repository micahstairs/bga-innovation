<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card355 extends Card
{

  // Almanac
  // - 3rd edition:
  //   - ECHO: Draw and foreshadow a [4].
  //   - You may return a card from your forecast with a bonus. If you do, draw and score a card
  //     of value one higher than that bonus.
  // - 4th edition:
  //   - ECHO: Draw and foreshadow an Echoes [4].
  //   - You may return a card from your forecast with a bonus. If you do, draw and score a card
  //     of value one higher than that bonus.
  //   - If Almanac was foreseen, foreshadow all cards in another player's forecast.

  public function initialExecution()
  {
   if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::drawAndForeshadow(4);
      } else {
        self::foreshadow(self::drawType(4, $this->game::ECHOES), [$this, 'transferToHand']);
      }
   } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
   } else if (self::wasForeseen()) {
      self::setMaxSteps(2);
   }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass' => true,
          'location_from' => 'forecast',
          'location_to' => 'revealed,deck',
          'with_bonus' => true,
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'choose_player' => true,
        'players' => $this->game->getOtherActivePlayers(self::getPlayerId()),
      ];
    } else {
      return [
        'n' => 'all',
        'owner_from' => self::getAuxiliaryValue(),
        'location_from' => 'forecast',
        'location_to' => 'forecast',
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(self::getBonusIcon($card) + 1); 
    }
  }

  public function handleSpecialChoice(int $playerId)
  {
    self::setAuxiliaryValue($playerId);
  }

}