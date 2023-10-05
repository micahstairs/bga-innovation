<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card175 extends AbstractCard
{
  // Periodic Table
  //   - Choose two top cards on your board of the same value. If you do, draw a card of value one
  //     higher and meld it. If it melded over one of the chosen cards, repeat this effect.

  public function initialExecution()
  {
    if (count(self::getRepeatedValues(self::getTopCards())) >= 1) {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(-1); // Indicate that the first color has not been chosen yet
    } else {
      self::notifyNoTopCardsOfSameValue();
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getAuxiliaryValue() === -1) {
      $colors = [];
      $repeatedValues = self::getRepeatedValues(self::getTopCards());
      foreach (self::getTopCards() as $card) {
        if (in_array(self::getValue($card), $repeatedValues)) {
          $colors[] = $card['color'];
        }
      }
      return [
        'n'                 => 2,
        'choose_from'       => Locations::BOARD,
        'color'             => $colors,
        'refresh_selection' => true,
        'enable_autoselection' => true,
      ];
    } else {
      return [
        'choose_from' => Locations::BOARD,
        'not_id'      => self::getLastSelectedId(),
        'age'         => self::getLastSelectedFaceUpAge(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    $args = ['card' => $this->game->getNotificationArgsForCardList([$card])];
    self::notifyPlayer(clienttranslate('${You} choose ${card}.'), $args);
    self::notifyOthers(clienttranslate('${player_name} chose ${card}'), $args);
    if (self::getAuxiliaryValue() === -1) {
      self::setAuxiliaryValue($card['color']);
    } else {
      $color1 = self::getAuxiliaryValue();
      $color2 = $card['color'];
      $meldedCard = self::drawAndMeld(self::getValue($card) + 1);
      if ($meldedCard['color'] == $color1 || $meldedCard['color'] == $color2) {
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

}