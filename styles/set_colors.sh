#!/bin/bash

sed -i -e 's/\(COL-DOM.*:\).*/\1 #1933B8;/g' *.css
sed -i -e 's/\(COL-SUB.*:\).*/\1 #6D9EE0;/g' *.css
#sed -i -e 's/\(COL-BG.*:\).*/\1  #E3D3CA;/g' *.css
sed -i -e 's/\(COL-BG.*:\).*/\1  #FFFEF2;/g' *.css
sed -i -e 's/\(COL-FBG.*:\).*/\1 #E3DAD5;/g' *.css
sed -i -e 's/\(COL-HI.*:\).*/\1  #E3A45F;/g' *.css
sed -i -e 's/\(COL-TXT.*:\).*/\1 #170741;/g' *.css
sed -i -e 's/\(COL-WRN.*:\).*/\1 #E00606;/g' *.css


