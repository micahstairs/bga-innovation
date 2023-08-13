<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card549 extends Card
{

  // Black Market
  //   - You may safeguard a card from your hand. If you do, reveal two available standard
  //     achievements. You may meld a revealed card with no [EFFICIENCY] or [AVATAR]. Return each
  //     revealed card you do not meld.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {

    if (self::isFirstInteraction()) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'location_to'   => 'safe',
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'n'             => 2,
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'revealed',
      ];
    } else if (self::isThirdInteraction()) {
      return [
        'can_pass'                        => true,
        'location_from'                   => 'revealed',
        'location_to'                     => 'board',
        'meld_keyword'                    => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => 'revealed',
        'location_to'   => 'deck',
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() > 0) {
        $card = self::getLastSelectedCard();
        if ($card['location'] == 'safe' && $card['owner'] == self::getPlayerId()) {
          self::setMaxSteps(4);
        }
      }
    } else if (self::isSecondInteraction()) {
      $cardIds = self::getRevealedCardIdsWithoutEfficiencyOrAvatar();
      if (count($cardIds) > 0) {
        $this->game->setAuxiliaryArray($cardIds);
      } else {
        self::setNextStep(4);
      }
    }
  }

  private function getRevealedCardIdsWithoutEfficiencyOrAvatar()
  {
    $cardIds = [];
    $revealed_cards = self::getCards( 'revealed');
    foreach ($revealed_cards as $card) {
      if (
        !$this->game->hasRessource($card, $this->game::EFFICIENCY) &&
        !$this->game->hasRessource($card, $this->game::AVATAR)
      ) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}