<?php

namespace Innovation\Enums;

class CardTypes
{
  const BASE = 0;
  const ARTIFACTS = 1;
  const CITIES = 2;
  const ECHOES = 3;
  const FIGURES = 4;
  const UNSEEN = 5;

  public static function getAllTypesOtherThan(int $type)
  {
    return array_diff(range(0, 5), [$type]);
  }
}