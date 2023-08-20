<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card359 extends Card
{

  // Charitable Trust
  // - 3rd edition:
  //   - ECHO: Draw a [3] or [4].
  //   - You may meld the card you drew due to Charitable Trust's echo effect. If you do, either
  //     return or achieve (if eligible) your top green card.
  // - 4th edition:
  //   - ECHO: Draw a [3] or [4].
  //   - You may meld the last card you drew due to Charitable Trust's echo effect. If you meld a [3],
  //     achieve your top green card, if eligible. If you meld a [4], return your top green card.

  public function initialExecution()
  {
    if (self::isEcho()) {
      // No not re-initialize the array if this is a first or third edition game and the echo effect is executing for a second time
      if (self::isFourthEdition() || !$this->game->isExecutingAgainDueToEndorsedAction()) {
        self::setActionScopedAuxiliaryArray([], self::getPlayerId()); // Track which card IDs are drawn in the echo effect
      }
      self::setMaxSteps(1);
    } else if ($this->game->echoEffectWasExecuted()) {
      // The non-demand effect is a no-op if no cards were drawn as part of the echo effect
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return ['choices' => [3, 4]];
    } else if (self::isFirstInteraction()) {
      if (count(self::getActionScopedAuxiliaryArray(self::getPlayerId())) === 2) {
        // If two cards were drawn due to the Endorse action, the launcher is allowed to choose to meld
        // the same card twice. Unfortunately, this means the card may no longer be in a visible
        // location so we need to use a special prompt.
        return [
          'can_pass' => true,
          'choices'  => [0, 1],
        ];
      } else {
        self::setAuxiliaryArray(self::getActionScopedAuxiliaryArray(self::getPlayerId()));
        return [
          'can_pass'                        => true,
          'location_from'                   => 'hand',
          'meld_keyword'                    => true,
          'card_ids_are_in_auxiliary_array' => true,
        ];
      }
    } else {
      if (self::isEligibleForAchieving(self::getCard(self::getAuxiliaryValue()))) {
        return ['choices' => [1, 2]];
      } else {
        return ['choices' => [1]];
      }
    }

  }

  public function getSpecialChoicePrompt(): array
  {
    if (self::isEcho()) {
      return self::buildPromptFromList([
        3 => [clienttranslate('Draw a ${age}'), 'age' => $this->game->getAgeSquare(3)],
        4 => [clienttranslate('Draw a ${age}'), 'age' => $this->game->getAgeSquare(4)],
      ]);
    } else if (self::isFirstInteraction()) {
      $cardIds = self::getActionScopedAuxiliaryArray(self::getPlayerId());
      return self::buildPromptFromList([
        0 => [clienttranslate('Meld ${card}'), 'card' => $this->game->getNotificationArgsForCardList([self::getCard($cardIds[0])])],
        1 => [clienttranslate('Meld ${card}'), 'card' => $this->game->getNotificationArgsForCardList([self::getCard($cardIds[1])])],
      ]);
    } else {
      $cardArgs = $this->game->getNotificationArgsForCardList([self::getCard(self::getAuxiliaryValue())]);
      return self::buildPromptFromList([
        1 => [clienttranslate('Return ${card}'), 'card' => $cardArgs],
        2 => [clienttranslate('Achieve ${card}, if eligible'), 'card' => $cardArgs],
      ]);
    }
  }


  public function handleSpecialChoice($choice)
  {
    if (self::isEcho()) {
      $card = self::draw($choice);
      self::addToActionScopedAuxiliaryArray($card['id'], self::getPlayerId());
    } else if (self::isFirstInteraction()) {
      $cardIds = self::getActionScopedAuxiliaryArray(self::getPlayerId());
      $card = self::getCard($cardIds[$choice]);
      self::meld($card);
      self::respondToMeld($card);
    } else {
      $topGreenCard = self::getCard(self::getAuxiliaryValue());
      if ($choice === 1) {
        self::return($topGreenCard);
      } else {
        self::achieve($topGreenCard);
      }
    }
  }

  public function handleCardChoice(array $card)
  {
    self::respondToMeld($card);
  }

  private function respondToMeld($card)
  {
    $topGreenCard = self::getTopCardOfColor($this->game::GREEN);
    if (!$topGreenCard) {
      return;
    }
    if (self::isFirstOrThirdEdition()) {
      self::setAuxiliaryValue($topGreenCard['id']); // Track card ID which will be returned or achieved
      self::setMaxSteps(2);
    } else if ($card['age'] == 3) {
      self::achieveIfEligible($topGreenCard);
    } else if ($card['age'] == 4) {
      self::return($topGreenCard);
    }
  }

}