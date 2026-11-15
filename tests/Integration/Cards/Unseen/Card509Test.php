<?php

namespace Integration\Cards\Unseen;

use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;
use Integration\Cards\BaseCardIntegrationTest;

class Card509Test extends BaseCardIntegrationTest
{
  // Cliffhanger:
  //   - Reveal a [4] in your safe. If it is: green, tuck it; purple, meld it; red, achieve it
  //     regardless of eligibility; yellow, score it; blue, draw a [5]. Otherwise, safeguard
  //     the top card of the [4] deck.

  // When the safe is over the current limit (allowed by the rules when the limit shrinks),
  // revealing a blue [4] from the safe must still put the card back in the safe: a reveal
  // never removes the card from where it was, and the blue case is not a new safeguard.
  public function test_overLimitSafe_unseen_fourthEdition_revealedBlueCardReturnsToSafe()
  {
    // Fill the safe to the default limit of 5, including a blue [4] (Experimentation)
    $blueFour = self::debugTransfer(35, 'safeguard');
    self::debugTransfer(1, 'safeguard');
    self::debugTransfer(2, 'safeguard');
    self::debugTransfer(3, 'safeguard');
    self::debugTransfer(4, 'safeguard');
    self::assertEquals(5, self::countCards(Locations::SAFE));

    // Splay blue left, dropping the safe limit to 4 and leaving the safe over limit
    self::debugTransfer(36, 'meld'); // Printing Press (blue [4])
    self::debugTransfer(25, 'meld'); // blue [3]
    self::debugSplay(Colors::BLUE, Directions::LEFT);
    self::assertEquals(5, self::countCards(Locations::SAFE)); // over-limit cards stay

    self::dogma();

    self::assertDogmaComplete();
    self::assertEquals('safe', self::getCard(intval($blueFour['id']))['location']);
    self::assertEquals(0, self::countCards(Locations::REVEALED));
  }
}
