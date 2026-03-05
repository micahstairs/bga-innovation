<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card160 extends AbstractCard
{
  // Hudson's Bay Company Archives
  // - 3rd edition:
  //   - Score the bottom card of every color on your board. Meld a card from your score pile.
  //     Splay right the color of the melded card.
  // - 4th edition:
  //   - Score the bottom card of every color on your board. Meld a card from your score pile.
  //     Splay right the color of the melded card. Junk all cards in the deck of value equal to
  //     the melded card.

  public function initialExecution()
  {
    foreach (Colors::ALL as $color) {
      self::score(self::getBottomCardOfColor($color));
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->meld()->fromYourScore();
  }

  public function handleCardChoice(array $card)
  {
    self::splayRight(self::getColor($card));
    if (self::isFourthEdition()) {
      self::junkBaseDeck(self::getFaceupValue($card));
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::BOARD) || self::hasCards(Locations::SCORE);
  }

}