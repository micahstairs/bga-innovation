<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\Colors;

class Card544 extends AbstractCard
{

  // Triad:
  //   - If you have at least three cards in your hand, return a card from your hand and splay the
  //     color of the returned card right, tuck a card from your hand, and score a card from your hand.

  public function initialExecution()
  {
    if (self::countCards(Locations::HAND) >= 3) {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      // If it's possible for there to be an effective right splay, then reveal the card before returning it
      $mustReveal = false;
      foreach (Colors::ALL as $color) {
        if (self::canSplay($color) && !self::isSplayedRight($color)) {
          $mustReveal = true;
        }
      }
      return [
        'location_from' => Locations::HAND,
        'location_to'   => $mustReveal ? Locations::REVEALED_THEN_DECK : Locations::DECK,
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'location_from' => Locations::HAND,
        'tuck_keyword'  => true,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'score_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::splayRight(self::getLastSelectedColor());
    }
  }

}