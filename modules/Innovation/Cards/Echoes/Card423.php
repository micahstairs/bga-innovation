<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card423 extends Card
{

  // Karaoke
  // - 3rd edition
  //   - ECHO: Draw and meld a card of value less than [10].
  //   - Execute all of the non-demand dogma effects of the card you melded due to Karaoke's echo
  //     effect. Do not share them.
  //   - You may take a bottom card from your board into your hand.
  // - 4th edition
  //   - ECHO: Draw and meld a card of value present in your hand.
  //   - Transfer your bottom card of each color to your hand.
  //   - Claim an available achievement of value equal to the card you last melded due to Karaoke's
  //     echo effect during this action, regardless of eligibility. If you do, self-execute the
  //     melded card.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition() && !$this->game->isExecutingAgainDueToEndorsedAction()) {
        self::setActionScopedAuxiliaryArray([], self::getPlayerId());
      }
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (self::isFirstOrThirdEdition()) {
        self::setMaxSteps(1);
      } else {
        foreach (Colors::ALL as $color) {
          self::transferToHand(self::getBottomCardOfColor($color));
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        $values = [1, 2, 3, 4, 5, 6, 7, 8, 9];
      } else {
        $values = self::getUniqueValues('hand');
        if (empty($values)) {
          $values[] = 0;
        }
      }
      return [
        'choose_value' => true,
        'age'          => $values,
      ];
    } else if (self::isFirstNonDemand()) {
      $cardIds = self::getActionScopedAuxiliaryArray(self::getPlayerId());
      $choices = [];
      for ($i = 0; $i < count($cardIds); $i++) {
        $choices[] = $i;
      }
      return ['choices' => $choices];
    } else if (self::isFirstOrThirdEdition()) {
      return [
        'can_pass'      => true,
        'location_from' => 'board',
        'location_to'   => 'hand',
        'bottom_from'   => true,
      ];
    } else {
      $value = 0;
      // NOTE: A loop is used for convenience but the array will have at most one element in it.
      foreach (self::getActionScopedAuxiliaryArray(self::getPlayerId()) as $cardId) {
        $value = self::getCard($cardId)['faceup_age'];
      }
      return [
        'achieve_keyword' => true,
        'age' => $value,
      ];
    }
  }

  protected function getPromptForListChoice(): array
  {
    $cardIds = self::getActionScopedAuxiliaryArray(self::getPlayerId());
    $choices = [];
    for ($i = 0; $i < count($cardIds); $i++) {
      $choices[$i] = [
        clienttranslate('Self-execute ${card}'),
        'card' => $this->game->getNotificationArgsForCardList([self::getCard($cardIds[$i])]),
      ];
    }
    return self::buildPromptFromList($choices);
  }

  public function handleSpecialChoice(int $choice)
  {
    if (self::isEcho()) {
      $card = self::drawAndMeld($choice);
      if (self::isFirstOrThirdEdition()) {
        self::addToActionScopedAuxiliaryArray($card['id'], self::getPlayerId());
      } else {
        self::setActionScopedAuxiliaryArray([$card['id']], self::getPlayerId());
      }
    } else {
      $cardIds = self::getActionScopedAuxiliaryArray(self::getPlayerId());
      self::selfExecute(self::getCard($cardIds[$choice]));
    }
  }

  public function handleCardChoice(array $card) {
    if (self::isFourthEdition() && self::isSecondNonDemand()) {
      // NOTE: A loop is used for convenience but the array will have at most one element in it.
      foreach (self::getActionScopedAuxiliaryArray(self::getPlayerId()) as $cardId) {
        self::selfExecute(self::getCard($cardId));
      }
    }
  }

}