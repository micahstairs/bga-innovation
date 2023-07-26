<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card352 extends Card
{

  // Watermill
  // - 3rd edition:
  //   - Tuck a card with a bonus from your hand. If you do, draw a card of value equal to that
  //     card's bonus. If the drawn card also has a bonus, you may return a card from your hand to
  //     repeat this dogma effect.
  // - 4th edition:
  //   - Draw a card of value equal to a bonus on your board, if you have one.
  //   - Tuck a card from your hand. If Watermill was foreseen, tuck all cards from the deck of
  //     value equal to the tucked card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      if (self::getCurrentStep() === 1) {
        return [
          'location_from' => 'hand',
          'tuck_keyword'  => true,
          'with_bonus'    => true,
        ];
      } else {
        return [
          'can_pass'      => true,
          'location_from' => 'hand',
          'location_to'   => 'deck',
        ];
      }
    } else if (self::getEffectNumber() === 1) {
      return [
        'choose_value' => true,
        'age'          => $this->game->getVisibleBonusesOnBoard(self::getPlayerId()),
      ];
    } else {
      return [
        'location_from' => 'hand',
        'tuck_keyword'  => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstOrThirdEdition()) {
      if (self::getCurrentStep() === 1) {
        $drawnCard = self::draw(self::getBonusIcon($card));
        if (self::hasBonusIcon($drawnCard)) {
          self::setMaxSteps(2);
        }
      } else {
        self::setNextStep(1);
        self::setMaxSteps(1);
      }
    } else if (self::getEffectNumber() === 2 && self::wasForeseen()) {
      while ($topCard = $this->game->getDeckTopCard($card['age'], $this->game::BASE)) {
        self::tuck($topCard);
      }
    }
  }

  public function handleSpecialChoice(int $value) {
    self::draw($value);
  }

  public function afterInteraction()
  {
    if (self::isFirstOrThirdEdition() && self::getCurrentStep() === 1) {
      // Reveal hand to prove that there are no bonuses
      self::revealHand();
    }
  }

}