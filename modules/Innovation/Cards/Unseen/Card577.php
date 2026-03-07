<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;

class Card577 extends AbstractCard
{

  // Fight Club:
  //   - I DEMAND you transfer one of your secrets to my achievements!
  //   - You may splay your yellow cards up.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->fromYourSafe()->toMyAchivements();
    } else {
      return self::youMay()->splayUp(Colors::YELLOW);
    }
  }

}