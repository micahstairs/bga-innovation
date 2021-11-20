#!/bin/bash
mkdir temp

mogrify -path temp -resize 110x150 \
    ../cards/Print_BaseCards_back/Print_BaseCards_back-0{0..9}6.png ../cards/Print_ArtifactsCards_back/Print_ArtifactsCards_back-106.png \
    ../cards/Print_ArtifactsCards_back/Print_ArtifactsCards_back-0{0..9}6.png ../cards/Print_ArtifactsCards_back/Print_ArtifactsCards_back-109.png \
    ../cards/Print_CitiesCards_back/Print_CitiesCards_back-0{0..9}6.png ../cards/Print_ArtifactsCards_back/Print_ArtifactsCards_back-108.png \
    ../cards/Print_EchoesCards_back/Print_EchoesCards_back-0{0..9}6.png ../cards/Print_ArtifactsCards_back/Print_ArtifactsCards_back-107.png \
    ../cards/Print_FiguresCards_back/Print_FiguresCards_back-0{0..9}6.png ../cards/Print_ArtifactsCards_back/Print_ArtifactsCards_back-110.png

magick montage \
    temp/Print_BaseCards_back-0{0..9}6.png temp/Print_ArtifactsCards_back-106.png \
    temp/Print_ArtifactsCards_back-0{0..9}6.png temp/Print_ArtifactsCards_back-109.png \
    temp/Print_CitiesCards_back-0{0..9}6.png temp/Print_ArtifactsCards_back-108.png \
    temp/Print_EchoesCards_back-0{0..9}6.png temp/Print_ArtifactsCards_back-107.png \
    temp/Print_FiguresCards_back-0{0..9}6.png temp/Print_ArtifactsCards_back-110.png \
-tile 11x5 -geometry +3+3 -background 'none' ../../img/card_backs_portrait.jpg

rm -r temp
