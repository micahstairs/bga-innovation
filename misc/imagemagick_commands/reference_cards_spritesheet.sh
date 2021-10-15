#!/bin/bash

folder_path="../reference_cards"
magick montage $folder_path/{side_1_notext,side_2_notext,side_1}.png -tile 1x3 -geometry +5+5 -background 'none' $folder_path/reference_cards_spritesheet.png