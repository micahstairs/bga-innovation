<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card502 extends AbstractCard
{
  // Fingerprints
  //   - You may splay your red or yellow cards left.
  //   - Safeguard an available achievement of value equal to the number of splayed colors on your
  //     board. Transfer a card of that value in your hand to any board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      // TODO(FIGURES): Handle case where there are cards of value 0.
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::LEFT,
        'color'           => [Colors::RED, Colors::YELLOW],
      ];
    } else if (self::isFirstInteraction()) {
      $numSplayedColors = 0;
      foreach (self::getTopCards() as $card) {
        if ($card['splay_direction'] != Directions::UNSPLAYED) {
          $numSplayedColors++;
        }
      }
      return [
        'safeguard_keyword' => true,
        'age'               => $numSplayedColors,
      ];
    } else if (self::isSecondInteraction()) {
      return ['choose_player' => true];
    } else {
      return [
        'location_from' => Locations::HAND,
        'owner_to'      => self::getAuxiliaryValue2(),
        'location_to'   => Locations::BOARD,
        'age'           => self::getAuxiliaryValue(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondNonDemand() && self::isFirstInteraction()) {
      // Don't bother picking a player if there are no cards of that value in hand
      $value = self::getValue($card);
      if (self::countCardsKeyedByValue(Locations::HAND)[$value] > 0) {
        self::setAuxiliaryValue($value); // Track value to transfer
        self::setMaxSteps(3);
      }
    }
  }

  protected function getPromptForPlayerChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose a player whose board you will transfer a card to'),
      "message_for_others" => clienttranslate('${player_name} must choose a player'),
    ];
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue2($playerId); // Track player to transfer the card to
  }

}