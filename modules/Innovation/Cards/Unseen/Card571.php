<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card571 extends Card
{

  // Magic 8-Ball:
  //   - Choose whether you wish to draw two [10], draw and score two [8], or safeguard two
  //     available achievements. Draw and tuck an [8]. If it has a CONCEPT, do as you wish. If it
  //     is red or purple, repeat this effect.'),

  public function initialExecution()
  {
    self::setMaxSteps(1);
    self::setAuxiliaryValue2(0); // Tracks whether the effect will be repeating
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return ['choices' => [1, 2, 3]];
    } else {
      return [
        'n'             => 2,
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
      $card = self::drawAndTuck(8);
      $isRedOrPurple = $card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE;
      if ($isRedOrPurple) {
        self::setNextStep(1);
      }
      self::setAuxiliaryValue2($isRedOrPurple ? 1 : 0);

      // Check if the wish should be granted
      if ($this->game->hasRessource($card, $this->game::CONCEPT)) {
        $this->notifications->notifyGeneralInfo(clienttranslate('It has a ${icon} so the wish is granted.'), ['icon' => $this->notifications->getIconSquare($this->game::CONCEPT)]);
        $choice = self::getAuxiliaryValue();
        if ($choice == 1) {
          self::draw(10);
          self::draw(10);
        } else if ($choice == 2) {
          self::drawAndScore(8);
          self::drawAndScore(8);
        } else {
          self::setNextStep(2);
          self::setMaxSteps(2);
        }
      } else {
        $this->notifications->notifyGeneralInfo(clienttranslate('It does not have a ${icon} so the wish is not granted.'), ['icon' => $this->notifications->getIconSquare($this->game::CONCEPT)]);
      }
    } else {
      if (self::getAuxiliaryValue2() == 1) {
        self::setNextStep(1);
        self::setMaxSteps(1);
        self::setAuxiliaryValue2(0);
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return self::getPromptForChoiceFromList([
      1 => [clienttranslate('Draw two ${age}'), 'age' => $this->game->getAgeSquare(10)],
      2 => [clienttranslate('Draw and score two ${age}'), 'age' => $this->game->getAgeSquare(8)],
      3 => clienttranslate('Safeguard two available achievements'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice === 1) {
      $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} wish to draw two ${age}.'), ['age' => $this->game->getAgeSquare(10)]);
      $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} wishes to draw two ${age}.'), ['age' => $this->game->getAgeSquare(10)]);
    } else if ($choice === 2) {
      $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} wish to draw and score two ${age}.'), ['age' => $this->game->getAgeSquare(8)]);
      $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} wishes to draw and score two ${age}.'), ['age' => $this->game->getAgeSquare(8)]);
    } else {
      $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} wish to safeguard two available achievements.'), []);
      $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} wishes to safeguard two available achievements.'), []);
    }
    self::setAuxiliaryValue($choice); // Tracks the wish that the player chose
  }

}