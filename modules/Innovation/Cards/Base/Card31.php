<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\Icons;
use Innovation\Enums\Colors;

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
      $playerCards = self::getHighestCards(Locations::HAND, self::getPlayerId());
      $launcherCards = self::getHighestCards(Locations::HAND, self::getLauncherId());
      foreach ($playerCards as $card) {
        self::transferToHand($card, self::getLauncherId());
      }
      foreach ($launcherCards as $card) {
        self::transferToHand($card, self::getPlayerId());
      }
    } else {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand() && self::isFirstInteraction()) {
      return self::youMust()->revealAndScore()->withIcon(Icons::AUTHORITY)->fromYourHand()->revealIfUnable()->build();
    } else {
      return self::youMay()->splayLeft()->withColor(Colors::RED)->build();
    }
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND, self::getPlayerId()) || self::hasCards(Locations::HAND, self::getLauncherId());
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::canSplayLeft(Colors::RED)) {
      return true;
    }
    if (self::isLauncher()) {
      return count(self::filterByIcon(self::getCards(Locations::HAND), Icons::AUTHORITY)) > 0;
    } else {
      return self::hasCards(Locations::HAND);
    }
  }

}