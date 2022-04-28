#!/bin/bash

# Setup
mkdir temp

# File paths/prefixes
CARD_PATH="../cards/Print_CitiesCards_front/Print_CitiesCards_front-"
CITIES_MASK_PATH="../cities_icons/cities_mask.png"
SEARCH_MASK_PATH="../cities_icons/search_border_mask.png"

# Extracts icon from top center position of a card using a mask
# arg1: input filename
# arg2: output filename
# example: extract_icon "006" "000"

extract_icon()
{
    magick convert "${CARD_PATH}$1.png" "$2" -compose CopyOpacity -composite -trim +repage "temp/$3_cities.png"
}

# Extract blue, red, green, yellow, purple plus icons
extract_icon "016" "$CITIES_MASK_PATH" "000"
extract_icon "015" "$CITIES_MASK_PATH" "001"
extract_icon "006" "$CITIES_MASK_PATH" "002"
extract_icon "041" "$CITIES_MASK_PATH" "003"
extract_icon "011" "$CITIES_MASK_PATH" "004"

# Extract blue, red, green, yellow, purple arrow icons
extract_icon "027" "$CITIES_MASK_PATH" "005"
extract_icon "035" "$CITIES_MASK_PATH" "006"
extract_icon "028" "$CITIES_MASK_PATH" "007"
extract_icon "030" "$CITIES_MASK_PATH" "008"
extract_icon "033" "$CITIES_MASK_PATH" "009"

# Extract blue, red, green, yellow, purple flag icons
extract_icon "056" "$CITIES_MASK_PATH" "010"
extract_icon "054" "$CITIES_MASK_PATH" "011"
extract_icon "049" "$CITIES_MASK_PATH" "012"
extract_icon "060" "$CITIES_MASK_PATH" "013"
extract_icon "052" "$CITIES_MASK_PATH" "014"

# Extract blue, red, green, yellow, purple fountain icons
extract_icon "087" "$CITIES_MASK_PATH" "015"
extract_icon "084" "$CITIES_MASK_PATH" "016"
extract_icon "078" "$CITIES_MASK_PATH" "017"
extract_icon "081" "$CITIES_MASK_PATH" "018"
extract_icon "093" "$CITIES_MASK_PATH" "019"

# Extract blue, red, green, yellow, purple search icons
extract_icon "001" "$SEARCH_MASK_PATH" "020"
extract_icon "013" "$SEARCH_MASK_PATH" "021"
extract_icon "004" "$SEARCH_MASK_PATH" "022"
extract_icon "007" "$SEARCH_MASK_PATH" "023"
extract_icon "010" "$SEARCH_MASK_PATH" "024"

# Build cities spritesheet, 5x5
magick montage \
temp/00{0..9}_cities.png \
temp/0{10..24}_cities.png \
-trim -tile 5x5 -geometry +5+5 -background 'none' ../../img/cities_special_icons.png

echo "Cleaning up..."

# Cleanup
rm -r temp