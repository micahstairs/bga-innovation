<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card175 extends AbstractCard
{
  // Periodic Table
  //   - Choose two top cards on your board of the same value. If you do, draw a card of value one
  //     higher and meld it. If it melded over one of the chosen cards, repeat this effect.

  public function initialExecution()
  {
    if (self::countTopCardsOfSameValue() >= 2) {
      self::setMaxSteps(2);
    } else {
      self::notifyNoTopCardsOfSameValue();
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      $colors = [];
      $repeatedValues = self::getRepeatedValues(self::getTopCards());
      foreach (self::getTopCards() as $card) {
        if (in_array($card['faceup_age'], $repeatedValues)) {
          $colors[] = $card['color'];
        }
      }
      return [
        'choose_from' => 'board',
        'color'       => $colors,
      ];
    } else {
      return [
        'choose_from' => 'board',
        'not_id'      => self::getLastSelectedId(),
        'age'         => self::getLastSelectedFaceUpAge(),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    $cardArgs = $this->game->getNotificationArgsForCardList([$card]);
    self::notifyPlayer(clienttranslate('${You} choose ${card}.'), ['card' => $cardArgs]);
    self::notifyOthers(clienttranslate('${player_name} chose ${card}'), ['card' => $cardArgs]);
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue($card['color']);
    } else {
      $color1 = self::getAuxiliaryValue();
      $color2 = $card['color'];
      $meldedCard = self::drawAndMeld($card['faceup_age'] + 1);
      if ($meldedCard['color'] == $color1 || $meldedCard['color'] == $color2) {
        if (self::countTopCardsOfSameValue() >= 2) {
          self::setNextStep(2);
        } else {
          self::notifyNoTopCardsOfSameValue();
        }
      }
    }
  }

  private function countTopCardsOfSameValue(): int
  {
    return count(self::getRepeatedValues(self::getTopCards()));
  }

  private function notifyNoTopCardsOfSameValue()
  {
    self::notifyPlayer(clienttranslate('${You} have no top cards with the same value.'));
    self::notifyOthers(clienttranslate('${player_name} has no top cards with the same value.'));
  }

}