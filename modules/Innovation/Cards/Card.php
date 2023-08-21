<?php

namespace Innovation\Cards;

use Innovation\Cards\ExecutionState;
use Innovation\Utils\Arrays;
use Innovation\Utils\Notifications;

/* Abstract class of all card implementations */
abstract class Card
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
      case 10: // choose_player
        return static::getPromptForPlayerChoice();
      case 12: // choose_icon_type
        return static::getPromptForIconChoice();
      default:
        throw new \RuntimeException("Unhandled value in getSpecialChoicePrompt: " . $choiceType);
    }
  }

  protected function getPromptForListChoice():  array
  {
    // Subclasses are expected to override this method if the card has any interactions which use the 'choices' option.
    throw new \RuntimeException("Unimplemented getPromptForListChoice");
  }

  public function handleAbortedInteraction()
  {
    // Subclasses can optionally override this function if any extra handling needs to be done if
    // the interaction was aborted before it started.
  }

  public function handleCardChoice(array $card)
  {
    // Subclasses can optionally override this function if any extra handling is needed after each individual card is chosen.
  }

  public function handleSpecialChoice(int $choice)
  {
    // Subclasses are expected to override this function if any of the interactions use a special choice.
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

  protected function getEffectNumber(): int
  {
    return $this->state->getEffectNumber();
  }

  protected function isDemand(): bool
  {
    return $this->state->isDemand();
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

  protected function drawFromSet(int $age, int $type, int $playerId = null)
  {
    return $this->game->executeDraw(self::coercePlayerId($playerId), $age, 'hand', /*bottom_to=*/false, /*type=*/$type);
  }

  protected function transferToHand($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'hand');
  }

  protected function score($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->scoreCard($card, self::coercePlayerId($playerId));
  }

  protected function transferToScorePile($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'score');
  }

  protected function meld($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->meldCard($card, self::coercePlayerId($playerId));
  }

  protected function transferToBoard($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'board');
  }

  protected function tuck($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->tuckCard($card, self::coercePlayerId($playerId));
  }

  protected function reveal($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'revealed');
  }

  protected function safeguard($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->safeguardCard($card, self::coercePlayerId($playerId));
  }

  protected function putBackInSafe($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->putCardBackInSafe($card, self::coercePlayerId($playerId));
  }

  protected function achieve($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), "achievements");
  }

  protected function achieveIfEligible($card, int $playerId = null)
  {
    if (self::isEligibleForAchieving($card, self::coercePlayerId($playerId))) {
      return self::achieve($card);
    }
    return $card;
  }

  protected function isEligibleForAchieving($card, int $playerId = null): bool
  {
    if (!$card) {
      return false;
    }
    return in_array($card['age'], $this->game->getClaimableValuesIgnoringAvailability(self::coercePlayerId($playerId)));
  }

  protected function return ($card)
  {
    if (!$card) {
      return null;
    }
    return $this->game->returnCard($card);
  }

  protected function placeOnTopOfDeck($card)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, 0, 'deck', /*bottom_to=*/false);
  }

  protected function junk($card)
  {
    if (!$card) {
      return null;
    }
    return $this->game->junkCard($card);
  }

  protected function foreshadow($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->foreshadowCard($card, self::coercePlayerId($playerId));
  }

  protected function putInHand($card, int $playerId = null)
  {
    if (!$card) {
      return null;
    }
    return $this->game->transferCardFromTo($card, self::coercePlayerId($playerId), 'hand');
  }

  protected function drawAndMeld(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndMeld(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndTuck(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndTuck(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndScore(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndScore(self::coercePlayerId($playerId), $age);
  }

  protected function drawAndSafeguard(int $age, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    if (self::isFourthEdition() && self::countCards('safe') >= $this->game->getForecastAndSafeLimit($playerId)) {
      $card = self::draw($age, $playerId);
      $this->notifications->notifyLocationFull(clienttranslate('safe'), $playerId);
    } else {
      $card = $this->game->executeDrawAndSafeguard($playerId, $age);
    }
    return $card;
  }

  protected function drawAndForeshadow(int $age, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    if (self::isFourthEdition() && self::countCards('forecast') >= $this->game->getForecastAndSafeLimit($playerId)) {
      $card = self::draw($age, $playerId);
      $this->notifications->notifyLocationFull(clienttranslate('forecast'), $playerId);
    } else {
      $card = $this->game->executeDrawAndForeshadow($playerId, $age);
    }
    return $card;
  }

  protected function drawAndReveal(int $age, int $playerId = null)
  {
    return $this->game->executeDrawAndReveal(self::coercePlayerId($playerId), $age);
  }

  protected function getTopCardOfColor(int $color, int $playerId = null)
  {
    return $this->game->getTopCardOnBoard(self::coercePlayerId($playerId), $color);
  }

  protected function getTopCards(int $playerId = null)
  {
    $cards = $this->game->getTopCardsOnBoard(self::coercePlayerId($playerId));
    if ($cards === null) {
      return [];
    }
    return $cards;
  }

  protected function getBottomCardOfColor(int $color, int $playerId = null)
  {
    return $this->game->getBottomCardOnBoard(self::coercePlayerId($playerId), $color);
  }

  protected function getRevealedCard(int $playerId = null)
  {
    $cards = self::getCards('revealed');
    if (count($cards) === 0) {
      return null;
    }
    return $cards[0];
  }

  protected function getCards(string $location, int $playerId = null)
  {
    return $this->game->getCardsInLocation(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  // BULK CARD HELPERS

  protected function junkBaseDeck(int $age): bool
  {
    return $this->game->junkBaseDeck($age);
  }

  protected function revealHand(int $playerId = null)
  {
    $this->game->revealLocation(self::coercePlayerId($playerId), 'hand');
  }

  protected function revealScorePile(int $playerId = null)
  {
    $this->game->revealLocation(self::coercePlayerId($playerId), 'score');
  }

  protected function revealForecast(int $playerId = null)
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

  protected function hasIcon($card, int $icon): bool
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

  protected function isValuedCard($card): bool
  {
    return $card['age'] !== null;
  }

  protected function isSpecialAchievement($card): bool
  {
    return $card['age'] === null;
  }

  protected function isBlue($card): bool
  {
    return $card['color'] == $this->game::BLUE;
  }

  protected function isRed($card): bool
  {
    return $card['color'] == $this->game::RED;
  }

  protected function isGreen($card): bool
  {
    return $card['color'] == $this->game::GREEN;
  }

  protected function isYellow($card): bool
  {
    return $card['color'] == $this->game::YELLOW;
  }

  protected function isPurple($card): bool
  {
    return $card['color'] == $this->game::PURPLE;
  }

  protected function getCard(int $cardId)
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

  protected function unsplay(int $color, int $targetPlayerId = null, int $triggeringPlayerId = null)
  {
    $targetPlayerId = self::coercePlayerId($targetPlayerId);
    if ($triggeringPlayerId === null) {
      $triggeringPlayerId = $targetPlayerId;
    }
    $this->game->unsplay($triggeringPlayerId, $targetPlayerId, $color);
  }

  protected function splayLeft(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayLeft($playerId, $playerId, $color);
  }

  protected function splayRight(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayRight($playerId, $playerId, $color);
  }

  protected function splayUp(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayUp($playerId, $playerId, $color);
  }

  protected function splayAslant(int $color, int $playerId = null)
  {
    $playerId = self::coercePlayerId($playerId);
    $this->game->splayAslant($playerId, $playerId, $color);
  }

  protected function getSplayDirection(int $color, int $playerId = null): int
  {
    return $this->game->getCurrentSplayDirection(self::coercePlayerId($playerId), $color);
  }

  protected function isSplayed(int $color, int $playerId = null): int
  {
    return self::getSplayDirection(self::coercePlayerId($playerId), $color) > 0;
  }

  // COLOR HELPERS

  protected function getAllColorsOtherThan(int $color)
  {
    return array_diff(range(0, 4), [$color]);
  }

  // SELECTION HELPERS

  protected function getLastSelectedCard()
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
    $this->game->setActionScopedAuxiliaryArray(self::getCardIdFromClassName(), $playerId, $array);
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
    return $this->game->getActionScopedAuxiliaryArray(self::getCardIdFromClassName(), $playerId);
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

  protected function getPromptForIconChoice(): array
  {
    if (self::canPass()) {
      return [
        "message_for_player" => clienttranslate('Choose a value'),
        "message_for_others" => clienttranslate('${player_name} may choose a value'),
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
    return $this->game->countCardsInLocationKeyedByAge( /*owner=*/0, 'deck', $this->game::BASE)[$age];
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
    return $this->game->countCardsInLocationKeyedByAge(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function getUniqueColors(string $location, int $playerId = null): array
  {
    $colors = [];
    $cardsByColor = self::getCardsKeyedByColor($location, $playerId);
    for ($color = 0; $color < 5; $color++) {
      if ($cardsByColor[$color] > 0) {
        $colors[] = $color;
      }
    }
    return $colors;
  }

  protected function getCardsKeyedByColor(string $location, int $playerId = null): array
  {
    return $this->game->getCardsInLocationKeyedByColor(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function countCardsKeyedByColor(string $location, int $playerId = null): array
  {
    return $this->game->countCardsInLocationKeyedByColor(self::coercePlayerIdUsingLocation($playerId, $location), $location);
  }

  protected function getScore(int $playerId = null): int
  {
    return $this->game->getPlayerScore(self::coercePlayerId($playerId));
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

  protected function hasIconInCommon(array $card1, array $card2): bool
  {
    return count(array_intersect(self::getIconTypes($card1), self::getIconTypes($card2))) > 0;
  }

  protected function getIconTypes(array $card): array
  {
    $icons = [];
    for ($i = 1; $i <= 6; $i++) {
      $icon = $card['spot_' . $i];
      // Echo effects don't actually count as an icon type
      if ($icon && !$this->game::ECHO_EFFECT_ICON) {
        $icons[] = min($icon, 100);
      }
    }
    return $icons;
  }

  protected function getIconCount(int $icon, int $playerId = null): int
  {
    return $this->game->getPlayerSingleRessourceCount(self::coercePlayerId($playerId), $icon);
  }

  protected function getIconCounts(int $playerId = null): array
  {
    return $this->game->getPlayerResourceCounts(self::coercePlayerId($playerId));
  }

  protected function wasForeseen(): bool
  {
    // NOTE: The phrase "was foreseen" didn't appear on cards until the fourth edition.
    return self::isFourthEdition() && $this->game->innovationGameState->get('foreseen_card_id') == self::getCardIdFromClassName();
  }

  protected function getAllTypesOtherThan(int $type)
  {
    return array_diff(range(0, 5), [$type]);
  }

  protected function notifyAll($log, array $args = [])
  {
    $this->game->notifyGeneralInfo($log, $args);
  }

  protected function notifyPlayer($log, array $args, int $playerId = null)
  {
    $this->game->notifyPlayer(self::coercePlayerId($playerId), 'log', $log, $args);
  }

  protected function notifyOthers($log, array $args, int $playerId = null)
  {
    $this->game->notifyAllPlayersBut(self::coercePlayerId($playerId), 'log', $log, $args);
  }

  protected function renderIcon(int $icon)
  {
    return $this->game->getIconSquare($icon);
  }

  public function renderValue(int $value): string
  {
    return $this->notifications->renderValue($value);
  }

  public function renderValueWithType(int $value, int $type): string
  {
    return $this->notifications->renderValueWithType($value, $type);
  }

  // GENERAL UTILITY HELPERS

  protected function getCardIdFromClassName(): string
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5));
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
    if ($location === 'deck' || $location === 'junk' || $location === 'relics') {
      return 0;
    }
    return self::coercePlayerId($playerId);
  }

}