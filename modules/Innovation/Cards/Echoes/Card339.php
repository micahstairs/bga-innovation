<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card339 extends Card
{

  // Chopsticks
  // - 3rd edition:
  //   - ECHO: Draw a [1].
  //   - If the [1] deck has at least one card, you may transfer its bottom card to the available achievements.
  // - 4th edition:
  //   - ECHO: You may draw and foreshadow a [1].
  //   - You may junk all cards in the [1] deck. If you do, achieve the highest junked card if eligible.

  public function initialExecution()
  {
    if (self::isEcho()) {
      if (self::isFirstOrThirdEdition()) {
        self::draw(1);
      } else {
        self::drawAndForeshadow(1);
      }
    } else if (self::getBaseDeckCount(1) > 0) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'can_pass' => true,
        'choices'  => [1],
      ];
    } else {
      return [
        'location_from'                   => 'junk',
        'location_to'                     => 'achievements',
        'require_achievement_eligibility' => true,
        'age'                             => self::getMaxValueInLocation('junk'),
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::getPromptForChoiceFromList([
        1 => [clienttranslate('Transfer the bottom ${age} to the available achievements'), 'age' => $this->game->getAgeSquare(1)],
      ]);
    } else {
      return self::getPromptForChoiceFromList([
        1 => [clienttranslate('Junk all cards in the ${age} deck'), 'age' => $this->game->getAgeSquare(1)],
      ]);
    }
  }

  public function handleSpecialChoice(int $choice): void
  {
    if (self::isFirstOrThirdEdition()) {
      if ($choice === 1) {
        $this->game->executeDraw(0, /*age=*/1, 'achievements', /*bottom_to=*/false, /*type=*/0, /*bottom_from=*/true);
      } else {
        $this->game->notifyPlayer(
          self::getPlayerId(),
          'log',
          clienttranslate('${You} choose not to transfer a ${age} to the available achievements.'),
          ['You' => 'You', 'age' => $this->game->getAgeSquare(1)]
        );
        $this->game->notifyAllPlayersBut(
          self::getPlayerId(),
          'log',
          clienttranslate('${player_name} chooses not to transfer a ${age} to the available achievements.'),
          ['player_name' => $this->game->getColoredPlayerName(self::getPlayerId()), 'age' => $this->game->getAgeSquare(1)]
        );
      }
    } else {
      if ($choice === 1) {
        self::junkBaseDeck(1);
        self::setMaxSteps(2);
      } else {
        $this->game->notifyPlayer(
          self::getPlayerId(),
          'log',
          clienttranslate('${You} choose not to junk all cards in the ${age} deck.'),
          ['You' => 'You', 'age' => $this->game->getAgeSquare(1)]
        );
        $this->game->notifyAllPlayersBut(
          self::getPlayerId(),
          'log',
          clienttranslate('${player_name} chooses not to junk all cards in the ${age} deck.'),
          ['player_name' => $this->game->getColoredPlayerName(self::getPlayerId()), 'age' => $this->game->getAgeSquare(1)]
        );
      }
    }
  }

}