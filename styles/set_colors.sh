#!/bin/bash

sed -i -e 's/\(COL-DOM.*:\).*/\1 #0F413E;/g' *.css
sed -i -e 's/\(COL-SUB.*:\).*/\1 #74AFAD;/g' *.css
sed -i -e 's/\(COL-BG.*:\).*/\1 #ECECEA;/g' *.css
sed -i -e 's/\(COL-HI.*:\).*/\1 #D9A270;/g' *.css


