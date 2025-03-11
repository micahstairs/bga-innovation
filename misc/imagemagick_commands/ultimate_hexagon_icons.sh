#!/bin/bash

# Setup
mkdir temp

FILE_SUFFIX=".png"

# Extracts hexagon from a card and places in a colored border
# arg1: input filename
# arg2: position of hexagon in card
# arg3: output filename
# example: extract_hexagon "010" "$TL" "000"
extract_hexagon()
{
  magick "${READ_PATH}$1.png" \( +clone -fill Black -colorize 100 -fill White -draw "polygon ${2}" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/$3${FILE_SUFFIX}"
}

# Coordinates for hexagons
TL="102,39 39,150 102,260 231,260 294,150 231,39" # Top left
BL="103,488 39,599 103,710 230,710 294,599 230,488" # Bottom left
BC="461,488 397,599 461,710 588,710 652,599 588,488" # Bottom center
BR="819,488 755,599 819,710 946,710 1010,599 946,488" # Bottom right
TR="819,39 755,150 819,260 946,260 1010,150 946,39" # Top right

# Extract base hexagons
echo "Extracting base hexagons..."
READ_PATH="../ultimate/Base/Base"

# Age 1
extract_hexagon "10" "$TL" "000"
extract_hexagon "11" "$TL" "001"
extract_hexagon "12" "$TL" "002"
extract_hexagon "1" "$BC" "003"
extract_hexagon "2" "$BC" "004"
extract_hexagon "3" "$BC" "005"
extract_hexagon "7" "$TL" "006"
extract_hexagon "8" "$BC" "007"
extract_hexagon "9" "$TL" "008"
extract_hexagon "4" "$TL" "009"
extract_hexagon "5" "$BC" "010"
extract_hexagon "6" "$BL" "011"
extract_hexagon "13" "$TL" "012"
extract_hexagon "14" "$TL" "013"
extract_hexagon "15" "$TL" "014"

# Age 2
extract_hexagon "22" "$TL" "015"
extract_hexagon "23" "$TL" "016"
extract_hexagon "16" "$BL" "017"
extract_hexagon "17" "$BC" "018"
extract_hexagon "20" "$BC" "019"
extract_hexagon "21" "$TL" "020"
extract_hexagon "18" "$TL" "021"
extract_hexagon "19" "$BC" "022"
extract_hexagon "24" "$TL" "023"
extract_hexagon "25" "$TL" "024"

# Age 3
extract_hexagon "32" "$TL" "025"
extract_hexagon "33" "$TL" "026"
extract_hexagon "26" "$BL" "027"
extract_hexagon "27" "$BR" "028"
extract_hexagon "30" "$TL" "029"
extract_hexagon "31" "$TL" "030"
extract_hexagon "28" "$BC" "031"
extract_hexagon "29" "$BR" "032"
extract_hexagon "34" "$BR" "033"
extract_hexagon "35" "$TL" "034"

# Age 4
extract_hexagon "42" "$TL" "035"
extract_hexagon "43" "$TL" "036"
extract_hexagon "36" "$TL" "037"
extract_hexagon "37" "$TL" "038"
extract_hexagon "40" "$TL" "039"
extract_hexagon "41" "$TL" "040"
extract_hexagon "38" "$BR" "041"
extract_hexagon "39" "$TL" "042"
extract_hexagon "44" "$TL" "043"
extract_hexagon "45" "$BC" "044"

# Age 5
extract_hexagon "52" "$BR" "045"
extract_hexagon "53" "$BR" "046"
extract_hexagon "46" "$BR" "047"
extract_hexagon "47" "$BR" "048"
extract_hexagon "50" "$BC" "049"
extract_hexagon "51" "$BR" "050"
extract_hexagon "48" "$BR" "051"
extract_hexagon "49" "$TL" "052"
extract_hexagon "54" "$BR" "053"
extract_hexagon "55" "$BL" "054"

# Age 6
extract_hexagon "62" "$BR" "055"
extract_hexagon "63" "$TL" "056"
extract_hexagon "56" "$BR" "057"
extract_hexagon "57" "$BC" "058"
extract_hexagon "60" "$BR" "059"
extract_hexagon "61" "$TL" "060"
extract_hexagon "58" "$TL" "061"
extract_hexagon "59" "$BR" "062"
extract_hexagon "64" "$BR" "063"
extract_hexagon "65" "$BR" "064"

# Age 7
extract_hexagon "72" "$BR" "065"
extract_hexagon "73" "$TL" "066"
extract_hexagon "66" "$BR" "067"
extract_hexagon "67" "$TL" "068"
extract_hexagon "70" "$BR" "069"
extract_hexagon "71" "$BC" "070"
extract_hexagon "68" "$TL" "071"
extract_hexagon "69" "$BC" "072"
extract_hexagon "74" "$TL" "073"
extract_hexagon "75" "$BR" "074"

# Age 8
extract_hexagon "82" "$BR" "075"
extract_hexagon "83" "$BR" "076"
extract_hexagon "76" "$BL" "077"
extract_hexagon "77" "$TL" "078"
extract_hexagon "80" "$TL" "079"
extract_hexagon "81" "$BL" "080"
extract_hexagon "78" "$BR" "081"
extract_hexagon "79" "$TL" "082"
extract_hexagon "84" "$BR" "083"
extract_hexagon "85" "$BL" "084"

# Age 9
extract_hexagon "92" "$BL" "085"
extract_hexagon "93" "$BR" "086"
extract_hexagon "86" "$BC" "087"
extract_hexagon "87" "$TL" "088"
extract_hexagon "90" "$TL" "089"
extract_hexagon "91" "$TL" "090"
extract_hexagon "88" "$BR" "091"
extract_hexagon "89" "$TL" "092"
extract_hexagon "94" "$TL" "093"
extract_hexagon "95" "$TL" "094"

# Age 10
extract_hexagon	"102" "$BR" "095"
extract_hexagon	"103" "$BR" "096"
extract_hexagon	"96" "$TL" "097"
extract_hexagon	"97" "$TL" "098"
extract_hexagon	"100" "$TL" "099"
extract_hexagon	"101" "$TL" "100"
extract_hexagon	"98" "$TL" "101"
extract_hexagon	"99" "$TL" "102"
extract_hexagon	"104" "$BR" "103"
extract_hexagon	"105" "$TL" "104"

# Age 11
extract_hexagon	"112" "$BL" "105"
extract_hexagon	"113" "$BR" "106"
extract_hexagon	"106" "$BL" "107"
extract_hexagon	"107" "$BR" "108"
extract_hexagon	"110" "$BR" "109"
extract_hexagon	"111" "$BR" "110"
extract_hexagon	"108" "$TL" "111"
extract_hexagon	"109" "$BC" "112"
extract_hexagon	"114" "$BL" "113"
extract_hexagon	"115" "$BL" "114"

# Combine base hexagons into a single spritesheet
echo "Combining base hexagons..."
magick montage \
temp/00{0..9}.png \
temp/0{10..99}.png \
temp/{100..114}.png \
-trim -tile 115x1 -geometry 60x60+5+5 -background 'none' temp/ultimate_base_hexagons.png

# Extract artifacts hexagons
echo "Extracting artifacts hexagons..."
READ_PATH="../ultimate/Artifacts/Artifacts"

# Age 1
extract_hexagon "10" "$BC" "000"
extract_hexagon "11" "$BR" "001"
extract_hexagon "12" "$BL" "002"
extract_hexagon "1" "$BC" "003"
extract_hexagon "2" "$BR" "004"
extract_hexagon "3" "$TL" "005"
extract_hexagon "7" "$BL" "006"
extract_hexagon "8" "$BL" "007"
extract_hexagon "9" "$TL" "008"
extract_hexagon "4" "$TL" "009"
extract_hexagon "5" "$TL" "010"
extract_hexagon "6" "$TL" "011"
extract_hexagon "13" "$BC" "012"
extract_hexagon "14" "$TL" "013"
extract_hexagon "15" "$TL" "014"

# Age 2
extract_hexagon "22" "$BR" "015"
extract_hexagon "23" "$BR" "016"
extract_hexagon "16" "$BL" "017"
extract_hexagon "17" "$TL" "018"
extract_hexagon "20" "$BC" "019"
extract_hexagon "21" "$BC" "020"
extract_hexagon "18" "$BL" "021"
extract_hexagon "19" "$BR" "022"
extract_hexagon "24" "$TL" "023"
extract_hexagon "25" "$TL" "024"

# Age 3
extract_hexagon "32" "$BC" "025"
extract_hexagon "33" "$BC" "026"
extract_hexagon "26" "$BL" "027"
extract_hexagon "27" "$TL" "028"
extract_hexagon "30" "$BR" "029"
extract_hexagon "31" "$BC" "030"
extract_hexagon "28" "$BC" "031"
extract_hexagon "29" "$BR" "032"
extract_hexagon "34" "$TL" "033"
extract_hexagon "35" "$BL" "034"

# Age 4
extract_hexagon "42" "$BL" "035"
extract_hexagon "43" "$BL" "036"
extract_hexagon "36" "$BR" "037"
extract_hexagon "37" "$BR" "038"
extract_hexagon "40" "$BR" "039"
extract_hexagon "41" "$BC" "040"
extract_hexagon "38" "$BC" "041"
extract_hexagon "39" "$TL" "042"
extract_hexagon "44" "$BC" "043"
extract_hexagon "45" "$BL" "044"

# Age 5
extract_hexagon "52" "$BR" "045"
extract_hexagon "53" "$BC" "046"
extract_hexagon "46" "$TL" "047"
extract_hexagon "47" "$BC" "048"
extract_hexagon "50" "$BL" "049"
extract_hexagon "51" "$BL" "050"
extract_hexagon "48" "$BC" "051"
extract_hexagon "49" "$BR" "052"
extract_hexagon "54" "$BR" "053"
extract_hexagon "55" "$BL" "054"

# Age 6
extract_hexagon "62" "$BL" "055"
extract_hexagon "63" "$BL" "056"
extract_hexagon "56" "$TL" "057"
extract_hexagon "57" "$BR" "058"
extract_hexagon "60" "$BC" "059"
extract_hexagon "61" "$BR" "060"
extract_hexagon "58" "$TL" "061"
extract_hexagon "59" "$TL" "062"
extract_hexagon "64" "$BC" "063"
extract_hexagon "65" "$BC" "064"

# Age 7
extract_hexagon "72" "$BL" "065"
extract_hexagon "73" "$BC" "066"
extract_hexagon "66" "$BR" "067"
extract_hexagon "67" "$TL" "068"
extract_hexagon "70" "$BL" "069"
extract_hexagon "71" "$BR" "070"
extract_hexagon "68" "$BL" "071"
extract_hexagon "69" "$TL" "072"
extract_hexagon "74" "$BR" "073"
extract_hexagon "75" "$BC" "074"

# Age 8
extract_hexagon "82" "$BR" "075"
extract_hexagon "83" "$BL" "076"
extract_hexagon "76" "$TL" "077"
extract_hexagon "77" "$BL" "078"
extract_hexagon "80" "$BR" "079"
extract_hexagon "81" "$BC" "080"
extract_hexagon "78" "$BR" "081"
extract_hexagon "79" "$TL" "082"
extract_hexagon "84" "$TL" "083"
extract_hexagon "85" "$BR" "084"

# Age 9
extract_hexagon "92" "$BR" "085"
extract_hexagon "93" "$BL" "086"
extract_hexagon "86" "$TL" "087"
extract_hexagon "87" "$BR" "088"
extract_hexagon "90" "$TL" "089"
extract_hexagon "91" "$TL" "090"
extract_hexagon "88" "$BC" "091"
extract_hexagon "89" "$BC" "092"
extract_hexagon "94" "$BL" "093"
extract_hexagon "95" "$BR" "094"

# Age 10
extract_hexagon "102" "$TL" "095"
extract_hexagon "103" "$BR" "096"
extract_hexagon "96" "$BL" "097"
extract_hexagon "97" "$BL" "098"
extract_hexagon "100" "$BC" "099"
extract_hexagon "101" "$BR" "100"
extract_hexagon "98" "$TL" "101"
extract_hexagon "99" "$BC" "102"
extract_hexagon "104" "$TL" "103"
extract_hexagon "105" "$TL" "104"

# Age 11
extract_hexagon	"112" "$BL" "105"
extract_hexagon	"113" "$BR" "106"
extract_hexagon	"106" "$BR" "107"
extract_hexagon	"107" "$TL" "108"
extract_hexagon	"110" "$BC" "109"
extract_hexagon	"111" "$BL" "110"
extract_hexagon	"108" "$TL" "111"
extract_hexagon	"109" "$BL" "112"
extract_hexagon	"114" "$TL" "113"
extract_hexagon	"115" "$BR" "114"

# Combine artifacts hexagons into a single spritesheet
echo "Combining artifacts hexagons..."
magick montage \
temp/00{0..9}.png \
temp/0{10..99}.png \
temp/{100..114}.png \
-trim -tile 115x1 -geometry 60x60+5+5 -background 'none' temp/ultimate_artifacts_hexagons.png

# Extract cities hexagons
echo "Extracting cities hexagons..."
READ_PATH="../ultimate/Cities/Cities"

# Age 1
extract_hexagon "1" "$TR" "000"
extract_hexagon "13" "$TR" "001"
extract_hexagon "4" "$TR" "002"
extract_hexagon "7" "$TR" "003"
extract_hexagon "10" "$TR" "004"

# Age 2
extract_hexagon "16" "$TR" "005"
extract_hexagon "24" "$TR" "006"
extract_hexagon "18" "$TR" "007"
extract_hexagon "20" "$TR" "008"
extract_hexagon "22" "$TR" "009"

# Age 3
extract_hexagon "26" "$TR" "010"
extract_hexagon "34" "$TR" "011"
extract_hexagon "28" "$TR" "012"
extract_hexagon "30" "$TR" "013"
extract_hexagon "32" "$TR" "014"

# Age 4
extract_hexagon "36" "$TR" "015"
extract_hexagon "44" "$TR" "016"
extract_hexagon "38" "$TR" "017"
extract_hexagon "40" "$TR" "018"
extract_hexagon "42" "$TR" "019"

# Age 5
extract_hexagon "46" "$TR" "020"
extract_hexagon "54" "$TR" "021"
extract_hexagon "48" "$TR" "022"
extract_hexagon "50" "$TR" "023"
extract_hexagon "52" "$TR" "024"

# Age 6
extract_hexagon "56" "$TR" "025"
extract_hexagon "64" "$TR" "026"
extract_hexagon "58" "$TR" "027"
extract_hexagon "60" "$TR" "028"
extract_hexagon "62" "$TR" "029"

# Age 7
extract_hexagon "66" "$TR" "030"
extract_hexagon "74" "$TR" "031"
extract_hexagon "68" "$TR" "032"
extract_hexagon "70" "$TR" "033"
extract_hexagon "72" "$TR" "034"

# Age 8
extract_hexagon "76" "$TR" "035"
extract_hexagon "84" "$TR" "036"
extract_hexagon "78" "$TR" "037"
extract_hexagon "80" "$TR" "038"
extract_hexagon "82" "$TR" "039"

# Age 9
extract_hexagon "86" "$TR" "040"
extract_hexagon "94" "$TR" "041"
extract_hexagon "88" "$TR" "042"
extract_hexagon "90" "$TR" "043"
extract_hexagon "92" "$TR" "044"

# Age 10
extract_hexagon "96" "$TR" "045"
extract_hexagon "104" "$TR" "046"
extract_hexagon "98" "$TR" "047"
extract_hexagon "100" "$TR" "048"
extract_hexagon "102" "$TR" "049"

# Age 11
extract_hexagon "106" "$TR" "050"
extract_hexagon "114" "$TR" "051"
extract_hexagon "108" "$TR" "052"
extract_hexagon "110" "$TR" "053"
extract_hexagon "112" "$TR" "054"

# Since there is extra space on this row, also include two special icons for Echoes
SMALL_HEXAGON="464,248 455,264 464,280 482,280 491,264 482,248"
magick "../ultimate/Echoes/Echoes75.png" \( +clone -fill Black -colorize 100 -fill White -draw "polygon $SMALL_HEXAGON" \) -alpha off -compose CopyOpacity -composite -trim +repage "temp/saxophone_hex.png"
magick temp/saxophone_hex.png -fill black -colorize 100 "temp/black_hex.png"

# Combine cities hexagons into a single spritesheet
echo "Combining cities hexagons..."
magick montage \
temp/00{0..9}.png \
temp/0{10..54}.png \
temp/saxophone_hex.png \
temp/black_hex.png \
-trim -tile 57x1 -geometry 60x60+5+5 -background 'none' temp/ultimate_cities_hexagons.png

# Extract echoes hexagons
echo "Extracting echoes hexagons..."
READ_PATH="../ultimate/Echoes/Echoes"

# Age 1
extract_hexagon "13" "$BL" "000"
extract_hexagon "14" "$BL" "001"
extract_hexagon "15" "$TL" "002"
extract_hexagon "7" "$BR" "003"
extract_hexagon "8" "$BC" "004"
extract_hexagon "9" "$BC" "005"
extract_hexagon "4" "$TL" "006"
extract_hexagon "5" "$BL" "007"
extract_hexagon "6" "$BC" "008"
extract_hexagon "1" "$TL" "009"
extract_hexagon "2" "$BC" "010"
extract_hexagon "3" "$BC" "011"
extract_hexagon "10" "$TL" "012"
extract_hexagon "11" "$BR" "013"
extract_hexagon "12" "$BL" "014"

# Age 2
extract_hexagon "24" "$BC" "015"
extract_hexagon "25" "$BR" "016"
extract_hexagon "20" "$TL" "017"
extract_hexagon "21" "$BL" "018"
extract_hexagon "18" "$BC" "019"
extract_hexagon "19" "$BR" "020"
extract_hexagon "16" "$BL" "021"
extract_hexagon "17" "$TL" "022"
extract_hexagon "22" "$BL" "023"
extract_hexagon "23" "$BR" "024"

# Age 3
extract_hexagon "34" "$TL" "025"
extract_hexagon "35" "$TL" "026"
extract_hexagon "30" "$BL" "027"
extract_hexagon "31" "$BC" "028"
extract_hexagon "28" "$BR" "029"
extract_hexagon "29" "$BC" "030"
extract_hexagon "26" "$TL" "031"
extract_hexagon "27" "$BC" "032"
extract_hexagon "32" "$TL" "033"
extract_hexagon "33" "$BL" "034"

# Age 4
extract_hexagon "44" "$BC" "035"
extract_hexagon "45" "$BC" "036"
extract_hexagon "40" "$BL" "037"
extract_hexagon "41" "$BR" "038"
extract_hexagon "38" "$BR" "039"
extract_hexagon "39" "$TL" "040"
extract_hexagon "36" "$BC" "041"
extract_hexagon "37" "$BC" "042"
extract_hexagon "42" "$BR" "043"
extract_hexagon "43" "$TL" "044"

# Age 5
extract_hexagon "54" "$BC" "045"
extract_hexagon "55" "$BR" "046"
extract_hexagon "50" "$BL" "047"
extract_hexagon "51" "$BR" "048"
extract_hexagon "48" "$BL" "049"
extract_hexagon "49" "$TL" "050"
extract_hexagon "46" "$BR" "051"
extract_hexagon "47" "$BC" "052"
extract_hexagon "52" "$BC" "053"
extract_hexagon "53" "$TL" "054"

# Age 6
extract_hexagon "64" "$TL" "055"
extract_hexagon "65" "$BC" "056"
extract_hexagon "60" "$BC" "057"
extract_hexagon "61" "$TL" "058"
extract_hexagon "58" "$BR" "059"
extract_hexagon "59" "$BL" "060"
extract_hexagon "56" "$BC" "061"
extract_hexagon "57" "$BR" "062"
extract_hexagon "62" "$BL" "063"
extract_hexagon "63" "$BR" "064"

# Age 7
extract_hexagon "74" "$TL" "065"
extract_hexagon "75" "$BC" "066"
extract_hexagon "70" "$BC" "067"
extract_hexagon "71" "$TL" "068"
extract_hexagon "68" "$BR" "069"
extract_hexagon "69" "$BL" "070"
extract_hexagon "66" "$BR" "071"
extract_hexagon "67" "$TL" "072"
extract_hexagon "72" "$BC" "073"
extract_hexagon "73" "$BL" "074"

# Age 8
extract_hexagon "84" "$BC" "075"
extract_hexagon "85" "$BL" "076"
extract_hexagon "80" "$BR" "077"
extract_hexagon "81" "$BL" "078"
extract_hexagon "78" "$TL" "079"
extract_hexagon "79" "$BR" "080"
extract_hexagon "76" "$BC" "081"
extract_hexagon "77" "$BL" "082"
extract_hexagon "82" "$TL" "083"
extract_hexagon "83" "$TL" "084"

# Age 9
extract_hexagon "94" "$TL" "085"
extract_hexagon "95" "$BC" "086"
extract_hexagon "90" "$BR" "087"
extract_hexagon "91" "$BR" "088"
extract_hexagon "88" "$BL" "089"
extract_hexagon "89" "$TL" "090"
extract_hexagon "86" "$BR" "091"
extract_hexagon "87" "$TL" "092"
extract_hexagon "92" "$BL" "093"
extract_hexagon "93" "$BC" "094"

# Age 10
extract_hexagon	"104" "$BC" "095"
extract_hexagon	"105" "$BL" "096"
extract_hexagon	"100" "$BL" "097"
extract_hexagon "101" "$BC" "098"
extract_hexagon "98" "$BL" "099"
extract_hexagon "99" "$BR" "100"
extract_hexagon "96" "$TL" "101"
extract_hexagon "97" "$TL" "102"
extract_hexagon "102" "$TL" "103"
extract_hexagon	"103" "$BR" "104"

# Age 11
extract_hexagon "114" "$TL" "105"
extract_hexagon "115" "$BL" "106"
extract_hexagon "110" "$TL" "107"
extract_hexagon "111" "$BR" "108"
extract_hexagon "108" "$TL" "109"
extract_hexagon "109" "$BC" "110"
extract_hexagon "106" "$BR" "111"
extract_hexagon "107" "$BL" "112"
extract_hexagon "112" "$BR" "113"
extract_hexagon "113" "$TL" "114"

# Combine echoes hexagons into a single spritesheet
echo "Combining echoes hexagons..."
magick montage \
temp/00{0..9}.png \
temp/0{10..99}.png \
temp/{100..114}.png \
-trim -tile 115x1 -geometry 60x60+5+5 -background 'none' temp/ultimate_echoes_hexagons.png

# Combine everything into a single spritesheet
echo "Combining everything into a single spritesheet..."
magick convert \
temp/ultimate_base_hexagons.png \
temp/ultimate_artifacts_hexagons.png \
temp/ultimate_cities_hexagons.png \
temp/ultimate_echoes_hexagons.png \
-append -background 'none' ../../img/ultimate_hexagon_icons.png

# Cleanup
echo "Cleaning up..."
rm -r temp