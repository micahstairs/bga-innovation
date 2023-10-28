<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card452 extends AbstractCard
{
  // Psyche
  //   - Choose a value different from any top card on your board. Score all cards in the deck of
  //     that value. Score all cards in the junk of that value.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    $presentValues = array_unique(self::getValues(self::getTopCards()));
    $absentValues = array_diff(range(1, 11), $presentValues);
    return [
      'choose_value' => true,
      'age'          => $absentValues,
    ];
  }

  public function handleValueChoice(int $value) {
    // NOTE: We want to output a message to the game log before scoring the cards in case the Monument achievement is awarded
    $args = ['age' => self::renderValue($value)];
    self::notifyPlayer(clienttranslate('${You} score all ${age} in the base deck and in the junk.'), $args);
    self::notifyOthers(clienttranslate('${player_name} scores all ${age} in the base deck and in the junk.'), $args);

    $deckCards = self::filterByType(self::getCardsKeyedByValue(Locations::DECK)[$value], [CardTypes::BASE]);
    self::scoreCards($deckCards);

    $junkCards = self::getCardsKeyedByValue(Locations::JUNK)[$value];
    self::scoreCards($junkCards);
  }

}