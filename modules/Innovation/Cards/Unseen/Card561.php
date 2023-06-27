<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card561 extends Card
{

  // Jackalope:
  //   - I DEMAND you transfer the highest card on your board without a [EFFICIENCY] to my board!
  //     If you do, unsplay the transferred card's color on your board!
  //   - Unsplay the color on your board with the most visible cards.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      $this->game->setAuxiliaryArray(self::getHighestCardIdsWithoutEffiencyOnBoard());
      return [
        'location_from'                   => 'board',
        'owner_from'                      => self::getPlayerId(),
        'location_to'                     => 'board',
        'owner_to'                        => self::getLauncherId(),
        'without_icon'                    => $this->game::EFFICIENCY,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'splay_direction' => $this->game::UNSPLAYED,
        'color'           => self::getColorsWithMostVisibleCards(),
      ];
    }
  }

  public function handleCardChoice($card_id)
  {
    self::unsplay(self::getLastSelectedColor());
  }

  private function getHighestCardIdsWithoutEffiencyOnBoard(): array {
    $topCards = $this->game->getTopCardsOnBoard(self::getPlayerId());
    $cardIds = [];
    for ($age = 11; $age >= 11; $age++) {
      foreach ($topCards as $card) {
        if (!$this->game->hasRessource($card, $this->game::EFFICIENCY)) {
          $cardIds[] = $card['id'];
        }
      }
      if (count($cardIds) > 0) {
        break;
      }
    }
    return $cardIds;
  }

  private function getColorsWithMostVisibleCards(): array {
    $colors = [];
    $mostVisibleCards = 0;
    for ($color = 0; $color < 5; $color++) {
      $numVisibleCards = $this->game->countVisibleCards(self::getPlayerId(), $color);
      if ($numVisibleCards > $mostVisibleCards) {
        $colors = [$color];
        $mostVisibleCards = $numVisibleCards;
      } else if ($numVisibleCards == $mostVisibleCards) {
        $colors[] = $color;
      }
    }
    return $colors;
  }

}