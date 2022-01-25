#!/bin/bash
# Setup
mkdir temp

### ECHOES ###

# Coordinates for circles
# First coord pair is center, second is any point the radius away from the center
TOP_LEFT="134,122 134,45"
BOTTOM_LEFT="137,414 137,337"
BOTTOM_CENTER="380,414 380,337"
BOTTOM_RIGHT="615,414 615,337"

# Coordinates for ellipses
# First coord pair is center, second is xradius, yradius, third is start and end of arc in degrees
TOP_LEFT_ELLIPSE="134,122 40,45 0,360"
BOTTOM_LEFT_ELLIPSE="137,414 40,45 0,360"
BOTTOM_CENTER_ELLIPSE="380,414 40,45 0,360"
BOTTOM_RIGHT_ELLIPSE="615,414 40,45 0,360"


# File paths/prefixes
READ_PATH="../cards/Print_EchoesCards_front/Print_EchoesCards_front-"

# Extracts shape from a base card and places in a colored border
# arg1: input filename
# arg2: position of shape in card
# arg4: output filename
# example: extract_circle "002" "$BOTTOM_RIGHT" "000"

extract_circle()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "circle ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$3_circle.png"
}

extract_ellipse()
{
    magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "ellipse ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$3_ellipse.png"
}

echo "Extracting echoes circle icons..."

# Extract blue, red, green, yellow, purple bonus circles
extract_circle "013" "$BOTTOM_LEFT" "1"
extract_circle "008" "$BOTTOM_LEFT"  "2"
extract_circle "059" "$BOTTOM_LEFT"  "3"
extract_circle "003" "$BOTTOM_LEFT"  "4"
extract_circle "022" "$BOTTOM_LEFT"  "5"

# Extract numbers 1-11 
extract_ellipse "002" "$BOTTOM_RIGHT_ELLIPSE" "1"
extract_ellipse "003" "$BOTTOM_LEFT_ELLIPSE"  "2"
extract_ellipse "012" "$BOTTOM_CENTER_ELLIPSE"  "3"
extract_ellipse "034" "$BOTTOM_CENTER_ELLIPSE"  "4"
extract_ellipse "042" "$BOTTOM_LEFT_ELLIPSE"  "5"
extract_ellipse "054" "$BOTTOM_RIGHT_ELLIPSE"  "6"
extract_ellipse "058" "$BOTTOM_RIGHT_ELLIPSE"  "7"
extract_ellipse "078" "$TOP_LEFT_ELLIPSE"  "8"
extract_ellipse "079" "$BOTTOM_CENTER_ELLIPSE"  "9"
extract_ellipse "091" "$BOTTOM_RIGHT_ELLIPSE"  "10"
extract_ellipse "104" "$BOTTOM_RIGHT_ELLIPSE"  "11"

# Build row of circular bonus icons
magick montage \
  temp/{1..5}_circle.png \
  -trim -tile 5x1 -geometry +5+5 -background 'none' temp/bonus_circles.png

# Build row of number ellipses
magick montage \
  temp/{1..11}_ellipse.png \
  -trim -tile 11x1 -geometry +5+5 -background 'none' temp/bonus_numbers.png

# Combine all images into a single spritesheet.
magick montage \
  temp/bonus_circles.png \
  temp/bonus_numbers.png \
  -tile 1x2 -geometry +0+0 -background 'none' ../../img/circle_icons.png

echo "Cleaning up..."

# Cleanup
rm -r temp