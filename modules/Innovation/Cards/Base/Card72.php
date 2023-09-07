<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Enums\CardTypes;

class Card72 extends Card
{

  // Sanitation:
  // - 3rd edition:
  //   - I DEMAND you exchange the two highest cards in your hand with the lowest card in my hand!
  // - 4th edition:
  //   - I DEMAND you exchange the two highest cards in your hand with the lowest card in my hand!
  //   - Choose [7] or [8]. Junk all cards in that deck.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(3);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return [
          'player_id'     => self::getLauncherId(),
          'owner_from'    => self::getLauncherId(),
          'location_from' => 'hand',
          'owner_to'      => self::getPlayerId(),
          'location_to'   => 'hand',
          'age'           => self::getMinValueInLocation('hand', self::getLauncherId()),
        ];
      } else {
        return [
          'owner_from'    => self::getPlayerId(),
          'location_from' => 'hand',
          'owner_to'      => self::getLauncherId(),
          'location_to'   => 'hand',
          'age'           => self::getMaxValueInLocation('hand'),
        ];
      }
    } else {
      return ['choices' => [7, 8]];
    }
  }

  public function executeCardTransfer(array $card): bool
  {
    if (self::isFirstInteraction()) {
      // Delay the transfer, so that the other player cannot choose the card they would be giving them
      self::setAuxiliaryValue($card['id']);
      return true;
    }
    return false;
  }

  public function afterInteraction()
  {
    if (self::isThirdInteraction()) {
      $this->game->gamestate->changeActivePlayer(self::getLauncherId());
      self::transferToHand(self::getCard(self::getAuxiliaryValue()));
      self::setAuxiliaryValue(-1);
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      7 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(7, CardTypes::BASE)],
      8 => [clienttranslate('Junk ${age} deck'), 'age' => self::renderValueWithType(8, CardTypes::BASE)],
    ]);
  }

  public function handleSpecialChoice($value)
  {
    self::junkBaseDeck($value);
  }

}