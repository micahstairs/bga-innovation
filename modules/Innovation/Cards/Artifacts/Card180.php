<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card180 extends AbstractCard
{
  // Hansen Writing Ball
  //   - I COMPEL you to draw four [7]! Meld a blue card, then transfer all cards in your hand to my hand!  
  //   - Draw and reveal a [7]. If it has no [EFFICIENCY], tuck it and repeat this effect.

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::draw(7);
      self::draw(7);
      self::draw(7);
      self::draw(7);
      self::setMaxSteps(1);
    } else {
      while (true) {
        $card = self::drawAndReveal(7);
        if (!self::hasIcon($card, Icons::EFFICIENCY)) {
          self::tuck($card);
        } else {
          self::transferToHand($card);
          break;
        }
      }
      ;
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from'    => Locations::HAND,
      'meld_keyword'     => true,
      'color'            => [Colors::BLUE],
      'reveal_if_unable' => true,
    ];
  }

  public function afterInteraction()
  {
    foreach (self::getCards(Locations::HAND) as $card) {
      self::transferToHand($card, self::getLauncherId());
    }
  }

}