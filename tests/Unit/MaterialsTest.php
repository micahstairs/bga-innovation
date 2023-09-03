<?php

namespace Unit;

use BaseTest;

class MaterialsTest extends BaseTest
{
  protected function setUp(): void
  {
    parent::setUp();
  }

  public function testAllKeysAreSupported()
  {
    $game = $this->getInnovationInstance();
    $materials = $game->textual_card_infos;

    $uniqueProperties = [];
    foreach ($materials as $key => $cardData) {
      $uniqueProperties = array_unique(array_merge($uniqueProperties, array_keys($cardData)));
    }

    $this->assertEquals([], array_diff($uniqueProperties, self::getSupportedProperties()));
  }

  private function getSupportedProperties(): array
  {
    // Start with the list of properties which cannot be versioned
    $properties = [
      'alternative_condition_for_claiming',
      'separate_4E_implementation', // TODO(LATER): Remove support for this
    ];

    // Add in the properties which can be versioned
    $versionSuffixes = [
      '_first',
      '_first_and_third',
      '_third',
      '_third_and_fourth',
      '_fourth',
    ];
    $propertiesWhichCanBeVersioned = [
      'condition_for_claiming',
      'echo_effect',
      'i_compel_effect',
      'i_demand_effect',
      'name',
      'non_demand_effect_1',
      'non_demand_effect_2',
      'non_demand_effect_3',
    ];
    foreach ($propertiesWhichCanBeVersioned as $property) {
      $properties[] = $property;
      foreach ($versionSuffixes as $versionSuffix) {
        $properties[] = $property . $versionSuffix;
      }
    }

    return $properties;
  }
}