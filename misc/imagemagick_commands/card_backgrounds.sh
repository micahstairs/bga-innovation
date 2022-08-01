#!/bin/bash

mkdir temp

function crop_add_colorblind_indicators() {
    local border_color=${1}  # e.g. blue
    local colorblind_img=../card_shapes/${border_color}_shape.png
    local background_img=../card_backgrounds/${border_color}.png
    local result_img=temp/${border_color}.png
    
    magick composite -geometry +580+12 \
    $colorblind_img \
    $background_img[666x475+42+36] \
    $result_img
}

function tint_image() {
    local img_path=${1}
    local tint_color=${2} # e.g. green
    local tint_intensity=${3} # out of 200
    magick $img_path -fill $tint_color -tint $tint_intensity $img_path
}

for color in {blue,red,green,yellow,purple}; do
    crop_add_colorblind_indicators $color #crop and add indicators to base
    cp ../card_backgrounds/${color}_cities.png temp/${color}_cities.png # copy cities to temp
done

tint_image temp/blue.png white 50
tint_image temp/blue_cities.png white 50
tint_image temp/green.png green 30
tint_image temp/green_cities.png green 30
tint_image temp/yellow.png '#8b6a28' 5
tint_image temp/yellow_cities.png '#8b6a28' 5

# Build base card background spritesheet
magick montage temp/{blue,red,green,yellow,purple}.png \
-tile 5x1 -resize 560x408 -geometry +5+5 -background 'white' \
../../img/card_backgrounds.jpg

# Build cities card background spritesheet (these had colorblind indicators included)
magick montage temp/{blue,red,green,yellow,purple}_cities.png[666x475+42+36] \
-tile 5x1 -resize 560x408 -geometry +5+5 -background 'white' \
../../img/card_backgrounds_cities.jpg

rm -r temp
