#!/bin/bash 

cd /home/oac/connectors/
git checkout master
git pull
date >> /home/oac/logs/build.log

