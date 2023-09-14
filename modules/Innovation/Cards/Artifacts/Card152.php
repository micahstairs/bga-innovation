<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card152 extends Card
{

  // Mona Lisa
  //   - Choose a number and a color. Draw five [4], then reveal your hand. If you have exactly
  //     that many cards of that color, score them, and splay right your cards of that color.
  //     Otherwise, return all cards from your hand.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_color' => true];
    } else if (self::isSecondInteraction()) {
      return ['choose_non_negative_integer' => true];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'hand',
        'return_keyword' => true,
      ];
    }
  }

  public function handleSpecialChoice(int $choice)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue2($choice); // Track the chosen color
      // Help the UI pick a reasonable default for the integer selection
      self::setAuxiliaryValue(self::countCardsKeyedByColor(Locations::HAND)[$choice]);
    } else {
      for ($i = 0; $i < 5; $i++) {
        self::draw(4);
      }
      self::revealHand();
      $color = self::getAuxiliaryValue2();
      $cards = self::getCardsKeyedByColor(Locations::HAND)[$color];
      $args = ['i18n' => ['color'], 'n' => count($cards), 'color' => Colors::render($color)];
      self::notifyPlayer(clienttranslate('${You} revealed ${n} ${color} cards.'), $args);
      self::notifyOthers(clienttranslate('${player_name} revealed ${n} ${color} cards.'), $args);
      if (count($cards) == $choice) {
        foreach ($cards as $card) {
          self::score($card);
        }
        self::splayRight($color);
      } else {
        self::setMaxSteps(3);
      }
    }
  }

}