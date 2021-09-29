#!/bin/bash

folder_path="../special_achievement_icons"
magick montage $folder_path/{empire,monument,universe,wonder,world}.png -tile 5x1 -geometry +5+5 -background 'none' $folder_path/special_achievement_icons.png