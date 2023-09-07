<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card110 extends Card
{

  // Treaty of Kadesh
  // - 3rd edition:
  //   - I COMPEL you to return all top cards from your board with a demand effect!
  //   - Score a top, non-blue card from your board with a demand effect.
  // - 4th edition:
  //   - I COMPEL you to return a top card with a demand effect of each color from your board!
  //   - Score a top, non-blue card from your board with a demand effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      return [
        'n'                 => 'all',
        'location_from'     => 'board',
        'return_keyword'    => true,
        'has_demand_effect' => true,
      ];
    } else {
      return [
        'location_from'     => 'board',
        'score_keyword'     => true,
        'color'             => Colors::NON_BLUE,
        'has_demand_effect' => true,
      ];
    }
  }

}