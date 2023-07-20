<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card516 extends Card
{

  // The Prophecies:
  //   - Choose to either draw and safeguard a [4], or draw and reveal a card of value one higher
  //     than one your secrets. If you reveal a red or purple card, meld one of your other secrets.
  //     If you do, safeguard the drawn card.

  public function initialExecution()
  {
    if ($this->game->countCardsInLocation(self::getPlayerId(), 'safe') > 0) {
      self::setMaxSteps(1);
    } else {
      self::drawAndSafeguard(4);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return ['choose_yes_or_no' => true];
    } else if (self::getCurrentStep() == 2) {
      return [
        'location_from' => 'safe',
        'location_to'   => 'none',
      ];
    } else {
      return [
        'location_from' => 'safe',
        'location_to'   => 'board',
        'not_id'        => self::getAuxiliaryValue2(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 2) {
      $card = self::drawAndReveal(self::getLastSelectedAge() + 1);
      if ($card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE) {
        self::setAuxiliaryValue2(self::getLastSelectedId());
        self::setMaxSteps(3);
      } else {
        self::putInHand($card);
      }
    } else if (self::getCurrentStep() == 3) {
      $revealedCard = self::getCards('revealed')[0];
      if (self::getNumChosen() > 0) {
        self::safeguard($revealedCard);
        // Put all revealed cards in hand if they can't fit in the safe
        foreach (self::getCards('revealed') as $card) {
            self::putInHand($card);
        }
      } else {
        self::putInHand($revealedCard);
      }
    }

  }

  public function getSpecialChoicePrompt(): array
  {
    $ageToDraw = $this->game->getAgeToDrawIn(self::getPlayerId(), 4);
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => $ageToDraw <= $this->game->getMaxAge() ? clienttranslate('Draw and safeguard a ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
          'age'   => $this->game->getAgeSquare($ageToDraw),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Draw and reveal a card of value one higher than one your secrets'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(int $choice): void
  {
    $secrets = self::getCards('safe');
    if ($choice === 1) {
      self::drawAndSafeguard(4);
    } else if (count($secrets) == 1) {
      $secret = $secrets[0];
      $card = self::drawAndReveal($secret['faceup_age'] + 1);
      if ($card['color'] == $this->game::RED || $card['color'] == $this->game::PURPLE) {
        self::safeguard($card);
      } else {
        self::putInHand($card);
      }
    } else {
      self::setMaxSteps(2);
    }
  }
}