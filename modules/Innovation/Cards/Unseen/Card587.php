<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card587 extends AbstractCard
{

  // Cloaking:
  //   - I DEMAND you transfer one of your claimed standard achievements to my safe!

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->range(1, 11)->toMySafe();
  }

}