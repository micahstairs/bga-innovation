<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card50 extends AbstractCard
{
  // Measurement:
  // - 3rd edition:
  //   - You may return a card from your hand. If you do, splay that color of your cards right,
  //     and draw a card of value equal to the number of cards of that color on your board.
  // - 4th edition:
  //   - You may return a card from your hand. If you do, splay your cards of that card's color right,
  //     and draw a card of value equal to the number of cards of that color on your board.

  public function getInteractionOptions(): array
  {
    return self::youMay()->revealAndReturn()->fromYourHand()->build();
  }

  public function handleCardChoice(array $card)
  {
    $color = self::getColor($card);
    self::splayRight($color);
    self::draw(self::countCardsKeyedByColor(Locations::BOARD)[$color]);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}