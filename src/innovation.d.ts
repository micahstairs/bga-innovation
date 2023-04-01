declare type Card = {

  // From SQL database
  id: number;
  type: number;
  age: number;
  faceup_age: number;
  color: number;
  spot_1: number;
  spot_2: number;
  spot_3: number;
  spot_4: number;
  spot_5: number;
  spot_6: number;
  dogma_icon: number;
  is_relic: boolean;

  // From materials file
  name: string;
  condition_for_claiming: string;
  alternative_condition_for_claiming: string;
  echo_effect_1: string;
  i_demand_effect_1: string;
  i_demand_effect_1_is_compel: boolean;
  non_demand_effect_1: string;
  non_demand_effect_2: string;
  non_demand_effect_3: string;
}