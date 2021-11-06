#!/bin/bash

mkdir temp

function generate_border() {
    local border_color=${1}  # e.g. blue
    local border_hexcolor=${2}  # e.g. '#0e2a50'
    local result_img="temp/${border_color}_square_icon_border.png"
    magick convert -size '155x155' 'canvas:white' -fill 'white' -stroke $border_hexcolor -strokewidth 6 -draw 'rectangle 2,2 152,152' $result_img
}
generate_border 'blue' '#0e2a50'
generate_border 'red' '#5a090c'
generate_border 'green' '#122c18'
generate_border 'yellow' '#8b6a28'
generate_border 'purple' '#220e40'
generate_border 'white' '#fffffe' # For some reason if I change this to #ffffff then it turns the row grayscale!

# (2) Generate each combination of icon + colored border box
function borderize() {
    local icon_type=${1}  # e.g. crown
    local border_color=${2}  # e.g. blue
    local icon_img="../square_icons/${icon_type}.png"
    local border_img="temp/${border_color}_square_icon_border.png"
    local result_img="temp/${icon_type}_${border_color}.png"
    magick convert -composite -gravity center $border_img $icon_img $result_img
}

for icon in {crown,leaf,lightbulb,castle,factory,clock}; do
    for color in {blue,red,green,yellow,purple,white}; do
        borderize ${icon} ${color}
    done
done

# (3) Generate spritesheet from all individual bordered icons constructed in step 2
magick montage \
    "temp/"{crown,leaf,lightbulb,castle,factory,clock}"_blue.png" \
    "temp/"{crown,leaf,lightbulb,castle,factory,clock}"_red.png" \
    "temp/"{crown,leaf,lightbulb,castle,factory,clock}"_green.png" \
    "temp/"{crown,leaf,lightbulb,castle,factory,clock}"_yellow.png" \
    "temp/"{crown,leaf,lightbulb,castle,factory,clock}"_purple.png" \
    "temp/"{crown,leaf,lightbulb,castle,factory,clock}"_white.png" \
-tile 6x6 -geometry +2+2 -background 'white' "../../img/resource_icons.jpg"

rm -r temp
