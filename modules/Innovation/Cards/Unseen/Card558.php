<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card558 extends Card
{

  // Enigma Machine:
  //   - Choose to either safeguard all available standard achievements, transfer all your secrets
  //     to your hand, or transfer all cards in your hand to the available achievements.
  //   - Splay a color of your cards splayed left up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() == 1) {
      if (self::getCurrentStep() == 1) {
        return ['choices' => [1, 2, 3]];
      } else {
        return [
          'n'             => 'all',
          'location_from' => 'achievements',
          'owner_from'    => 0,
          'location_to'   => 'safe',
        ];
      }
    } else {
      return [
        'splay_direction'     => $this->game::UP,
        'has_splay_direction' => [$this->game::LEFT],
      ];
    }
  }

  public function getSpecialChoicePrompt(): array
  {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the three possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Safeguard all available standard achievements'),
        ],
        [
          'value' => 2,
          'text'  => clienttranslate('Transfer all your secrets to your hand'),
        ],
        [
          'value' => 3,
          'text'  => clienttranslate('Transfer all cards in your hand to the available achievements'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    if ($choice == 1) {
      self::setMaxSteps(2);;
    } else if ($choice == 2) {
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'safe') as $card) {
        self::putInHand($card);
      }
    } else if ($choice == 3) {
      foreach ($this->game->getCardsInHand(self::getPlayerId()) as $card) {
        $this->game->transferCardFromTo($card, 0, 'achievements');
      }
    }
  }

}