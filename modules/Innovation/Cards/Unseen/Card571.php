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
    if (self::isFirstInteraction()) {
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
    if (self::isFirstInteraction()) {
      $card = self::drawAndTuck(8);
      $isRedOrPurple = self::isRed($card) || self::isPurple($card);
      if ($isRedOrPurple) {
        self::setNextStep(1);
      }
      self::setAuxiliaryValue2($isRedOrPurple ? 1 : 0);

      // Check if the wish should be granted
      if (self::hasIcon($card, $this->game::CONCEPT)) {
        $this->notifications->notifyGeneralInfo(clienttranslate('It has a ${icon} so the wish is granted.'), ['icon' => $this->notifications->getIconSquare($this->game::CONCEPT)]);
        $choice = self::getAuxiliaryValue();
        if ($choice === 1) {
          self::draw(10);
          self::draw(10);
        } else if ($choice === 2) {
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

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      1 => [clienttranslate('Draw two ${age}'), 'age' => self::renderValue(10)],
      2 => [clienttranslate('Draw and score two ${age}'), 'age' => self::renderValue(8)],
      3 => clienttranslate('Safeguard two available achievements'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    $args = [];
    if ($choice === 1) {
      $args = ['age' => self::renderValue(10)];
      $playerMessage = clienttranslate('${You} wish to draw two ${age}.');
      $othersMessage = clienttranslate('${player_name} wishes to draw two ${age}.');
    } else if ($choice === 2) {
      $args = ['age' => self::renderValue(8)];
      $playerMessage = clienttranslate('${You} wish to draw and score two ${age}.');
      $othersMessage = clienttranslate('${player_name} wishes to draw and score two ${age}.');
    } else {
      $playerMessage = clienttranslate('${You} wish to safeguard two available achievements.');
      $othersMessage = clienttranslate('${player_name} wishes to safeguard two available achievements.');
    }
    self::notifyPlayer($playerMessage, array_merge($args, ['You' => 'You']));
    self::notifyOthers($othersMessage, array_merge($args, ['player_name' => self::renderPlayerName()]));
    self::setAuxiliaryValue($choice); // Tracks the wish that the player chose
  }

}