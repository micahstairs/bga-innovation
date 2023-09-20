<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card170 extends Card
{
  // Buttonwood Agreement
  //   - Choose three colors. Draw and reveal an [8]. If the drawn card is one of the chosen
  //     colors, score it and splay up that color. Otherwise, return all cards of the drawn
  //     card's color from your score pile, and unsplay that color.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choose_three_colors' => true];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'score',
        'return_keyword' => true,
        'color'          => [self::getAuxiliaryValue()],
      ];
    }
  }

  public function handleThreeColorChoice(int $color1, int $color2, int $color3)
  {
    $args = [
      'i18n'    => ['color_1', 'color_2', 'color_3'],
      'color_1' => Colors::render($color1),
      'color_2' => Colors::render($color2),
      'color_3' => Colors::render($color3),
    ];
    self::notifyPlayer(clienttranslate('${You} choose ${color_1}, ${color_2}, and ${color_3}.'), $args);
    self::notifyOthers(clienttranslate('${player_name} chooses ${color_1}, ${color_2}, and ${color_3}.'), $args);

    $card = self::drawAndReveal(8);
    $this->notifications->notifyCardColor($card['color']);
    if (in_array($card['color'], [$color1, $color2, $color3])) {
      self::score($card);
      self::splayUp($card['color']);
    } else {
      self::setMaxSteps(2);
      self::transferToHand($card);
      self::setAuxiliaryValue($card['color']); // Track the color to return
    }

  }

}