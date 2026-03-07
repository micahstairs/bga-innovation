<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card175 extends AbstractCard
{
  // Periodic Table
  //   - Choose two top cards on your board of the same value. If you do, draw a card of value one
  //     higher and meld it. If it melded over one of the chosen cards, repeat this effect.

  public function initialExecution()
  {
    if (self::getRepeatedValues(self::getTopCards())) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(-1); // Indicate that the first color has not been chosen yet
    } else {
      self::notifyNoTopCardsOfSameValue();
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::getAuxiliaryValue() === -1) {
      $topCards = self::getTopCards();
      $colors = self::getColorsMatchingValues($topCards, self::getRepeatedValues($topCards));
      return self::youMust()->chooseCardFrom(Locations::BOARD)->exactly(2)->withColor($colors)->refreshingSelection()->forceAutoselection();
    } else {
      return self::youMust()->chooseCardFrom(Locations::BOARD)->otherThan(self::getLastSelectedId())->value(self::getLastSelectedFaceUpAge());
    }
  }

  public function handleCardChoice(array $card)
  {
    $args = ['card' => $this->game->getNotificationArgsForCardList([$card])];
    self::notifyPlayer(clienttranslate('${You} choose ${card}.'), $args);
    self::notifyOthers(clienttranslate('${player_name} chose ${card}'), $args);
    if (self::getAuxiliaryValue() === -1) {
      self::setAuxiliaryValue(self::getColor($card));
    } else {
      $color1 = self::getAuxiliaryValue();
      $color2 = self::getColor($card);
      $meldedCard = self::drawAndMeld(self::getValue($card) + 1);
      if (self::getColor($meldedCard) == $color1 || self::getColor($meldedCard) == $color2) {
        if (count(self::getRepeatedValues(self::getTopCards())) >= 1) {
          self::setNextStep(1);
          self::setAuxiliaryValue(-1); // Indicate that the first color has not been chosen yet
        } else {
          self::notifyNoTopCardsOfSameValue();
        }
      }
    }
  }

  private function notifyNoTopCardsOfSameValue()
  {
    self::notifyPlayer(clienttranslate('${You} have no top cards with the same value.'));
    self::notifyOthers(clienttranslate('${player_name} has no top cards with the same value.'));
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::getRepeatedValues(self::getTopCards())) >= 1;
  }

}
