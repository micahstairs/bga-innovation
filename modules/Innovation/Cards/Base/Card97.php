<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card97 extends AbstractCard
{
  // Miniaturization:
  // - 3rd edition:
  //   - You may return a card from your hand. If you returned a [10], draw a [10] for every
  //     different value of card in your score pile.
  // - 4th edition:
  //   - Return a card from your hand. If you return a [10], draw a [10] for every different
  //     value of card in your score pile. If you return an [11], junk all cards in the [11] deck.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstOrThirdEdition()) {
      return self::youMay()->return()->fromYourHand();
    } else {
      return self::youMust()->return()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::getValue($card) == 10) {
      $numUniqueValues = count(self::getUniqueValuesInLocation(Locations::SCORE));
      $args = ['number' => $numUniqueValues];
      self::notifyPlayer(clienttranslate('${You} have ${number} different values in your score pile.'), $args);
      self::notifyOthers(clienttranslate('${player_name} has ${number} different values in his score pile.'), $args);
      for ($i = 0; $i < $numUniqueValues; $i++) {
        self::draw(10);
      }
    } else if (self::isFourthEdition() && self::getValue($card) == 11) {
      self::junkBaseDeck(11);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}