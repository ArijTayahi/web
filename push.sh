#!/bin/bash
cd /c/xampp/htdocs/eya
export GIT_EDITOR=true
git reset --hard HEAD
git push target main --force
