<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card457 extends AbstractCard
{
  // Tasmanian Tiger
  //   - Choose a card in your score pile. Choose a top card of the same color on any player's
  //     board. Exchange the two cards, maintaining any splay. You may return two cards from your
  //     hand. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(3);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(-1); // Track card chosen from the score pile
      return ['choose_from' => Locations::SCORE];
    } else if (self::isSecondInteraction()) {
      if (self::getAuxiliaryValue() === -1) {
        // Skip this interaction if no card was chosen from the score pile
        return [];
      }
      return [
        'choose_from' => Locations::BOARD,
        'owner_from'  => 'any player',
        'color'       => [self::getLastSelectedColor()],
      ];
    } else {
      return [
        'can_pass'       => true,
        'n'              => 2,
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue($card['id']); // Track card selected from score pile
    } else if (self::isSecondInteraction()) {
      self::transferToBoard(self::getCard(self::getAuxiliaryValue()), $card['owner']);
      self::transferToScorePile($card, self::getPlayerId());
    }
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction() && self::getNumChosen() === 2) {
      self::setNextStep(1);
    }
  }

}