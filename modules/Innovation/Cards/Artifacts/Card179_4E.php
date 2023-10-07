<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card179_4E extends AbstractCard
{
  // International Prototype Metre Bar (4th edition):
  //   - Choose a value. Draw and reveal three cards of that value. Splay up the colors of the
  //     cards. If the number of cards of each of those colors on your board is equal to that
  //     value, you win. Otherwise, return the drawn cards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_value' => true];
    } else {
      return [
        'n' => 'all',
        'location_from' => Locations::REVEALED,
        'return_keyword' => true,
      ];
    }
  }

  public function handleValueChoice(int $value) {
    self::notifyValueChoice($value);

    $card1 = self::drawAndReveal($value);
    $card2 = self::drawAndReveal($value);
    $card3 = self::drawAndReveal($value);

    self::splayUp($card1['color']);
    self::splayUp($card2['color']);
    self::splayUp($card3['color']);

    $count1 = self::countVisibleCardsInStack($card1['color']);
    $count2 = self::countVisibleCardsInStack($card2['color']);
    $count3 = self::countVisibleCardsInStack($card3['color']);

    if ($count1 === $value && $count2 === $value && $count3 === $value) {
      self::win();
    } else {
      self::setMaxSteps(2);
    }
  }

}