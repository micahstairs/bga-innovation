<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card519 extends Card
{

  // Blackmail:
  //   - I DEMAND you reveal your hand! Meld a revealed card of my choice! Reveal your score pile!
  //     Self-execute a card revealed due to this effect of my choice, replacing 'may' with 'must'!

  public function initialExecution()
  {
    self::setAuxiliaryArray([]);
    foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
      self::reveal($card);
      self::addToAuxiliaryArray($card['id']);
    }
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'player_id'     => self::getLauncherId(),
        'owner_from'    => self::getPlayerId(),
        'location_from' => 'revealed',
        'owner_to'      => self::getPlayerId(),
        'meld_keyword'  => true,
      ];
    } else {
      $choices = [];
      $array = self::getAuxiliaryArray();
      for ($i = 0; $i < count($array); $i++) {
        $choices[] = $i;
      }
      return [
        'player_id' => self::getLauncherId(),
        'choices'   => $choices,
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    $cardIds = self::getAuxiliaryArray();
    $choices = [];
    for ($i = 0; $i < count($cardIds); $i++) {
      $choices[$i] = [
        clienttranslate('Self-execute ${card}'),
        'card' => $this->game->getNotificationArgsForCardList([self::getCard($cardIds[$i])]),
      ];
    }
    return self::buildPromptFromList($choices);
  }

  public function handleSpecialChoice(int $index)
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    $cardId = self::getAuxiliaryArray()[$index];
    $this->game->selfExecute(self::getCard($cardId), /*replace_may_with_must=*/true);
  }

  public function afterInteraction()
  {
    $this->game->gamestate->changeActivePlayer(self::getPlayerId());
    foreach (self::getCards('revealed') as $card) {
      self::transferToHand($card);
    }
    self::revealScorePile();
    foreach (self::getCards('score') as $card) {
      self::addToAuxiliaryArray($card['id']);
    }
  }

}