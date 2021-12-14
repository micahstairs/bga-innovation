#!/bin/bash
# Setup
mkdir temp

### ECHOES ###

# Coordinates for circles
# First coord pair is center, second is any point the radius away from the center
TOP_LEFT="133,123 133,45"
BOTTOM_LEFT="136,415 136,337"
BOTTOM_CENTER="379,415 379,337"
BOTTOM_RIGHT="614,415 614,337"

# File paths/prefixes
READ_PATH="../cards/Print_EchoesCards_front/Print_EchoesCards_front-"

# Extracts hexagon from a base card and places in a colored border
# arg1: input filename
# arg2: position of circle in card
# arg4: output filename
# example: extract_circle "002" "$BOTTOM_RIGHT" "000"

extract_circle()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "circle ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$3.png"
}

echo "Extracting echoes circle icons..."

# Age 1 examples
extract_circle "002" "$BOTTOM_RIGHT" "000"
extract_circle "003" "$BOTTOM_LEFT"  "001"
extract_circle "012" "$BOTTOM_CENTER"  "002"
extract_circle "011" "$TOP_LEFT"  "003"
