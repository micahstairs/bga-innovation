<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card387 extends Card
{

  // Loom
  // - 3rd edition
  //   - ECHO: Score your lowest top card.
  //   - You may return two cards of different value from your score pile. If you do, draw and tuck
  //     three [6].
  //   - If you have five or more [IMAGE] visible on your board in one color, claim the Heritage
  //     achievement.
  // - 4th edition
  //   - ECHO: Score your lowest top card.
  //   - You may return exactly two cards of different value from your score pile. If you do, draw
  //     and tuck three [6].
  //   - If you have five [IMAGE] on your board in one color, claim the Heritage achievement.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      if (count(self::getUniqueValues('score')) >= 2) {
        self::setMaxSteps(1);
      }
    } else {
      for ($color = 0; $color < 5; $color++) {
        if ($this->game->countVisibleIconsInPile(self::getPlayerId(), $this->game::HEX_IMAGE, $color) >= 5) {
          $this->game->claimSpecialAchievement(self::getPlayerId(), 437);
          break;
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return [
        'location_from' => 'board',
        'score_keyword' => true,
        'age'           => $this->game->getMinAgeOnBoardTopCards(self::getPlayerId()),
      ];
    } else if (self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'location_from'  => 'score',
        'return_keyword' => true,
      ];
    } else {
      return [
        'location_from'                   => 'score',
        'return_keyword'                  => true,
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        $cardIds = [];
        foreach (self::getCards('score') as $scoreCard) {
          if ($scoreCard['age'] != $card['age']) {
            $cardIds[] = $scoreCard['id'];
          }
        }
        self::setAuxiliaryArray($cardIds);
        self::setMaxSteps(2);
      } else {
        self::drawAndTuck(6);
        self::drawAndTuck(6);
        self::drawAndTuck(6);
      }
    }
  }

}