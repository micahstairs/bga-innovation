<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\Icons;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card31 extends AbstractCard
{
  // Machinery:
  // - 3rd edition:
  //   - I DEMAND you exchange all the cards in your hand with all the highest cards in my hand!
  //   - Score a card from your hand with a [AUTHORITY]. You may splay your red cards left.
  // - 4th edition:
  //   - I DEMAND you exchange all the cards in your hand with all the highest cards in my hand!
  //   - Score a card from your hand with [AUTHORITY].
  //   - You may splay your red cards left.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $playerCardIds = $this->game->getIdsOfCardsInLocation(self::getPlayerId(), Locations::HAND);
      $launcherCardIds = $this->game->getIdsOfCardsInLocation($this->getLauncherId(), Locations::HAND);
      foreach ($playerCardIds as $cardId) {
        self::transferToHand(self::getCard($cardId), $this->getLauncherId());
      }
      foreach ($launcherCardIds as $cardId) {
        self::transferToHand(self::getCard($cardId), self::getPlayerId());
      }
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      return [
        'location_from'    => Locations::HAND,
        'location_to'      => Locations::REVEALED_THEN_SCORE,
        'score_keyword'    => true,
        'with_icon'        => Icons::AUTHORITY,
        'reveal_if_unable' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'color'           => [Colors::RED],
        'splay_direction' => Directions::LEFT,
      ];
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(self::getPlayerId(), Locations::HAND) || self::hasCards(self::getLauncherId(), Locations::HAND);
  }

}