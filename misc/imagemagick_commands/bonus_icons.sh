#!/bin/bash
# Setup
mkdir temp

# File paths/prefixes
READ_PATH="../cards/4E_Cities/Cities"

# Extracts shape from a base card and places in a colored border
# Covers number with an ellipse based on circle background
# arg1: input filename
# arg2: position of shape in card
# arg4: output filename
extract_circle()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "circle ${2}" \)  -alpha off -compose CopyOpacity -composite -trim +repage "temp/$3_circle.png"
    magick "temp/$3_circle.png" -fill '%[pixel:p{106,76}]' -draw 'ellipse 115,108 58,68 0,360' "temp/$3_circle.png"
}

extract_ellipse()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "ellipse ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$3_ellipse.png"
}

echo "Extracting bonus icons..."

# Extract blue, red, green, yellow, purple bonus circles
extract_circle "22" "164,587 81,506" "1"
extract_circle "97" "164,587 81,506" "2"
extract_circle "20" "164,587 81,506" "3"
extract_circle "68" "164,587 81,506" "4"
extract_circle "35" "164,587 81,506" "5"

# Extract numbers 1-12
extract_ellipse "2"   "882,585 57,68 0,360" "1"
extract_ellipse "18"  "882,580 57,68 0,360" "2"
extract_ellipse "30"  "882,580 57,68 0,360" "3"
extract_ellipse "44"  "883,580 57,68 0,360" "4"
extract_ellipse "46"  "883,580 57,68 0,360" "5"
extract_ellipse "65"  "531,580 57,68 0,360" "6"
extract_ellipse "59"  "880,578 57,68 0,360" "7"
extract_ellipse "78"  "164,580 57,68 0,360" "8"
extract_ellipse "77"  "530,580 57,68 0,360" "9"
extract_ellipse "92"  "531,582 57,68 0,360" "10"
extract_ellipse "110" "882,585 57,68 0,360" "11"
extract_ellipse "111" "882,585 57,68 0,360" "12"

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