<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card592 extends AbstractCard
{

  // Fashion Mask:
  //   - Tuck a top card with a [PROSPERITY] or [INDUSTRY] of each color on your board. You may
  //     safeguard one of the tucked cards.
  //   - Score all but the top five each of your yellow and purple cards. Splay those colors aslant.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else if (self::isSecondNonDemand()) {
      foreach ([Colors::YELLOW, Colors::PURPLE] as $color) {
        $stack = self::getStack($color);
        for ($i = 0; $i < count($stack) - 5; $i++) {
          self::score($stack[$i]);
        }
      }
      self::splayAslant(Colors::YELLOW);
      self::splayAslant(Colors::PURPLE);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryArray(self::getTopCardIdsWithProsperityOrIndustryIcons());
      return [
        'n'                               => 'all',
        'location_from'                   => 'board',
        'tuck_keyword'                    => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'can_pass'                        => true,
        'location_from'                   => 'board',
        'bottom_from'                     => true,
        'location_to'                     => 'safe',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  private function getTopCardIdsWithProsperityOrIndustryIcons(): array
  {
    $cardIds = [];
    foreach (self::getTopCards() as $card) {
      if (self::hasIcon($card, Icons::PROSPERITY) || self::hasIcon($card, Icons::INDUSTRY)) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}