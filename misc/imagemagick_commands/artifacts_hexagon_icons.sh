#!/bin/bash

# Coordinates for hexagons
TOP_LEFT="97,58 60,122 97,186 170,186 207,122 170,58"
BOTTOM_LEFT="99,351 62,415 99,479 172,479 209,415 172,351"
BOTTOM_CENTER="341,351 304,415 341,479 414,479 451,415 414,351"
BOTTOM_RIGHT="573,349 536,413 573,477 646,477 683,413 646,349"

# Hexagon icon borders
RED_BORDER="../card_icon_borders/red_hexagon_icon_border.png"
YELLOW_BORDER="../card_icon_borders/yellow_hexagon_icon_border.png"
BLUE_BORDER="../card_icon_borders/blue_hexagon_icon_border.png"
GREEN_BORDER="../card_icon_borders/green_hexagon_icon_border.png"
PURPLE_BORDER="../card_icon_borders/purple_hexagon_icon_border.png"

# File paths/prefixes
SAVE_PATH="../hexagon_icons"
READ_PATH="../cards/Print_ArtifactsCards_front/Print_ArtifactsCards_front-"
FILE_SUFFIX="_artifacts.png"


# Extracts hexagon from card and places in matching border
# arg1: filename of card to extract from
# arg2: position of hexagon in card
# arg3: path for border file
# arg4: name of png to save to
# example: make_hexagon "010" "$TOP_LEFT" "$BLUE_BORDER" "000"
make_hexagon()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "polygon ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "${SAVE_PATH}/$4${FILE_SUFFIX}"
    magick convert -gravity center $3 "${SAVE_PATH}/$4${FILE_SUFFIX}" -composite "${SAVE_PATH}/$4${FILE_SUFFIX}"
}

# Age 1
make_hexagon "010" "$BOTTOM_CENTER"       "$BLUE_BORDER"   "000"
make_hexagon "011" "$BOTTOM_RIGHT"       "$BLUE_BORDER"   "001"
make_hexagon "012" "$BOTTOM_LEFT"       "$BLUE_BORDER"   "002"
make_hexagon "001" "$BOTTOM_CENTER"  "$RED_BORDER"    "003"
make_hexagon "002" "$BOTTOM_RIGHT"  "$RED_BORDER"    "004"
make_hexagon "003" "$TOP_LEFT"  "$RED_BORDER"    "005"
make_hexagon "007" "$BOTTOM_LEFT"       "$GREEN_BORDER"  "006"
make_hexagon "008" "$BOTTOM_LEFT"  "$GREEN_BORDER"  "007"
make_hexagon "009" "$TOP_LEFT"       "$GREEN_BORDER"  "008"
make_hexagon "004" "$TOP_LEFT"       "$YELLOW_BORDER" "009"
make_hexagon "005" "$TOP_LEFT"  "$YELLOW_BORDER" "010"
make_hexagon "006" "$TOP_LEFT"    "$YELLOW_BORDER" "011"
make_hexagon "013" "$BOTTOM_CENTER"       "$PURPLE_BORDER" "012"
make_hexagon "014" "$TOP_LEFT"       "$PURPLE_BORDER" "013"
make_hexagon "015" "$TOP_LEFT"       "$PURPLE_BORDER" "014"
