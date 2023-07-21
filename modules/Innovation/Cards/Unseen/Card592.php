<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card592 extends Card
{

  // Fashion Mask:
  //   - Tuck a top card with a [PROSPERITY] or [INDUSTRY] of each color on your board. You may
  //     safeguard one of the tucked cards.
  //   - You may score all but the top three of your yellow or purple cards. If you do, splay
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
      if (self::getCurrentStep() === 1) {
        $this->game->setAuxiliaryArray(self::getTopCardIdsWithProsperityOrIndustryIcons());
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
        'choices'  => [$this->game::YELLOW, $this->game::PURPLE],
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      $this->game::YELLOW => clienttranslate('Score all but top three yellow cards'),
      $this->game::PURPLE => clienttranslate('Score all but top three purple cards'),
    ]);
  }

  public function handleSpecialChoice(int $color)
  {
    $cards = self::getCardsKeyedByColor('board')[$color];
    $scoredCard = false;
    for ($i = 0; $i < count($cards) - 3; $i++) {
      self::score($cards[$i]);
      $scoredCard = true;
    }
    if ($scoredCard) {
      self::splayAslant($color);
    }
  }

  private function getTopCardIdsWithProsperityOrIndustryIcons(): array
  {
    $cardIds = [];
    foreach ($this->game->getTopCardsOnBoard(self::getPlayerId()) as $card) {
      if ($this->game->hasRessource($card, $this->game::PROSPERITY) || $this->game->hasRessource($card, $this->game::INDUSTRY)) {
        $cardIds[] = $card['id'];
      }
    }
    return $cardIds;
  }

}