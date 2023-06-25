<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card555 extends Card
{

  // Blacklight:
  //   - Choose to either unsplay one color of your cards, or splay up an unsplayed color on your
  //     board and draw a [9].

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return ['choose_yes_or_no' => true];
    } else {
      if (self::getAuxiliaryValue() == 1) {
         return [
          'splay_direction' => $this->game::UNSPLAYED,
         ];
      } else {
        return [
          'splay_direction' => $this->game::UP,
          'has_splay_direction' => [$this->game::UNSPLAYED],
        ];
      }
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Unsplay one color')
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Splay up and draw')
        ],
      ],
    ];
  }

  public function handleSpecialChoice($choice)
  {
    self::setAuxiliaryValue($choice);
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 2 && self::getAuxiliaryValue() == 0) {
      self::draw(9);
    }
  }

}