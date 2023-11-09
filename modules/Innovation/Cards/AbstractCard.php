<?php

namespace Innovation\Cards;

use Innovation\Cards\ExecutionState;
use Innovation\Enums\CardTypes;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;
use Innovation\Utils\Notifications;

/* Abstract class of all card implementations */
abstract class AbstractCard
{

  protected \Innovation $game;
  protected ExecutionState $state;
  protected Notifications $notifications;

  function __construct(\Innovation $game, ExecutionState $state)
  {
    $this->game = $game;
    $this->state = $state;
    $this->notifications = $game->notifications;
  }

  public function oneTimeSetup()
  {
    // Subclasses are expected to override this method if the card need to do any one-time setup before any player executes anything.
    // TODO(LATER): This method isn't actually called from the game logic yet. We need to wire it up if we want to use this method.
  }

  public abstract function initialExecution();

  public function getInteractionOptions(): array
  {
    // Subclasses are expected to override this method if the card has any interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented getInteractionOptions for card=$cardId");
  }

  public final function getSpecialChoicePrompt(): array
  {
    $choiceType = $this->game->innovationGameState->get('special_type_of_choice');
    switch ($choiceType) {
      // TODO:(LATER): Remove choose_yes_or_no.
      case 1: // choose_from_list
      case 7: // choose_yes_or_no
        return static::getPromptForListChoice();
      case 3: // choose_value
        return static::getPromptForValueChoice();
      case 4: // choose_color
        return static::getPromptForColorChoice();
      case 5: // choose_two_colors
        return static::getPromptForTwoColorChoice();
      case 8; // choose_type
        return static::getPromptForTypeChoice();
      case 9: // choose_three_colors
        return static::getPromptForThreeColorChoice();
      case 10: // choose_player
        return static::getPromptForPlayerChoice();
      case 11: // choose_non_negative_integer
        return static::getPromptForNumberChoice();
      case 12: // choose_icon_type
        return static::getPromptForIconChoice();
      default:
        $cardId = self::getThisCardId();
        throw new \RuntimeException("Unhandled value in getSpecialChoicePrompt: $choiceType for card=$cardId");
    }
  }

  protected function getPromptForListChoice(): array
  {
    // Subclasses are expected to override this method if the card has any interactions which use the 'choices' option.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented getPromptForListChoice for card=$cardId");
  }

  public function handleAbortedInteraction()
  {
    // Subclasses can optionally override this function if any extra handling needs to be done if
    // the interaction was aborted before it started.
  }

  public function executeCardTransfer(array $card): bool
  {
    // Subclasses can optionally override this function if the default card transfer needs to be overridden.
    // The override should return true if the transfer was handled, false otherwise.
    return false;
  }

  public function handleCardChoice(array $card)
  {
    // Subclasses can optionally override this function if any extra handling is needed after each individual card is chosen.
  }

  public function handleSpecialChoice(int $choice)
  {
    $choiceType = $this->game->innovationGameState->get('special_type_of_choice');
    switch ($choiceType) {
      // TODO:(LATER): Remove choose_yes_or_no.
      case 1: // choose_from_list
      case 7: // choose_yes_or_no
        return static::handleListChoice($choice);
      case 3: // choose_value
        return static::handleValueChoice($choice);
      case 4: // choose_color
        return static::handleColorChoice($choice);
      case 5: // choose_two_colors
        $colors = Arrays::decode($choice);
        return static::handleTwoColorChoice($colors[0], $colors[1]);
      case 8; // choose_type
        return static::handleTypeChoice($choice);
      case 9: // choose_two_colors
        $colors = Arrays::decode($choice);
        return static::handleThreeColorChoice($colors[0], $colors[1], $colors[2]);
      case 10: // choose_player
        return static::handlePlayerChoice($choice);
      case 11: // choose_non_negative_integer
        return static::handleNumberChoice($choice);
      case 12: // choose_icon_type
        return static::handleIconChoice($choice);
      default:
        $cardId = self::getThisCardId();
        throw new \RuntimeException("Unhandled value in handleSpecialChoice: $choiceType for card=$cardId");
    }
  }

  protected function handleListChoice(int $choice)
  {
    // Subclasses are expected to override this method if the card has any 'choose_from_list' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleListChoice for card=$cardId");
  }

  protected function handleValueChoice(int $value)
  {
    // Subclasses are expected to override this method if the card has any 'choose_value' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleValueChoice for card=$cardId");
  }

  protected function handleColorChoice(int $color)
  {
    // Subclasses are expected to override this method if the card has any 'choose_color' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleColorChoice for card=$cardId");
  }

  protected function handleTwoColorChoice(int $color1, int $color2)
  {
    // Subclasses are expected to override this method if the card has any 'choose_two_colors' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleTwoColorChoice for card=$cardId");
  }

  protected function handleThreeColorChoice(int $color1, int $color2, int $color3)
  {
    // Subclasses are expected to override this method if the card has any 'choose_three_colors' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleThreeColorChoice for card=$cardId");
  }

  protected function handleTypeChoice(int $type)
  {
    // Subclasses are expected to override this method if the card has any 'choose_type' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleTypeChoice for card=$cardId");
  }

  protected function handlePlayerChoice(int $playerId)
  {
    // Subclasses are expected to override this method if the card has any 'choose_player' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handlePlayerChoice for card=$cardId");
  }

  protected function handleNumberChoice(int $number)
  {
    // Subclasses are expected to override this method if the card has any 'choose_non_negative_integer' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleNumberChoice for card=$cardId");
  }

  protected function handleIconChoice(int $icon)
  {
    // Subclasses are expected to override this method if the card has any 'choose_icon_type' interactions.
    $cardId = self::getThisCardId();
    throw new \RuntimeException("Unimplemented handleIconChoice for card=$cardId");
  }

  public function afterInteraction()
  {
    // Subclasses can optionally override this function if any extra handling needs to be done
    // after an entire interaction (which could involve selecting multiple cards) is complete.
    //
    // NOTE: This function is not called if the interaction is aborted (e.g. safe is full).
    // See handleAbortedInteraction() for that.
  }

  public function hasPostExecutionLogic(): bool
  {
    // Subclasses are expected to override this method and return true if the card needs to do any more logic after executing a card.
    return false;
  }

  // EXECUTION HELPERS

  protected function isFirstOrThirdEdition(): bool
  {
    return $this->state->getEdition() <= 3;
  }

  protected function isFourthEdition(): bool
  {
    return $this->state->getEdition() === 4;
  }

  protected function getPlayerId(): int
  {
    return $this->state->getPlayerId();
  }

  protected function getLauncherId(): int
  {
    return $this->state->getLauncherId();
  }

  protected function isLauncher(): bool
  {
    return self::getPlayerId() === self::getLauncherId();
  }

  protected function isTheirTurn(int $playerId = null): bool
  {
    return self::coercePlayerId($playerId) == $this->game->getNestedCardState(0)['launcher_id'];
  }

  protected function getEffectNumber(): int
  {
    return $this->state->getEffectNumber();
  }

  protected function isDemand(): bool
  {
    return $this->state->isDemand();
  }

  protected function isCompel(): bool
  {
    return $this->state->isCompel();
  }

  protected function isNonDemand(): bool
  {
    return $this->state->isNonDemand();
  }

  protected function isFirstNonDemand(): bool
  {
    return self::isNonDemand() && self::getEffectNumber() === 1;
  }

  protected function isSecondNonDemand(): bool
  {
    return self::isNonDemand() && self::getEffectNumber() === 2;
  }

  protected function isThirdNonDemand(): bool
  {
    return self::isNonDemand() && self::getEffectNumber() === 3;
  }

  protected function isEcho(): bool
  {
    return $this->state->isEcho();
  }

  protected function isFirstInteraction(): int
  {
    return $this->state->getCurrentStep() === 1;
  }

  protected function isSecondInteraction(): int
  {
    return $this->state->getCurrentStep() === 2;
  }

  protected function isThirdInteraction(): int
  {
    return $this->state->getCurrentStep() === 3;
  }

  protected function isFourthInteraction(): int
  {
    return $this->state->getCurrentStep() === 4;
  }

  protected function setNextStep(int $step)
  {
    $this->state->setNextStep($step);
  }

  protected function getMaxSteps(): int
  {
    return $this->state->getMaxSteps();
  }

  protected function setMaxSteps(int $steps)
  {
    $this->state->setMaxSteps($steps);
  }

  protected function getNumChosen(): int
  {
    return $this->state->getNumChosen();
  }

  protected function isPostExecution(): int
  {
    return $this->game->getCurrentNestedCardState()['post_execution_index'] > 0;
  }

  protected function selfExecute($card): bool
  {
    if (!$card) {
      return false;
    }
    return $this->game->selfExecute($card);
  }

  protected function fullyExecute($card)
  {
    if ($card) {
      $this->game->fullyExecute($card);
    }
  }

  // CARD TRANSFER HELPERS

  protected function draw(int $age, int $playerId = null)
  {
    return $this->game->executeDraw(self::coercePlayerId($playerId), $age);
  }

  protected function drawType(int $age, int $type, int $playerId = null)
  {
    return $this->game->executeDraw(self::coercePlayerId($playerId), $age, 'hand', /*bottom_to=*/false, /*type=*/$type);
  }

  protected function transferToHand(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'hand');
  }

  protected function score(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->scoreCard($card, self::coercePlayerId($playerId));
  }

  protected function scoreCards(array $cards, int $playerId = null): bool
  {
    return $this->game->bulkTransferCards($cards, self::coercePlayerId($playerId), 'score', ['score_keyword' => true]);
  }

  protected function transferToScorePile(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'score');
  }

  protected function transferCardsToScorePile(array $cards, int $playerId = null): bool
  {
    return $this->game->bulkTransferCards($cards, self::coercePlayerId($playerId), 'score');
  }

  protected function meld(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->meldCard($card, self::coercePlayerId($playerId));
  }

  protected function transferToBoard(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'board');
  }

  protected function tuck(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->tuckCard($card, self::coercePlayerId($playerId));
  }

  protected function reveal(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'revealed');
  }

  protected function safeguard(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->safeguardCard($card, self::coercePlayerId($playerId));
  }

  protected function putBackInSafe(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->putCardBackInSafe($card, self::coercePlayerId($playerId));
  }

  protected function achieve(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), "achievements", ["achieve_keyword" => true]);
  }

  protected function claim(int $cardId, int $playerId = null): ?array {
    if (!self::isSpecialAchievement(self::getCard($cardId))) {
      return null;
    }
    return $this->game->claimSpecialAchievement(self::coercePlayerId($playerId), $cardId);
  }

  protected function achieveIfEligible(?array $card, int $playerId = null)
  {
    if (self::isEligibleForAchieving($card, self::coercePlayerId($playerId))) {
      return self::achieve($card);
    }
    return $card;
  }

  protected function isEligibleForAchieving(?array $card, int $playerId = null): bool
  {
    if (!$card) {
      return false;
    }
    return in_array($card['age'], $this->game->getClaimableValuesIgnoringAvailability(self::coercePlayerId($playerId)));
  }

  protected function transferToAchievements(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), "achievements");
  }

  protected function return (?array $card): ?array
  {
    if (!$card) {
      return null;
    }
    return $this->game->returnCard($card);
  }

  protected function placeOnTopOfDeck(?array $card): ?array
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, 0, 'deck', ['bottom_to' => false]);
  }

  protected function junkCards(array $cards): bool
  {
    return $this->game->bulkTransferCards($cards, 0, Locations::JUNK);
  }

  protected function junk(?array $card): ?array
  {
    if (!$card) {
      return null;
    }
    return $this->game->junkCard($card);
  }

  protected function remove(?array $card): ?array
  {
    if (!$card) {
      return null;
    }
    return $this->game->removeCard($card);
  }

  protected function removeCards(array $cards): bool
  {
    return $this->game->bulkTransferCards($cards, 0, Locations::REMOVED);
  }

  protected function foreshadow(?array $card, $callbackIfFull = null, int $playerId = null): ?array
  {
    if (!$card) {
      return null;
    }
    $foreshadowedCard = $this->game->foreshadowCard($card, self::coercePlayerId($playerId));
    if ($foreshadowedCard) {
      return $foreshadowedCard;
    }
    if ($callbackIfFull === null) {
      return null;
    }
    return $callbackIfFull($card);
  }

  protected function transferToForecast(?array $card, $callbackIfFull = null, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    $transferredCard = $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), Locations::FORECAST);
    if ($transferredCard) {
      return $transferredCard;
    }
    if ($callbackIfFull === null) {
      return null;
    }
    return $callbackIfFull($card);
  }

  protected function drawAndMeld(int $age, int $playerId = null): ?array
  {
    return $this->game->executeDrawAndMeld(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndMeldType(int $age, int $type, int $playerId = null): ?array
  {
    return $this->game->executeDrawAndMeld(self::coercePlayerId($playerId), $age, $type);
  }

  protected function drawAndTuck(int $age, int $playerId = null): ?array
  {
    return $this->game->executeDrawAndTuck(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndScore(int $age, int $playerId = null): ?array
  {
    return $this->game->executeDrawAndScore(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndSafeguard(int $age, int $playerId = null): ?array
  {
    $playerId = self::coercePlayerId($playerId);
    if (self::isFourthEdition() && self::countCards('safe') >= $this->game->getForecastAndSafeLimit($playerId)) {
      $card = self::draw($age, $playerId);
      $this->notifications->notifyLocationFull(clienttranslate('safe'), $playerId);
    } else {
      $card = $this->game->executeDraw($playerId, $age, 'safe');
    }
    return $card;
  }

  protected function drawAndForeshadow(int $age, int $playerId = null): ?array
  {
    $playerId = self::coercePlayerId($playerId);
    if (self::isFourthEdition() && self::countCards('forecast') >= $this->game->getForecastAndSafeLimit($playerId)) {
      $card = self::draw($age, $playerId);
      $this->notifications->notifyLocationFull(clienttranslate('forecast'), $playerId);
    } else {
      $card = $this->game->executeDraw($playerId, $age, 'forecast');
    }
    return $card;
  }

  protected function drawAndReveal(int $age, int $playerId = null): ?array
  {
    return $this->game->executeDrawAndReveal(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndRevealType(int $age, int $type, int $playerId = null)
  {
    return $this->game->executeDrawAndReveal(self::coercePlayerId($playerId), $age, $type);
  }

  protected function countVisibleCardsInStack(int $color, int $playerId = null): int
  {
    return $this->game->countVisibleCards(self::coercePlayerId($playerId), $color);
  }

  protected function getTopCardOfColor(int $color, int $playerId = null): ?array
  {
    return $this->game->getTopCardOnBoard(self::coercePlayerId($playerId), $color);
  }

  protected function getTopCards(int $playerId = null): array
  {
    $cards = $this->game->getTopCardsOnBoard(self::coercePlayerId($playerId));
    if ($cards === null) {
      return [];
    }
    return $cards;
  }

  protected function getBottomCardOfColor(int $color, int $playerId = null): ?array
  {
    return $this->game->getBottomCardOnBoard(self::coercePlayerId($playerId), $color);
  }

  protected function filterByColor(array $cards, array $colors): array
  {
    return array_values(array_filter($cards, function ($card) use ($colors) {
      return in_array($card['color'], $colors);
    }));
  }

  protected function filterByType(array $cards, array $types): array
  {
    return array_values(array_filter($cards, function ($card) use ($types) {
      return in_array($card['type'], $types);
    }));
  }

  protected function getValues(array $cards): array
  {
    return array_map(function ($card) {
      return AbstractCard::getValue($card);
    }, $cards);
  }

  public function getRepeatedValues(array $cards): array
  {
    $values = self::getValues($cards);
    return Arrays::getRepeatedValues($values);
  }

  public function getColorsMatchingValues(array $cards, array $values): array {
    $colors = [];
    foreach ($cards as $card) {
      if (in_array(self::getValue($card), $values)) {
        $colors[] = $card['color'];
      }
    }
    return $colors;
  }

  protected function getMinValue(array $cards)
  {
    if (empty($cards)) {
      return 0;
    }
    return min(array_map(function ($card) {
      return AbstractCard::getValue($card);
    }, $cards));
  }

  protected function getMaxValue(array $cards)
  {
    if (empty($cards)) {
      return 0;
    }
    return max(array_map(function ($card) {
      return AbstractCard::getValue($card);
    }, $cards));
  }

  protected function getRevealedCard(int $playerId = null): ?array
  {
    $cards = self::getCards('revealed');
    if (count($cards) === 0) {
      return null;
    }
    return $cards[0];
  }

  protected function getCards(string $location, int $playerId = null): array
  {
    return $this->game->getCardsInLocation(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function getAvailableStandardAchievements(): array
  {
    $achievements = $this->game->getCardsInLocation(0, Locations::ACHIEVEMENTS);
    return array_values(array_filter($achievements, function ($card) {
      return self::isValuedCard($card);
    }));
  }

  // BULK CARD HELPERS

  protected function junkBaseDeck(int $age): bool
  {
    return $this->game->junkBaseDeck($age);
  }

  protected function revealHand(int $playerId = null): void
  {
    $this->game->revealLocation(self::coercePlayerId($playerId), 'hand');
  }

  protected function revealScorePile(int $playerId = null): void
  {
    $this->game->revealLocation(self::coercePlayerId($playerId), 'score');
  }

  protected function revealForecast(int $playerId = null): void
  {
    $this->game->revealLocation(self::coercePlayerId($playerId), 'forecast');
  }

  // CARD ACCESSOR HELPERS

  protected function getMinValueInLocation(string $location, int $playerId = null): int
  {
    return $this->game->getMinOrMaxAgeInLocation(self::coercePlayerIdUsingLocation($playerId, $location), $location, 'MIN');
  }

  protected function getMaxValueInLocation(string $location, int $playerId = null): int
  {
    return $this->game->getMinOrMaxAgeInLocation(self::coercePlayerIdUsingLocation($playerId, $location), $location, 'MAX');
  }

  protected function hasIcon(?array $card, int $icon): bool
  {
    if (!$card) {
      return false;
    }
    return $this->game->hasRessource($card, $icon);
  }

  protected function getBonuses(int $playerId = null): array
  {
    return $this->game->getVisibleBonusesOnBoard(self::coercePlayerId($playerId));
  }

  protected static function isValuedCard(?array $card): bool
  {
    return $card && $card['age'] !== null;
  }

  protected static function isSpecialAchievement(?array $card): bool
  {
    return $card && $card['age'] === null && $card['id'] < 1000;
  }

  protected static function isBlue(?array $card): bool
  {
    return $card && $card['color'] == Colors::BLUE;
  }

  protected static function isRed(?array $card): bool
  {
    return $card && $card['color'] == Colors::RED;
  }

  protected static function isGreen(?array $card): bool
  {
    return $card && $card['color'] == Colors::GREEN;
  }

  protected static function isYellow(?array $card): bool
  {
    return $card && $card['color'] == Colors::YELLOW;
  }

  protected static function isPurple(?array $card): bool
  {
    return $card && $card['color'] == Colors::PURPLE;
  }

  protected static function getValue(?array $card): int
  {
    if (!$card) {
      return 0;
    }
    return Locations::isFaceup($card['location']) ? intval($card['faceup_age']) : intval($card['age']);
  }

  protected function getCard(int $cardId): ?array
  {
    return $this->game->getCardInfo($cardId);
  }

  // SPLAY HELPERS

  protected function splay(int $color, int $splayDirection, int $targetPlayerId = null, int $triggeringPlayerId = null)
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    $this->game->splay($triggeringPlayerId, $targetPlayerId, $color, $splayDirection);
  }

  protected function unsplay(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null): bool
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    return $this->game->unsplay($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function splayLeft(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null): bool
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    return $this->game->splayLeft($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function splayRight(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null): bool
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    return $this->game->splayRight($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function splayUp(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null): bool
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    return $this->game->splayUp($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function splayAslant(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null): bool
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    return $this->game->splayAslant($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function getSplayDirection(int $color, int $playerId = null): int
  {
    return intval($this->game->getCurrentSplayDirection(self::coercePlayerId($playerId), $color));
  }

  protected function isSplayed(int $color, int $playerId = null): int
  {
    return self::getSplayDirection($color, self::coercePlayerId($playerId)) > 0;
  }

  // SELECTION HELPERS

  protected function getLastSelectedCard(): ?array
  {
    return $this->game->getCardInfo(self::getLastSelectedId());
  }

  protected function getLastSelectedId(): int
  {
    return $this->game->innovationGameState->get('id_last_selected');
  }

  protected function getLastSelectedAge(): int
  {
    return $this->game->innovationGameState->get('age_last_selected');
  }

  protected function getLastSelectedFaceUpAge(): int
  {
    return self::getLastSelectedCard()['faceup_age'];
  }

  protected function getLastSelectedColor(): int
  {
    return $this->game->innovationGameState->get('color_last_selected');
  }

  protected function getLastSelectedOwner(): int
  {
    return $this->game->innovationGameState->get('owner_last_selected');
  }

  // AUXILARY VALUE HELPERS

  protected function getAuxiliaryValue(): int
  {
    return $this->game->getAuxiliaryValue();
  }

  protected function setAuxiliaryValue(int $value)
  {
    return $this->game->setAuxiliaryValue($value);
  }

  protected function incrementAuxiliaryValue(int $value = 1): int
  {
    $newValue = self::getAuxiliaryValue() + $value;
    self::setAuxiliaryValue($newValue);
    return $newValue;
  }

  protected function decrementAuxiliaryValue(int $value = 1): int
  {
    return self::incrementAuxiliaryValue(-$value);
  }

  protected function getAuxiliaryValue2(): int
  {
    return $this->game->getAuxiliaryValue2();
  }

  protected function setAuxiliaryValue2(int $value)
  {
    return $this->game->setAuxiliaryValue2($value);
  }

  protected function incrementAuxiliaryValue2(int $value = 1): int
  {
    $newValue = self::getAuxiliaryValue2() + $value;
    self::setAuxiliaryValue2($newValue);
    return $newValue;
  }

  protected function decrementAuxiliaryValue2(int $value = 1): int
  {
    return self::incrementAuxiliaryValue2(-$value);
  }

  protected function setAuxiliaryArray(array $array)
  {
    return $this->game->setAuxiliaryArray($array);
  }

  protected function addToAuxiliaryArray(int $value): array
  {
    $array = array_merge(self::getAuxiliaryArray(), [$value]);
    self::setAuxiliaryArray($array);
    return $array;
  }

  protected function removeFromAuxiliaryArray(int $value): array
  {
    $array = Arrays::removeElement(self::getAuxiliaryArray(), $value);
    self::setAuxiliaryArray($array);
    return $array;
  }

  protected function getAuxiliaryArray(): array
  {
    return $this->game->getAuxiliaryArray();
  }

  protected function setActionScopedAuxiliaryArray($array, $playerId = 0): void
  {
    $this->game->setActionScopedAuxiliaryArray(self::getThisCardId(), $playerId, $array);
  }

  protected function addToActionScopedAuxiliaryArray(int $value, $playerId = 0): array
  {
    $array = array_merge(self::getActionScopedAuxiliaryArray($playerId), [$value]);
    self::setActionScopedAuxiliaryArray($array, $playerId);
    return $array;
  }

  protected function removeFromActionScopedAuxiliaryArray(int $value, $playerId = 0): array
  {
    $array = Arrays::removeElement(self::getActionScopedAuxiliaryArray($playerId), $value);
    self::setActionScopedAuxiliaryArray($array, $playerId);
    return $array;
  }

  protected function getActionScopedAuxiliaryArray($playerId = 0): array
  {
    return $this->game->getActionScopedAuxiliaryArray(self::getThisCardId(), $playerId);
  }

  // PROMPT MESSAGE HELPERS

  protected function getPromptForColorChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a color'),
        "message_for_others" => clienttranslate('${player_name} may choose a color'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose a color'),
        "message_for_others" => clienttranslate('${player_name} must choose a color'),
      ];
    }
  }

  protected function getPromptForTwoColorChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose two colors'),
        "message_for_others" => clienttranslate('${player_name} may choose two colors'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose two colors'),
        "message_for_others" => clienttranslate('${player_name} must choose two colors'),
      ];
    }
  }

  protected function getPromptForThreeColorChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose three colors'),
        "message_for_others" => clienttranslate('${player_name} may choose three colors'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose three colors'),
        "message_for_others" => clienttranslate('${player_name} must choose three colors'),
      ];
    }
  }

  protected function getPromptForIconChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a icon'),
        "message_for_others" => clienttranslate('${player_name} may choose a icon'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose an icon'),
        "message_for_others" => clienttranslate('${player_name} must choose an icon'),
      ];
    }
  }

  protected function getPromptForValueChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a value'),
        "message_for_others" => clienttranslate('${player_name} may choose a value'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose a value'),
        "message_for_others" => clienttranslate('${player_name} must choose a value'),
      ];
    }
  }

  protected function getPromptForNumberChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a number'),
        "message_for_others" => clienttranslate('${player_name} may choose a number'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose an number'),
        "message_for_others" => clienttranslate('${player_name} must choose an number'),
      ];
    }
  }

  protected function getPromptForPlayerChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a player'),
        "message_for_others" => clienttranslate('${player_name} may choose a player'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose a player'),
        "message_for_others" => clienttranslate('${player_name} must choose a player'),
      ];
    }
  }

  protected function getPromptForTypeChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a type'),
        "message_for_others" => clienttranslate('${player_name} may choose a type'),
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('Choose a type'),
        "message_for_others" => clienttranslate('${player_name} must choose a type'),
      ];
    }
  }

  protected function buildPromptFromList(array $choiceMap): array
  {
    $options = self::getOptionsForChoiceFromList($choiceMap);
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('${You} may make a choice'),
        "message_for_others" => clienttranslate('${player_name} may make a choice among the possibilities offered by the card'),
        "options"            => $options,
      ];
    } else {
      return [
        "message_for_player" => clienttranslate('${You} must make a choice'),
        "message_for_others" => clienttranslate('${player_name} must make a choice among the possibilities offered by the card'),
        "options"            => $options,
      ];
    }
  }

  protected function getOptionsForChoiceFromList(array $choiceMap): array
  {
    $validChoices = $this->game->innovationGameState->getAsArray('choice_array');
    $options = [];
    foreach ($choiceMap as $value => $textOrArray) {
      if (!in_array($value, $validChoices)) {
        continue;
      }
      $text = is_array($textOrArray) ? reset($textOrArray) : $textOrArray;
      $args = is_array($textOrArray) ? array_slice($textOrArray, 1) : [];
      $options[] = array_merge($args, [
        'value' => $value,
        'text'  => $text,
      ]);
    }
    return $options;
  }

  // PLAYER HELPERS

  protected function win(int $playerId = null): void
  {
    // Abort if the game is in a special debug mode which prevents the game from ending
    if ($this->game->innovationGameState->get('debug_mode') == 2) {
      return;
    }

    $this->game->innovationGameState->set('winner_by_dogma', self::coercePlayerId($playerId));
    throw new \EndOfGame();
  }

  protected function lose(int $playerId = null): void
  {
    // Abort if the game is in a special debug mode which prevents the game from ending
    if ($this->game->innovationGameState->get('debug_mode') == 2) {
      return;
    }

    $playerId = self::coercePlayerId($playerId);

    // The entire team loses if one player loses 
    if ($this->game->isTeamGame()) {
      $teammateId = $this->game->getPlayerTeammate($playerId);
      $this->notifications->notifyTeamLoses($playerId, $teammateId);
      $arbitraryOpponentId = self::getRemainingPlayerIdsAfterEliminating([$playerId, $teammateId])[0];
      $this->game->innovationGameState->set('winner_by_dogma', $arbitraryOpponentId);
      throw new \EndOfGame();
    }

    // Declare a winner if there will only be one player remaining
    $remainingPlayerIds = self::getRemainingPlayerIdsAfterEliminating([$playerId]);
    if (count($remainingPlayerIds) === 1) {
      $this->notifications->notifyPlayerLoses($playerId);
      $this->game->innovationGameState->set('winner_by_dogma', $remainingPlayerIds[0]);
      throw new \EndOfGame();
    }

    // Otherwise, eliminate the player
    $this->notifications->notifyPlayerLoses($playerId);
    $this->game->eliminatePlayer($playerId);
    $args = ['player_to_remove' => $playerId];
    $this->game->notifyPlayer($playerId, 'removedPlayer', '', $args);
    $this->game->notifyAllPlayersBut($playerId, 'removedPlayer', '', $args);

    // Junk all cards that the player had
    self::junkAllCards($playerId);

    // Even if no cards are removed we will still mark that change has occurred because a player has been eliminated.
    $this->game->recordThatChangeOccurred();
  }

  protected function junkAllCards(int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);

    $cards = [];
    // TODO(4E): Make sure the museum cards themselves are junked, not just the artifacts in the museums.
    foreach (Locations::PLAYER_LOCATIONS as $location) {
      $cards = array_merge($cards, self::getCards($location, $playerId));
    }

    if (!$cards) {
      return;
    }

    $targetLocation = self::isFirstOrThirdEdition() ? Locations::REMOVED : Locations::JUNK;
    $this->game->bulkTransferCards($cards, 0, $targetLocation, ['player_already_lost' => true]);

    if (self::isFirstOrThirdEdition()) {
      self::notifyPlayer(clienttranslate('All ${your} cards were removed from the game.'));
      self::notifyOthers(clienttranslate('All ${player_name}\'s cards were removed from the game.'));
    } else {
      self::notifyPlayer(clienttranslate('All ${your} cards were junked.'));
      self::notifyOthers(clienttranslate('All ${player_name}\'s cards were junked.'));
    }
  }

  private function getRemainingPlayerIdsAfterEliminating(array $idsToEliminate): array
  {
    return array_values(array_diff($this->game->getAllActivePlayerIds(), $idsToEliminate));
  }

  protected function getOpponentIds(int $playerId = null): array
  {
    return $this->game->getActiveOpponentIds(self::coercePlayerId($playerId));
  }

  protected function getOtherPlayerIds(int $playerId = null): array
  {
    return $this->game->getOtherActivePlayerIds(self::coercePlayerId($playerId));
  }

  protected function getPlayerIds(): array
  {
    return $this->game->getAllActivePlayerIds();
  }

  // MISCELLANEOUS HELPERS

  protected function getBaseDeckCount(int $age): int
  {
    return self::getBaseDecks()[$age];
  }

  protected function countCards(string $location, int $playerId = null): int
  {
    return $this->game->countCardsInLocation(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function hasCards(string $location, int $playerId = null): int
  {
    return self::countCards($location, $playerId) > 0;
  }

  protected function getUniqueValues(string $location, int $playerId = null): array
  {
    $values = [];
    foreach (self::countCardsKeyedByValue($location, $playerId) as $value => $count) {
      if ($count > 0) {
        $values[] = $value;
      }
    }
    return $values;
  }

  protected function getUniqueColors(string $location, int $playerId = null): array
  {
    $colors = [];
    foreach (self::countCardsKeyedByColor($location, $playerId) as $color => $count) {
      if ($count > 0) {
        $colors[] = $color;
      }
    }
    return $colors;
  }

  protected function getCardsKeyedByValue(string $location, int $playerId = null): array
  {
    return $this->game->getCardsInLocationKeyedByAge(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function countCardsKeyedByValue(string $location, int $playerId = null): array
  {
    // TODO(LATER): Make this return an array of ints.
    return $this->game->countCardsInLocationKeyedByAge(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function getBaseDecks(): array
  {
    return $this->game->countCardsInLocationKeyedByAge( /*owner=*/0, 'deck', CardTypes::BASE);
  }

  protected function getCardsKeyedByColor(string $location, int $playerId = null): array
  {
    return $this->game->getCardsInLocationKeyedByColor(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function getStack(int $color, int $playerId = null): array
  {
    $playerId = self::coercePlayerIdUsingLocation($playerId, Locations::BOARD);
    return self::getCardsKeyedByColor(Locations::BOARD, $playerId)[$color];
  }

  protected function countCardsKeyedByColor(string $location, int $playerId = null): array
  {
    // TODO(LATER): Make this return an array of ints.
    return $this->game->countCardsInLocationKeyedByColor(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function getScore(int $playerId = null): int
  {
    return $this->game->getPlayerScore(self::coercePlayerId($playerId));
  }

  protected function getStandardIconCount(int $icon, int $playerId = null): int
  {
    return $this->game->getPlayerSingleRessourceCount(self::coercePlayerId($playerId), $icon);
  }

  protected function getStandardIconCounts(int $playerId = null): array
  {
    return $this->game->getPlayerResourceCounts(self::coercePlayerId($playerId));
  }

  protected function getStandardIconCountsOfAllPlayers(): array
  {
    $countsByPlayer = [];
    foreach (self::getPlayerIds() as $playerId) {
      $countsByPlayer[$playerId] = self::getStandardIconCounts($playerId);
    }
    return $countsByPlayer;
  }

  protected function getAllIconCounts(int $playerId = null): array
  {
    $icons = [];
    $cardsByColor = self::getCardsKeyedByColor('board', $playerId);
    foreach ($cardsByColor as $stack) {
      if (count($stack) > 1) {
        $spots = self::getVisibleSpotsOnBuriedCard(intval($stack[0]['splay_direction']));
      }
      foreach ($stack as $card) {
        if ($card['position'] == count($stack) - 1) {
          // All icons are visible on the top card in the stack
          $icons = array_merge($icons, self::getIcons($card, [1, 2, 3, 4, 5, 6], true));
        } else {
          $icons = array_merge($icons, self::getIcons($card, $spots, true));
        }
      }
    }
    // Convert array of icons to array of counts
    return array_count_values($icons);
  }

  protected function getAllIconCountsInStack(int $color, int $playerId = null): array
  {
    $icons = [];
    $stack = self::getStack($color, $playerId);
    if (count($stack) > 1) {
      $spots = self::getVisibleSpotsOnBuriedCard(intval($stack[0]['splay_direction']));
    }
    foreach ($stack as $card) {
      if ($card['position'] == count($stack) - 1) {
        // All icons are visible on the top card in the stack
        $icons = array_merge($icons, self::getIcons($card, [1, 2, 3, 4, 5, 6], true));
      } else {
        $icons = array_merge($icons, self::getIcons($card, $spots, true));
      }
    }
    // Convert array of icons to array of counts
    return array_count_values($icons);
  }

  protected function getIconCountInStack(int $color, int $icon, int $playerId = null): int
  {
    $countsByIcon = self::getAllIconCountsInStack($color);
    if (key_exists($icon, $countsByIcon)) {
      return $countsByIcon[$icon];
    }
    return 0;
  }

  protected function hasIconInCommon(array $card1, array $card2): bool
  {
    return count(array_intersect(self::getIcons($card1), self::getIcons($card2))) > 0;
  }

  protected function getVisibleSpotsOnBuriedCard(int $splayDirection): array
  {
    switch ($splayDirection) {
      case Directions::LEFT:
        return [4, 5];
      case Directions::RIGHT:
        return [1, 2];
      case Directions::UP:
        return [2, 3, 4];
      case Directions::ASLANT:
        return [1, 2, 3, 4];
      default:
        return [];
    }
  }

  protected function getIcons(array $card, array $spots = [1, 2, 3, 4, 5, 6], $includeEchoEffects = false): array
  {
    $icons = [];
    foreach ($spots as $spot) {
      $icon = $card['spot_' . $spot];
      // Echo effects don't actually count as an icon type
      if ($icon && ($includeEchoEffects || $icon != Icons::ECHO_EFFECT)) {
        // Bonus icons are normalized to 100 since they are considered to be the same icon type
        $icons[] = min($icon, 100);
      }
    }
    return $icons;
  }

  protected function getBonusIcon(array $card): int
  {
    $bonusIcons = $this->game->getBonusIcons($card);
    if (count($bonusIcons) > 0) {
      // Whenever there is more than one bonus, they are always the same value.
      return $bonusIcons[0];
    }
    return 0;
  }

  protected function hasBonusIcon(array $card): int
  {
    return self::getBonusIcon($card) > 0;
  }

  protected function wasForeseen(): bool
  {
    // NOTE: The phrase "was foreseen" didn't appear on cards until the fourth edition.
    return self::isFourthEdition() && $this->game->innovationGameState->get('foreseen_card_id') == self::getThisCardId();
  }

  // NOTIFICATION HELPERS

  protected function notifyAll($log, array $args = [])
  {
    $this->game->notifyGeneralInfo($log, $args);
  }

  protected function notifyPlayer($log, array $args = [], int $playerId = null)
  {
    $defaultArgs = ['You' => 'You', 'you' => 'you', 'Your' => 'Your', 'your' => 'your'];
    $this->game->notifyPlayer(self::coercePlayerId($playerId), 'log', $log, array_merge($defaultArgs, $args));
  }

  protected function notifyOthers($log, array $args = [], int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $defaultArgs = ['player_name' => self::renderPlayerName($playerId)];
    $this->game->notifyAllPlayersBut($playerId, 'log', $log, array_merge($defaultArgs, $args));
  }

  protected function notifyTeam($log, array $args = [], int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $teammateId = $this->game->getPlayerTeammate($playerId);
    self::notifyPlayer($log, $args, $playerId);
    self::notifyPlayer($log, $args, $teammateId);
  }

  protected function notifyOtherTeam($log, array $args = [], int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $teammateId = $this->game->getPlayerTeammate($playerId);
    foreach ($this->game->getAllPlayerIds() as $id) {
      if ($id != $playerId && $id != $teammateId) {
        self::notifyPlayer($log, $args, $id);
      }
    }
  }

  public function notifyValueChoice(int $value, int $playerId = null)
  {
    $args = ['age' => $this->notifications->renderValue($value)];
    self::notifyPlayer(clienttranslate('${You} choose ${age}.'), $args, $playerId);
    self::notifyOthers(clienttranslate('${player_name} chooses ${age}.'), $args, $playerId);
  }

  public function notifyColorChoice(int $color, int $playerId = null)
  {
    $args = ['i18n' => ['color'], 'color' => Colors::render($color)];
    self::notifyPlayer(clienttranslate('${You} choose ${color}.'), $args, $playerId);
    self::notifyOthers(clienttranslate('${player_name} chooses ${color}.'), $args, $playerId);
  }

  public function notifyTwoColorChoice(int $color1, int $color2, int $playerId = null)
  {
    $args = ['i18n' => ['color_1', 'color_2'], 'color_1' => Colors::render($color1), 'color_2' => Colors::render($color2)];
    self::notifyPlayer(clienttranslate('${You} choose ${color_1} and ${color_2}.'), $args, $playerId);
    self::notifyOthers(clienttranslate('${player_name} chooses ${color_1} and ${color_2}.'), $args, $playerId);
  }

  public function notifyIconChoice(int $icon, int $playerId = null)
  {
    $args = ['icon' => Icons::render($icon)];
    self::notifyPlayer(clienttranslate('${You} choose ${icon}.'), $args, $playerId);
    self::notifyOthers(clienttranslate('${player_name} chooses ${icon}.'), $args, $playerId);
  }

  public function notifyPlayerChoice(int $chosenPlayerId, int $playerId = null)
  {
    $args = ['player_choice' => $this->notifications->renderPlayerName($chosenPlayerId)];
    self::notifyPlayer(clienttranslate('${You} choose the player ${player_choice}.'), $args, $playerId);
    self::notifyOthers(clienttranslate('${player_name} chooses the player ${player_choice}.'), $args, $playerId);
  }

  public function renderValue(int $value): string
  {
    return $this->notifications->renderValue($value);
  }

  public function renderValueWithType(int $value, int $type): string
  {
    return $this->notifications->renderValueWithType($value, $type);
  }

  public function renderNumber(int $number): string
  {
    return $this->game->renderNumber($number);
  }

  public function renderPlayerName(int $playerId = null): string
  {
    return $this->game->renderPlayerName(self::coercePlayerId($playerId));
  }

// PRIVATE HELPERS

  private function canPass(): bool
  {
    return $this->game->innovationGameState->get('can_pass');
  }

  private function getThisCardId(): string
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5));
  }

  private function getThisCard(): array
  {
    return self::getCard(self::getThisCardId());
  }

  private function coercePlayerId(?int $playerId): int
  {
    if ($playerId === null) {
      return $this->state->getPlayerId();
    }
    return $playerId;
  }

  private function coercePlayerIdUsingLocation(?int $playerId, string $location): int
  {
    if (in_array($location, [Locations::DECK, Locations::JUNK, Locations::RELICS, Locations::AVAILABLE_ACHIEVEMENTS])) {
      return 0;
    }
    return self::coercePlayerId($playerId);
  }

}