<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card502 extends AbstractCard
{
  // Fingerprints
  //   - You may splay your red or yellow cards left.
  //   - Safeguard an available achievement of value equal to the number of splayed colors on your
  //     board. Transfer a card of that value in your hand to any board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      // TODO(FIGURES): Handle case where there are cards of value 0.
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayLeft()->withColor([Colors::RED, Colors::YELLOW])->build();
    } else if (self::isFirstInteraction()) {
      return self::youMust()->safeguard()->value(self::countSplayedColors())->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->choosePlayer()->build();
    } else {
      return self::youMust()->value(self::getAuxiliaryValue())->fromYourHand()->toPlayer(self::getAuxiliaryValue2())->toBoard()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isSecondNonDemand() && self::isFirstInteraction()) {
      // Don't bother picking a player if there are no cards of that value in hand
      $value = self::getValue($card);
      if (self::countCardsKeyedByValue(Locations::HAND)[$value] > 0) {
        self::setAuxiliaryValue($value); // Track value to transfer
        self::setMaxSteps(3);
      }
    }
  }

  protected function getPromptForPlayerChoice(): array
  {
    return [
      "message_for_player" => clienttranslate('Choose a player whose board you will transfer a card to'),
      "message_for_others" => clienttranslate('${player_name} must choose a player'),
    ];
  }

  public function handlePlayerChoice(int $playerId)
  {
    self::setAuxiliaryValue2($playerId); // Track player to transfer the card to
  }

}