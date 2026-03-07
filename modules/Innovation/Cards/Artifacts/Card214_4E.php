<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Locations;

class Card214_4E extends AbstractCard
{
  // Dragon's Lair (4th edition):
  //   - I COMPEL you to meld the lowest card in your score pile with a unique value! If you do,
  //     meld a card of the same color from your hand! If you meld one card total, you lose! 
  //     Otherwise, junk the top card of each deck!

  public function initialExecution()
  {
    foreach (self::getCardsKeyedByValue(Locations::SCORE) as $cards) {
      if (count($cards) === 1) {
        self::meld($cards[0]);
        self::setAuxiliaryValue(self::getColor($cards[0])); // Track color to meld from hand
        self::setMaxSteps(1);
        return;
      }
    }
    self::junkTopCardOfEachDeck();
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->meld()->withColor(self::getAuxiliaryValue())->fromYourHand()->revealingIfUnable();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 0) {
      self::lose();
    } else {
      self::junkTopCardOfEachDeck();
    }
  }

  private function junkTopCardOfEachDeck()
  {
    self::junkCards(self::geyCardsToJunk());
    self::notifyPlayer(clienttranslate('${You} junk the top card of each base deck'));
    self::notifyOthers(clienttranslate('${player_name} junks the top card of each base deck'));
  }

  private function geyCardsToJunk(): array
  {
    $cards = [];
    # TODO(FIGURES): Handle age 0 cards
    for ($i = 1; $i <= 11; $i++) {
      $cards[] = $this->game->getDeckTopCard($i, CardTypes::BASE);
    }
    return $cards;
  }

  public function compelMightBeEffective(): bool
  {
    return self::countCards(Locations::SCORE) > 0 || self::geyCardsToJunk();
  }

}