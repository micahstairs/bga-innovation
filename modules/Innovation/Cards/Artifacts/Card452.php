<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card452 extends AbstractCard
{
  // Psyche
  //   - Choose a value different from any top card on your board. Score all cards in the deck of
  //     that value. Score all junked cards of that value.

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
    $cards = $this->game->getCardsInLocationKeyedByAge(/*owner=*/0, Locations::DECK, CardTypes::BASE)[$value];
    self::scoreCards($cards);
    $args = ['age' => self::renderValueWithType($value, CardTypes::BASE)];
    self::notifyPlayer(clienttranslate('${You} score all cards in the ${age} deck.'), $args);
    self::notifyOthers(clienttranslate('${player_name} scores all cards in the ${age} deck.'), $args);

    $cards = $this->game->getCardsInLocationKeyedByAge(/*owner=*/0, Locations::JUNK)[$value];
    self::scoreCards($cards);
    $args = ['age' => self::renderValueWithType($value, CardTypes::BASE)];
    self::notifyPlayer(clienttranslate('${You} score all ${age} cards in the junk.'), $args);
    self::notifyOthers(clienttranslate('${player_name} scores all ${age} cards in the junk.'), $args);
  }

}