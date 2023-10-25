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
      return ['choose_from' => Locations::SCORE];
    } else if (self::isSecondInteraction()) {
      return [
        'choose_from' => Locations::BOARD,
        'owner_from'  => 'any player',
        'color'       => [self::getLastSelectedColor()],
      ];
    } else {
      return [
        'can_pass'      => true,
        'n'             => 'all',
        'location_from' => Locations::HAND,
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
    } else if (self::isThirdInteraction()) {
      self::setNextStep(1);
    }
  }

}