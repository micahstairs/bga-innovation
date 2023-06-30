<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card571 extends Card
{

  // Magic 8-Ball:
  //   - Choose whether you wish to draw two [10], draw and
  //     score two [8], or safeguard two available achievements. 
  //     Draw and tuck an [8]. If it has a CONCEPT, do as you 
  //     wish. If it is red or purple, repeat this effect.'),

  public function initialExecution()
  {
      self::setMaxSteps(1);
      self::setAuxiliaryValue2(0);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
        // "Choose whether you wish to draw two [10], draw and
        //  score two [8], or safeguard two available achievements. "
        return ['choices' => [1, 2, 3]];
    } else {
        // "safeguard two available achievements."
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
        if ($this->game->hasRessource($card, $this->game::CONCEPT)) {
            $choice = self::getAuxiliaryValue();
            if ($choice == 1) {
                // "draw two [10]"
                self::draw(10);
                self::draw(10);
                if ($card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE) {
                    self::setNextStep(1);
                }
            } else if ($choice == 2) {
                // "draw and score two [8]"
                self::drawAndScore(8);
                self::drawAndScore(8);
                if ($card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE) {
                    self::setNextStep(1);
                }
            } else {
                self::setMaxSteps(2);
                if ($card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE) {
                    self::setAuxiliaryValue2(1);
                }
            }
        }
        if ($card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE) {
            self::setNextStep(1);
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
    $player_id = self::getPlayerId();
    $age_to_draw = $this->game->getAgeToDrawIn($player_id, 10);
    $age_to_score = $this->game->getAgeToDrawIn($player_id, 8);
    $max_age = $this->game->getMaxAge();
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the three possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $age_to_draw <= $max_age ? clienttranslate('Draw two ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($age_to_draw)
        ],
        [
          'value' => 2,
          'text'  => $age_to_score <= $max_age ? clienttranslate('Draw and score two ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($age_to_score)
        ],
        [
          'value' => 3,
          'text'  => clienttranslate('Safeguard 2 available achievements'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }  
  
}