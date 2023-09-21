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
    throw new \RuntimeException("Unimplemented getInteractionOptions");
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
        throw new \RuntimeException("Unhandled value in getSpecialChoicePrompt: " . $choiceType);
    }
  }

  protected function getPromptForListChoice(): array
  {
    // Subclasses are expected to override this method if the card has any interactions which use the 'choices' option.
    throw new \RuntimeException("Unimplemented getPromptForListChoice");
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
        $colors = Arrays::getValueAsArray($choice);
        return static::handleTwoColorChoice($colors[0], $colors[1]);
      case 8; // choose_type
        return static::handleTypeChoice($choice);
      case 9: // choose_two_colors
        $colors = Arrays::getValueAsArray($choice);
        return static::handleThreeColorChoice($colors[0], $colors[1], $colors[2]);
      case 10: // choose_player
        return static::handlePlayerChoice($choice);
      case 11: // choose_non_negative_integer
        return static::handleNumberChoice($choice);
      case 12: // choose_icon_type
        return static::handleIconChoice($choice);
      default:
        throw new \RuntimeException("Unhandled value in handleSpecialChoice: " . $choiceType);
    }
  }

  protected function handleListChoice(int $choice)
  {
    // Subclasses are expected to override this method if the card has any 'choose_from_list' interactions.
    throw new \RuntimeException("Unimplemented handleListChoice");
  }

  protected function handleValueChoice(int $value)
  {
    // Subclasses are expected to override this method if the card has any 'choose_value' interactions.
    throw new \RuntimeException("Unimplemented handleValueChoice");
  }

  protected function handleColorChoice(int $color)
  {
    // Subclasses are expected to override this method if the card has any 'choose_color' interactions.
    throw new \RuntimeException("Unimplemented handleColorChoice");
  }

  protected function handleTwoColorChoice(int $color1, int $color2)
  {
    // Subclasses are expected to override this method if the card has any 'choose_two_colors' interactions.
    throw new \RuntimeException("Unimplemented handleTwoColorChoice");
  }

  protected function handleThreeColorChoice(int $color1, int $color2, int $color3)
  {
    // Subclasses are expected to override this method if the card has any 'choose_three_colors' interactions.
    throw new \RuntimeException("Unimplemented handleThreeColorChoice");
  }

  protected function handleTypeChoice(int $type)
  {
    // Subclasses are expected to override this method if the card has any 'choose_type' interactions.
    throw new \RuntimeException("Unimplemented handleTypeChoice");
  }

  protected function handlePlayerChoice(int $playerId)
  {
    // Subclasses are expected to override this method if the card has any 'choose_player' interactions.
    throw new \RuntimeException("Unimplemented handlePlayerChoice");
  }

  protected function handleNumberChoice(int $number)
  {
    // Subclasses are expected to override this method if the card has any 'choose_non_negative_integer' interactions.
    throw new \RuntimeException("Unimplemented handleNumberChoice");
  }

  protected function handleIconChoice(int $icon)
  {
    // Subclasses are expected to override this method if the card has any 'choose_icon_type' interactions.
    throw new \RuntimeException("Unimplemented handleIconChoice");
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

  protected function getPostExecutionIndex(): int
  {
    return $this->game->getCurrentNestedCardState()['post_execution_index'];
  }

  protected function setPostExecutionIndex(int $index)
  {
    $this->game->updateCurrentNestedCardState('post_execution_index', $index);
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

  protected function transferToScorePile(?array $card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'score');
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
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), "achievements");
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
    return $this->game->junkCards($cards);
  }

  protected function junk(?array $card): ?array
  {
    if (!$card) {
      return null;
    }
    return $this->game->junkCard($card);
  }

  protected function foreshadow(?array $card, $callbackIfFull, int $playerId = null): ?array
  {
    if (!$card) {
      return null;
    }
    $playerId = self::coercePlayerId($playerId);
    if (self::isFourthEdition() && self::countCards('forecast') >= $this->game->getForecastAndSafeLimit($playerId)) {
      return $callbackIfFull($card);
    } else {
      return $this->game->foreshadowCard($card, $playerId);
    }
  }

  protected function drawAndMeld(int $age, int $playerId = null): ?array
  {
    return $this->game->executeDrawAndMeld(self::coercePlayerId($playerId), $age);
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
    return array_filter($cards, function ($card) use ($colors) {
      return in_array($card['color'], $colors);
    });
  }

  protected function getValues(array $cards): array
  {
    return array_map(function ($card) {
      if ($card['location'] === 'board' || $card['location'] === 'display') {
        return $card['faceup_age'];
      } else {
        return $card['age'];
      }
    }, $cards);
  }

  public function getRepeatedValues(array $cards): array
  {
    $values = self::getValues($cards);
    return Arrays::getRepeatedValues($values);
  }

  protected function getMinValue(array $cards)
  {
    if (empty($cards)) {
      return 0;
    }
    return min(array_map(function ($card) {
      if ($card['location'] === 'board' || $card['location'] === 'display') {
        return $card['faceup_age'];
      } else {
        return $card['age'];
      }
    }, $cards));
  }

  protected function getMaxValue(array $cards)
  {
    if (empty($cards)) {
      return 0;
    }
    return max(array_map(function ($card) {
      if ($card['location'] === 'board' || $card['location'] === 'display') {
        return $card['faceup_age'];
      } else {
        return $card['age'];
      }
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

  protected function isValuedCard(?array $card): bool
  {
    return $card && $card['age'] !== null;
  }

  protected function isSpecialAchievement(?array $card): bool
  {
    return $card && $card['age'] === null && $card['id'] < 1000;
  }

  protected function isBlue(?array $card): bool
  {
    return $card && $card['color'] == Colors::BLUE;
  }

  protected function isRed(?array $card): bool
  {
    return $card && $card['color'] == Colors::RED;
  }

  protected function isGreen(?array $card): bool
  {
    return $card && $card['color'] == Colors::GREEN;
  }

  protected function isYellow(?array $card): bool
  {
    return $card && $card['color'] == Colors::YELLOW;
  }

  protected function isPurple(?array $card): bool
  {
    return $card && $card['color'] == Colors::PURPLE;
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
    foreach ($choiceMap as $value => $textOrarray) {
      if (!in_array($value, $validChoices)) {
        continue;
      }
      $text = is_array($textOrarray) ? reset($textOrarray) : $textOrarray;
      $args = is_array($textOrarray) ? array_slice($textOrarray, 1) : [];
      $options[] = array_merge($args, [
        'value' => $value,
        'text'  => $text,
      ]);
    }
    return $options;
  }

  private function canPass(): bool
  {
    return $this->game->innovationGameState->get('can_pass');
  }

  // PLAYER HELPERS

  protected function win(int $playerId = null): void
  {
    $this->game->innovationGameState->set('winner_by_dogma', self::coercePlayerId($playerId));
    throw new \EndOfGame();
  }

  protected function lose(int $playerId = null): void
  {
    $playerId = self::coercePlayerId($playerId);

    // TODO(4E): Junk all of the player's cards.

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
    return $this->game->countCardsInLocationKeyedByAge( /*owner=*/0, 'deck', CardTypes::BASE)[$age];
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
    $countsByValue = self::countCardsKeyedByValue($location, $playerId);
    for ($age = 1; $age <= 11; $age++) {
      if ($countsByValue[$age] > 0) {
        $values[] = $age;
      }
    }
    return $values;
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

  protected function getUniqueColors(string $location, int $playerId = null): array
  {
    $colors = [];
    $counts = self::countCardsKeyedByColor($location, $playerId);
    foreach (Colors::ALL as $color) {
      if ($counts[$color] > 0) {
        $colors[] = $color;
      }
    }
    return $colors;
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
          $icons = array_merge($icons, self::getIcons($card));
        } else {
          $icons = array_merge($icons, self::getIcons($card, $spots));
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
        $icons = array_merge($icons, self::getIcons($card));
      } else {
        $icons = array_merge($icons, self::getIcons($card, $spots));
      }
    }
    // Convert array of icons to array of counts
    return array_count_values($icons);
  }


  protected function hasIconInCommon(array $card1, array $card2): bool
  {
    return count(array_intersect(self::getIcons($card1), self::getIcons($card2))) > 0;
  }

  private function getVisibleSpotsOnBuriedCard(int $splayDirection): array
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

  protected function getIcons(array $card, array $spots = [1, 2, 3, 4, 5, 6]): array
  {
    $icons = [];
    foreach ($spots as $spot) {
      $icon = $card['spot_' . $spot];
      // Echo effects don't actually count as an icon type
      if ($icon && $icon != Icons::ECHO_EFFECT) {
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
    $defaultArgs = ['You' => 'You'];
    $this->game->notifyPlayer(self::coercePlayerId($playerId), 'log', $log, array_merge($defaultArgs, $args));
  }

  protected function notifyOthers($log, array $args = [], int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $defaultArgs = ['player_name' => self::renderPlayerName($playerId)];
    $this->game->notifyAllPlayersBut($playerId, 'log', $log, array_merge($defaultArgs, $args));
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

  // GENERAL UTILITY HELPERS

  private function getThisCardId(): string
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5));
  }

  private function getThisCard(): array
  {
    return self::getCard(self::getThisCardId());
  }

  // PRIVATE HELPERS

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