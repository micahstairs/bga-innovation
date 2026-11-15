<?php

namespace Integration\Cards\Unseen;

use Integration\Cards\BaseCardIntegrationTest;

class Card569Test extends BaseCardIntegrationTest
{
  // Area 51:
  //   - You may splay your green cards up.
  //   - Choose to either draw an [11], or safeguard an available standard achievement.
  //   - Reveal one of your secrets, and super-execute it if it is your turn.

  public function test_whenRevealingASecretOnYourTurn_unseen_fourthEdition_putsItBackAfterSuperExecute()
  {
    $secret = self::debugTransfer(8, 'safeguard');
    self::assertEquals('safe', $secret['location']);

    self::dogma();
    if (self::getSpecialChoiceType() !== 'choose_from_list') {
      self::passIfNeeded();
    }
    self::assertEquals('choose_from_list', self::getSpecialChoiceType());
    self::chooseSpecial(1); // Draw an 11

    if (self::getCurrentStateName() === 'selectionMove') {
      self::selectCard(intval($secret['id']));
    }

    self::assertDogmaComplete();
    self::assertEquals('safe', self::getCard(intval($secret['id']))['location']);
  }
}
