#!/bin/bash
# Setup
mkdir temp

# File paths/prefixes
CITIES_PATH="../cards/4E_Cities/Cities"
ECHOES_PATH="../cards/4E_Echoes/Echoes"

# Extracts shape from a base card and places in a colored border
# Covers number with an ellipse based on circle background
# arg1: path
# arg2: input filename
# arg3: position of shape in card
# arg4: output filename
extract_circle()
{
    magick "$1$2.png" \( +clone -fill Black -colorize 100 -fill White -draw "circle $3" \)  -alpha off -compose CopyOpacity -composite -trim +repage "temp/$4_circle.png"
    magick "temp/$4_circle.png" -fill '%[pixel:p{106,76}]' -draw 'ellipse 115,108 58,68 0,360' "temp/$4_circle.png"
}

extract_ellipse()
{
    magick "$1$2.png" \( +clone -fill Black -colorize 100 -fill White -draw "ellipse $3" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$4_ellipse.png"
}

echo "Extracting bonus icons..."

# Extract blue, red, green, yellow, purple bonus circles
extract_circle $CITIES_PATH "22" "164,587 81,506" "1"
extract_circle $CITIES_PATH "97" "164,587 81,506" "2"
extract_circle $CITIES_PATH "20" "164,587 81,506" "3"
extract_circle $CITIES_PATH "68" "164,587 81,506" "4"
extract_circle $CITIES_PATH "35" "164,587 81,506" "5"

# Extract numbers 1-12
extract_ellipse $ECHOES_PATH "1"   "882,575 57,68 0,360" "1"
extract_ellipse $CITIES_PATH "18"  "882,580 57,68 0,360" "2"
extract_ellipse $CITIES_PATH "30"  "882,580 57,68 0,360" "3"
extract_ellipse $CITIES_PATH "44"  "883,580 57,68 0,360" "4"
extract_ellipse $CITIES_PATH "46"  "883,580 57,68 0,360" "5"
extract_ellipse $CITIES_PATH "65"  "531,580 57,68 0,360" "6"
extract_ellipse $CITIES_PATH "59"  "880,578 57,68 0,360" "7"
extract_ellipse $CITIES_PATH "78"  "164,580 57,68 0,360" "8"
extract_ellipse $CITIES_PATH "77"  "530,580 57,68 0,360" "9"
extract_ellipse $CITIES_PATH "92"  "531,582 57,68 0,360" "10"
extract_ellipse $CITIES_PATH "110" "882,585 57,68 0,360" "11"
extract_ellipse $CITIES_PATH "111" "882,585 57,68 0,360" "12"

# Build row of circular bonus icons
magick montage \
temp/{1..5}_circle.png \
-trim -tile 5x1 -geometry +5+5 -background 'none' temp/bonus_circles.png

# Build row of number ellipses
magick montage \
temp/{1..12}_ellipse.png \
-trim -tile 12x1 -geometry +5+5 -background 'none' temp/bonus_numbers.png

# Combine all images into a single spritesheet.
magick montage \
temp/bonus_circles.png \
temp/bonus_numbers.png \
-tile 1x2 -geometry +0+0 -background 'none' ../../img/bonus_icons.png

echo "Cleaning up..."

# Cleanup
rm -r temp