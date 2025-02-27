<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card89 extends AbstractCard
{
  // Collaboration:
  // - 3rd edition:
  //   - I DEMAND you draw two [9] and reveal them! Transfer the card of my choice to my board, and
  //     meld the other!
  //   - If you have ten or more green cards on your board, you win.
  // - 4th edition:
  //   - I DEMAND you draw two [9] and reveal them! Transfer the card of my choice to my board, and
  //     meld the other!
  //   - If you have at least ten green cards on your board, you win.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::drawAndReveal(9);
      self::drawAndReveal(9);
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (count(self::getStack(Colors::GREEN)) >= 10) {
        self::win();
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->fromYourRevealed()->toMyBoard()->ofMyChoice()->build();
  }

  public function handleCardChoice(array $card)
  {
    // Meld the other card
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    foreach (self::getCards(Locations::REVEALED) as $revealedCard) {
      self::meld($revealedCard);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::getStack(Colors::GREEN)) >= 10;
  }

}