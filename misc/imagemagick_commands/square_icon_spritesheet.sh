#!/bin/bash

# File paths/prefixes
ICON_SRC_PATH="../square_icons"
RESULT_PATH="../square_icons/bordered"

# (1) Generate colored square borders set on white background
function generate_border()
{
    local border_color=${1}  # e.g. blue
    local border_hexcolor=${2}  # e.g. '#0e2a50'
    local result_img="${BORDER_SRC_PATH}/${border_color}_square_icon_border.png"
    magick convert -size '155x155' 'canvas:white' -fill 'white' -stroke $border_hexcolor -strokewidth 6 -draw 'rectangle 2,2 152,152' $result_img
}
generate_border 'blue' '#0e2a50'
generate_border 'red' '#5a090c'
generate_border 'green' '#122c18'
generate_border 'yellow' '#8b6a28'
generate_border 'purple' '#220e40'

# (2) Generate each combination of icon + colored border box
function borderize()
{
    local icon_type=${1}  # e.g. crown leaf lightbulb castle factory clock
    local border_color=${2}  # e.g. blue red green yellow purple
    local icon_img="${ICON_SRC_PATH}/${icon_type}.png"
    local border_img="${ICON_SRC_PATH}/${border_color}_square_icon_border.png"
    local result_img="${RESULT_PATH}/${icon_type}_${border_color}.png"
    magick convert -composite -gravity center $border_img $icon_img $result_img
}

for icon in {crown,leaf,lightbulb,castle,factory,clock}; do
    for color in {blue,red,green,yellow,purple}; do
        borderize ${icon} ${color}
    done
done

# (3) Generate spritesheet from all individual bordered icons constructed in step 2
magick montage \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_blue.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_red.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_green.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_yellow.png" \
    "${RESULT_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}"_purple.png" \
-tile 6x5 -geometry +2+2 -background 'none' "${RESULT_PATH}/../square_icon_spritesheet_1.png"

magick montage \
    "${ICON_SRC_PATH}/"{crown,leaf,lightbulb,castle,factory,clock}".png" \
-tile 6x5 -geometry +11+5 -background 'none' "${RESULT_PATH}/../square_icon_spritesheet_2.png"

magick montage \
    "${RESULT_PATH}/../square_icon_spritesheet_"{1,2}".png" \
-tile 1x2 -geometry +0+0 -background 'none' "${RESULT_PATH}/../square_icon_spritesheet.png"

rm "${RESULT_PATH}/../square_icon_spritesheet_"{1,2}".png"
