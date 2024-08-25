<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card18 extends AbstractCard
{
  // Road Building:
  // - 3rd edition:
  //   - Meld one or two cards from your hand. If you melded two, you may transfer your top red
  //     card to another player board. If you do, transfer that player's top green card to your board.
  // - 4th edition:
  //   - Meld one or two cards from your hand. If you meld two, you may transfer your top red card
  //     to another player's board. If you do, meld that player's top green card.

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->minCards(1)->maxCards(2)->fromYourHand()->build();
    } else {
      return self::youMay()->choosePlayer(self::getOtherPlayerIds())->build();
    }
  }

  protected function getPromptForPlayerChoice(): array
  {
    return [
      "message_for_player" => self::isFourthEdition()
        ? clienttranslate('${You} may choose another player to transfer your top red card to, then meld his top green card')
        : clienttranslate('${You} may choose another player to transfer your top red card to, then transfer his top green card to your board'),
      "message_for_others" => clienttranslate('${player_name} may choose a player'),
    ];
  }

  public function handlePlayerChoice(int $opponentId)
  {
    self::transferToBoard(self::getTopCardOfColor(Colors::RED), $opponentId);
    $topGreenCard = self::getTopCardOfColor(Colors::GREEN, $opponentId);
    if (self::isFourthEdition()) {
      self::meld($topGreenCard);
    } else {
      self::transferToBoard($topGreenCard);
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() == 2 && self::getTopCardOfColor(Colors::RED)) {
      self::setMaxSteps(2);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}