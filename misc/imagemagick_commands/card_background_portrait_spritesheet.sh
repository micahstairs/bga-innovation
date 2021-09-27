#!/bin/bash

folder_path="../card_backgrounds"
magick montage $folder_path/{age_01,age_02,age_03,age_04,age_05,age_06,age_07,age_08,age_09,age_10,special_achievement_portrait}.png -tile 11x1 -geometry +5+5 -background 'white' $folder_path/card_background_portrait_spritesheet.jpg
