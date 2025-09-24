<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card587 extends AbstractCard
{

  // Cloaking:
  //   - I DEMAND you transfer one of your claimed standard achievements to my safe!

  public function getInteractionOptions(): array
  {
    return self::youMust()->range(1, 11)->toMySafe()->build();
  }

}