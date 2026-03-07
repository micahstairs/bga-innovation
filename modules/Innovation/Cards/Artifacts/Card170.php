<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card170 extends AbstractCard
{
  // Buttonwood Agreement
  //   - Choose three colors. Draw and reveal an [8]. If the drawn card is one of the chosen
  //     colors, score it and splay up that color on your board. Otherwise, return all cards of
  //     that color from your score pile, and unsplay that color.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseThreeColors();
    } else {
      return self::youMust()->return()->all()->withColor(self::getAuxiliaryValue())->fromYourScore();
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
    $this->notifications->notifyCardColor(self::getColor($card));
    if (in_array(self::getColor($card), [$color1, $color2, $color3])) {
      self::score($card);
      self::splayUp(self::getColor($card));
    } else {
      self::setMaxSteps(2);
      self::transferToHand($card);
      self::setAuxiliaryValue(self::getColor($card)); // Track the color to return
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      self::unsplay(self::getAuxiliaryValue());
    }
  }

}