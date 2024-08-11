<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card32 extends AbstractCard
{
  // Medicine:
  // - 3rd edition:
  //   - I DEMAND you exchange the highest card in your score pile with the lowest card in my score pile!
  // - 4th edition:
  //   - I DEMAND you exchange the highest card in your score pile with the lowest card in my score pile!
  //   - Junk an available achievement of value [3] or [4].

  public function initialExecution()
  {
    if (self::isDemand()) {

      self::setAuxiliaryValue(-1); // Track which card the launcher chose
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'player_id' => self::getLauncherId(),
          'location'  => Locations::SCORE,
          'age'       => self::getMinValue(self::getCards(Locations::SCORE, self::getLauncherId())),
        ];
      } else {
        return [
          'player_id' => self::getPlayerId(),
          'location'  => Locations::SCORE,
          'age'       => self::getMaxValue(self::getCards(Locations::SCORE, self::getPlayerId())),
        ];
      }
    } else {
      return [
        'location'     => Locations::AVAILABLE_ACHIEVEMENTS,
        'age_min'      => 3,
        'age_max'      => 4,
        'junk_keyword' => true,
      ];
    }
  }

  public function executeCardTransfer(array $card): bool
  {
    if (self::isDemand() && self::isFirstInteraction()) {
      // Delay the transfer so that the players cannot choose the same card
      self::setAuxiliaryValue($card['id']);
    }
    return false;
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::isSecondInteraction()) {
      $this->game->gamestate->changeActivePlayer(self::getLauncherId());
      self::transferToScorePile(self::getCard(self::getAuxiliaryValue()));
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(self::getPlayerId(), Locations::SCORE) || self::hasCards(self::getLauncherId(), Locations::SCORE);
  }

}