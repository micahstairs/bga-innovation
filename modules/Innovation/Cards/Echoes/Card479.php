<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card479 extends AbstractCard
{

  // Meritocracy
  //   - I DEMAND you choose the standard icon of which I have the most among my icon types!
  //     Transfer all cards with that visible icon from your board to mine, or if Meritocracy was
  //     foreseen, to my achievements!

  public function initialExecution()
  {
    $iconCounts = self::getStandardIconCounts(self::getLauncherId());
    $maxCount = max(array_values($iconCounts));
    $icons = array_keys(array_filter($iconCounts, function ($count) use ($maxCount) {
      return $count === $maxCount;
    }));

    if ($icons) {
      self::setAuxiliaryArray($icons); // Track icons which can be chosen
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'choose_icon_type' => true,
        'icon'             => self::getAuxiliaryArray(),
      ];
    } else {
      $icon = self::getAuxiliaryValue();
      self::setAuxiliaryArray(self::getCardIdsWithVisibleIcon($icon)); // Repurpose array to store the card IDs to transfer
      return [
        'n'                               => 'all',
        'location_from'                   => Locations::PILE,
        'location_to'                     => Locations::BOARD,
        'owner_to'                        => self::getLauncherId(),
        'with_icon'                       => $icon,    
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleIconChoice(int $icon)
  {
    self::setAuxiliaryValue($icon); // Track icon chosen
  }

  private function getCardIdsWithVisibleIcon(int $icon)
  {
    $cardIds = [];
    $stacks = self::getCardsKeyedByColor(Locations::BOARD);
    foreach ($stacks as $stack) {
      if (count($stack) > 1) {
        $spots = self::getVisibleSpotsOnBuriedCard(intval($stack[0]['splay_direction']));
      }
      foreach ($stack as $card) {
        if ($card['position'] == count($stack) - 1) {
          // All icons are visible on the top card in the stack
          $icons = self::getIcons($card, [1, 2, 3, 4, 5, 6]);
        } else {
          $icons = self::getIcons($card, $spots);
        }
        if (in_array($icon, $icons)) {
          $cardIds[] = $card['id'];
        }
      }
    }
    return $cardIds;
  }

}