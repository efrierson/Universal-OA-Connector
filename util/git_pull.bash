#!/bin/bash 

if [ "$1" == "develop" ]; then
    BRANCH="develop"
else
    BRANCH="master"
fi


cd ~/connectors/
git checkout $BRANCH
git pull
echo "Pulled branch $BRANCH on:" >> ~/logs/build.log
date >> ~/logs/build.log

