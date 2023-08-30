<?php

namespace Innovation\Cards;

/* Class which contains the state of a card's execution */
class ExecutionState
{

  /* The edition of the rules being used */
  private ?int $edition = null;

  /* The player who launched the card currently being executed */
  private ?int $launcherId = null;

  /* The current player */
  private ?int $playerId = null;

  /* The current effect type */
  private ?int $effectType = null;

  /* The current effect number */
  private ?int $effectNumber = null;

  /* The current interaction being executed */
  private ?int $currentStep = null;

  /* The number of things (e.g. cards) chosen so far in this interaction */
  private ?int $numChosen = null;

  /* The number of interactions that the current effect requires */
  private ?int $maxSteps = null;

  /* The next interaction that will be executed */
  private ?int $nextStep = null;

  protected \Table $game;

  function __construct(\Table $game)
  {
      $this->game = $game;
  }

  function setEdition(?int $edition): ExecutionState
  {
    $this->edition = $edition;
    return $this;
  }

  function getEdition(): ?int
  {
    return $this->edition;
  }

  function setLauncherId(?int $launcherId): ExecutionState
  {
    $this->launcherId = $launcherId;
    return $this;
  }

  function getLauncherId(): ?int
  {
    return $this->launcherId;
  }

  function setPlayerId(?int $playerId): ExecutionState
  {
    $this->playerId = $playerId;
    return $this;
  }

  function getPlayerId(): ?int
  {
    return $this->playerId;
  }

  function setEffectType(?int $effectType): ExecutionState
  {
    $this->effectType = $effectType;
    return $this;
  }

  function getEffectType(): ?int
  {
    return $this->effectType;
  }

  function isDemand(): bool
  {
    return $this->effectType === $this->game::DEMAND_EFFECT;
  }

  function isCompel(): bool
  {
    return $this->effectType === $this->game::COMPEL_EFFECT;
  }

  function isNonDemand(): bool
  {
    return $this->effectType === $this->game::NON_DEMAND_EFFECT;
  }

  function isEcho(): bool
  {
    return $this->effectType === $this->game::ECHO_EFFECT;
  }

  function setEffectNumber(?int $effectNumber): ExecutionState
  {
    $this->effectNumber = $effectNumber;
    return $this;
  }

  function getEffectNumber(): ?int
  {
    return $this->effectNumber;
  }

  function setCurrentStep(?int $currentStep): ExecutionState
  {
    $this->currentStep = $currentStep;
    return $this;
  }

  function getCurrentStep(): ?int
  {
    return $this->currentStep;
  }

  function setNumChosen(?int $numChosen): ExecutionState
  {
    $this->numChosen = $numChosen;
    return $this;
  }

  function getNumChosen(): ?int
  {
    return $this->numChosen;
  }

  function setMaxSteps(?int $maxSteps): ExecutionState
  {
    $this->maxSteps = $maxSteps;
    return $this;
  }

  function getMaxSteps(): ?int
  {
    return $this->maxSteps;
  }

  function setNextStep(?int $nextStep): ExecutionState
  {
    $this->nextStep = $nextStep;
    return $this;
  }

  function getNextStep(): ?int
  {
    return $this->nextStep;
  }
}