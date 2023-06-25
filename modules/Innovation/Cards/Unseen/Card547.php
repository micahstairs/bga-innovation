<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card547 extends Card
{

  // Camouflage:
  //   - Choose to either junk exactly two top cards of different color and equal value on your
  //     board, then safeguard them, or score exactly two of your secrets of equal value.
  //   - Draw a [7] for each special achievement you have.

  public function initialExecution()
  {
    if (self::getEffectNumber() == 1) {
      self::setMaxSteps(3);
    } else {
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'achievements') as $card) {
        if ($card['age'] == null) {
          self::draw(7);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return ['choose_yes_or_no' => true];
    } else if (self::getCurrentStep() == 2) {
      if (self::getAuxiliaryValue() == 1) {
        $this->game->setAuxiliaryArray(self::getCardIdsWithDuplicateValuesInLocation('board'));
        return [
          'location_from' => 'board',
          'location_to'   => 'junk,safe',
          'card_ids_are_in_auxiliary_array' => true,
        ];
      } else {
        $this->game->setAuxiliaryArray(self::getCardIdsWithDuplicateValuesInLocation('safe'));
        return [
          'location_from' => 'safe',
          'score_keyword' => true,
          'card_ids_are_in_auxiliary_array' => true,
        ];
      }
    } else {
      if (self::getAuxiliaryValue() == 1) {
        return [
          'location_from' => 'board',
          'location_to'   => 'junk,safe',
          'age'           => self::getLastSelectedAge(),
          'color'         => self::getAllColorsOtherThan(self::getLastSelectedColor()),
        ];
      } else {
        return [
          'location_from' => 'safe',
          'score_keyword' => true,
          'age'           => self::getLastSelectedAge(),
        ];
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Junk and safeguard two top cards')
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Score two secrets')
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

  private function getCardIdsWithDuplicateValuesInLocation(string $location): array
  {
    $cardIds = [];
    $cardsByAge = $this->game->getCardsInLocationKeyedByAge(self::getPlayerId(), $location);
    for ($age = 1; $age <= 11; $age++) {
      if (count($cardsByAge[$age]) >= 2) {
        foreach ($cardsByAge[$age] as $card) {
          $cardIds[] = $card['id'];
        }
      }
    }
    return $cardIds;
  }

}