#!/bin/bash
folder_path="../card_backgrounds"

mkdir $folder_path/temp

mogrify -path $folder_path/temp -resize 110x150 \
    $folder_path/age_{1..10}_base.png $folder_path/relic_base.png \
    $folder_path/age_{1..10}_artifacts.png $folder_path/relic_artifacts.png \
    $folder_path/age_{1..10}_cities.png $folder_path/relic_cities.png \
    $folder_path/age_{1..10}_echoes.png $folder_path/relic_echoes.png \
    $folder_path/age_{1..10}_figures.png $folder_path/relic_figures.png

magick montage \
    $folder_path/temp/age_{1..10}_base.png $folder_path/temp/relic_base.png \
    $folder_path/temp/age_{1..10}_artifacts.png $folder_path/temp/relic_artifacts.png \
    $folder_path/temp/age_{1..10}_cities.png $folder_path/temp/relic_cities.png \
    $folder_path/temp/age_{1..10}_echoes.png $folder_path/temp/relic_echoes.png \
    $folder_path/temp/age_{1..10}_figures.png $folder_path/temp/relic_figures.png \
-tile 11x5 -geometry +3+3 -background 'none' ../../img/card_backs_portrait.jpg

rm -r $folder_path/temp
