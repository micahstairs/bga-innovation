<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card112 extends AbstractCard
{

  // Basur Hoyuk Tokens
  //   - Draw and reveal a [4]. If you have a top card of the drawn card's color that comes before
  //     it in the alphabet, return the drawn card and all cards from your score pile.

  public function initialExecution()
  {
    $card = self::drawAndReveal(4);
    $topCard = self::getTopCardOfColor($card['color']);
    if ($topCard === null) {
      self::transferToHand($card);
    } else if ($this->game->comesAlphabeticallyBefore($topCard, $card)) {
      self::notifyAll(clienttranslate('In English alphabetical order, ${english_name_1} comes before ${english_name_2}.'), [
        'english_name_1' => self::getCardName($topCard),
        'english_name_2' => self::getCardName($card),
      ]);
      self::setMaxSteps(1);
    } else {
      self::notifyAll(clienttranslate('In English alphabetical order, ${english_name_1} does not come before ${english_name_2}.'), [
        'english_name_1' => self::getCardName($topCard),
        'english_name_2' => self::getCardName($card),
      ]);
      self::transferToHand($card);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => 'all',
      'location_from'  => 'revealed,score',
      'return_keyword' => true,
    ];
  }

  private function getCardName(array $card): string
  {
    return $this->game->getCardName($card['id']);
  }

}