<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card549 extends AbstractCard
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
        'can_pass'          => true,
        'location_from'     => 'hand',
        'safeguard_keyword' => true,
      ];
    } else if (self::isSecondInteraction()) {
      return [
        'n'             => 2,
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
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
        'n'              => 'all',
        'location_from'  => 'revealed',
        'return_keyword' => true,
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
        self::setAuxiliaryArray($cardIds);
      } else {
        self::setNextStep(4);
      }
    }
  }

  private function getRevealedCardIdsWithoutEfficiencyOrAvatar()
  {
    $cardIds = [];
    foreach (self::getCards('revealed') as $card) {
      if (!self::hasIcon($card, Icons::EFFICIENCY) && !self::hasIcon($card, Icons::AVATAR)) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}