<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card542 extends AbstractCard
{

  // Sabotage:
  //   - I DEMAND you draw a [6]! Reveal the cards in your hand! Return the card of my choice from
  //     your hand! Tuck your top card and all cards from your score pile of the same color as the
  //     returned card!

  public function initialExecution()
  {
    self::draw(6);
    foreach (self::getCards('hand') as $card) {
      self::reveal($card);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->fromYourRevealed()->ofMyChoice()->build();
    } else {
      return self::youMust()->tuck()->all()->withColor(self::getLastSelectedColor())->fromYourScore()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0 && self::isFirstInteraction()) {
      $this->game->gamestate->changeActivePlayer(self::getPlayerId());
      self::tuck(self::getTopCardOfColor(self::getLastSelectedColor()));
      foreach (self::getCards('revealed') as $card) {
        self::transferToHand($card);
      }
    }
    if (self::isSecondInteraction() && self::countCards(Locations::SCORE) > 0) {
      self::revealScorePile();
    }
  }

}