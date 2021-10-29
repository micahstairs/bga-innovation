#!/bin/bash
folder_path="../card_backgrounds"

mogrify -path $folder_path/scaled -resize 110x150 \
    $folder_path/age_{1..10}_base.png $folder_path/relic_base.png \
    $folder_path/age_{1..10}_echoes.png $folder_path/relic_echoes.png \
    $folder_path/age_{1..10}_figures.png $folder_path/relic_figures.png \
    $folder_path/age_{1..10}_cities.png $folder_path/relic_cities.png \
    $folder_path/age_{1..10}_artifacts.png $folder_path/relic_artifacts.png

magick montage \
    $folder_path/scaled/age_{1..10}_base.png $folder_path/scaled/relic_base.png \
    $folder_path/scaled/age_{1..10}_echoes.png $folder_path/scaled/relic_echoes.png \
    $folder_path/scaled/age_{1..10}_figures.png $folder_path/scaled/relic_figures.png \
    $folder_path/scaled/age_{1..10}_cities.png $folder_path/scaled/relic_cities.png \
    $folder_path/scaled/age_{1..10}_artifacts.png $folder_path/scaled/relic_artifacts.png \
-tile 11x5 -geometry +3+3 -background 'none' $folder_path/card_background_portrait_spritesheet.jpg
