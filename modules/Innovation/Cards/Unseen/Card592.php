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
  //   - You may score all but the top five of your yellow or purple cards. If you do, splay
  //     that color aslant.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
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
    } else {
      return [
        'can_pass' => true,
        'choices'  => [Colors::YELLOW, Colors::PURPLE],
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      Colors::YELLOW => clienttranslate('Score all but top five yellow cards'),
      Colors::PURPLE => clienttranslate('Score all but top five purple cards'),
    ]);
  }

  public function handleListChoice(int $color)
  {
    $stack = self::getStack($color);
    $scoredCard = false;
    for ($i = 0; $i < count($stack) - 5; $i++) {
      self::score($stack[$i]);
      $scoredCard = true;
    }
    if ($scoredCard) {
      self::splayAslant($color);
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