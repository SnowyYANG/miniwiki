#!/bin/sh

# $Id$

version=$1
echo Making distribution of miniWiki $version
mkdir packaged
target=packaged/miniwiki-$version
mkdir $target
cp -r index.php userdefs.php README NEWS lib maintenance $target
find $target -type f -name '*~' -exec rm '{}' ';'
find $target -type d -name '.svn' -exec rm -rf '{}' ';'
(cd $target; ../../bump_version.sh $version)
(cd packaged; tar czvf miniwiki-$version.tar.gz miniwiki-$version)
