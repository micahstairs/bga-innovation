#!/bin/bash

# Setup
mkdir temp

# File paths/prefixes
CARD_PATH="../cards/4E_Cities/Cities"
TOP_CENTER="230x230+413+60"
BOTTOM_CENTER="230x230+414+471"

# Extracts icon from top center position of a card
# arg1: input filename
# arg2: output filename
# example: extract_icon "11" "000" $TOP_CENTER

extract_icon()
{
    magick convert "${CARD_PATH}$1.png" -crop "$3" +repage "temp/$2_cities.png"
}

# Tints a color
function tint_image() {
    local img_path=${1}
    local tint_color=${2} # e.g. green
    local tint_intensity=${3} # out of 200
    magick $img_path -fill $tint_color -tint $tint_intensity $img_path
}


# Extract blue, red, green, yellow, purple Junk icons
extract_icon "11" "000" $TOP_CENTER
extract_icon "47" "001" $TOP_CENTER
extract_icon "80" "002" $TOP_CENTER
extract_icon "28" "003" $TOP_CENTER
extract_icon "45" "004" $TOP_CENTER

# Extract blue, red, green, yellow, purple Uplift icons
extract_icon "33" "005" $TOP_CENTER
extract_icon "36" "006" $BOTTOM_CENTER
extract_icon "7" "007" $TOP_CENTER
extract_icon "30" "008" $BOTTOM_CENTER
extract_icon "24" "009" $TOP_CENTER

# Extract blue, red, green, yellow, purple Unsplay icons
extract_icon "113" "010" $TOP_CENTER
extract_icon "107" "011" $TOP_CENTER
extract_icon "101" "012" $TOP_CENTER
extract_icon "98" "013" $TOP_CENTER
extract_icon "105" "014" $TOP_CENTER

# Tint green images
for i in {"002","007","012"}; do
    tint_image "temp/${i}_cities.png" green 30
done

# Build cities spritesheet, 5x5
magick montage \
temp/00{0..9}_cities.png \
temp/0{10..14}_cities.png \
-trim -tile 5x3 -geometry +5+5 -background 'none' ../../img/new_cities_icons.png

echo "Cleaning up..."

# Cleanup
rm -r temp