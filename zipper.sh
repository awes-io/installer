#!/usr/bin/env bash
wget https://github.com/awes-io/awes-io/archive/master.zip
unzip master.zip -d working
cd working/awes-io-master
composer install
zip -ry ../../awes-io-craft.zip .
cd ../..
mv awes-io-craft.zip public/awes-io-craft.zip
rm -rf working
rm master.zip
