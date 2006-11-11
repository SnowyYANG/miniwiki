#!/bin/bash

# $Id: bump_version.sh 79 2004-11-11 23:26:24Z src $

version=$1
sed 's/\(define("MW_VERSION", "\)[^"]\+/\1'$version'/' lib/miniwiki.php > lib/miniwiki.php.tmp
mv lib/miniwiki.php.tmp lib/miniwiki.php
sed 's/\(PROJECT_NUMBER.*=\).*$/\1 '$version'/' Doxyfile > Doxyfile.tmp
mv Doxyfile.tmp Doxyfile
sed 's/\(miniWiki \).*\( (c)\)/\1'$version'\2/' README > README.tmp
mv README.tmp README
