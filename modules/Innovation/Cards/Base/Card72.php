<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card72 extends AbstractCard
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
        return self::youMust()->lowest()->fromMyHand()->toYours()->ofMyChoice()->build();
      } else {
        return self::youMust()->exactly(2)->highest()->fromYourHand()->toMine()->build();
      }
    } else {
      return self::youMust()->choose([7, 8])->build();
    }
  }

  public function executeCardTransfer(array $card): bool
  {
    if (self::isFirstInteraction()) {
      // Delay the transfer, so that the other player cannot choose the card they would be giving them
      self::setAuxiliaryValue(self::getId($card));
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

  public function handleListChoice($value)
  {
    self::junkBaseDeck($value);
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND) || self::hasCards(Locations::HAND, self::getLauncherId());
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::isFourthEdition() && (self::hasCards(Locations::HAND) || self::hasCards(Locations::HAND, self::getLauncherId()));
  }

}