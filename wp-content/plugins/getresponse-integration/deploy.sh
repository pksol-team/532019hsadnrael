#!/bin/sh

TMP_PATH="`pwd`/tmp"
GIT_PATH=`pwd`
SVN_PATH="$TMP_PATH/svn"

mkdir -p $TMP_PATH

# ASK INFO
echo "--------------------------------------------"
echo "      Github to WordPress.org RELEASER      "
echo "--------------------------------------------"
read -p "TAG AND RELEASE VERSION: " VERSION
echo "--------------------------------------------"
echo ""
echo "Before continuing, confirm that you have done the following :)"
echo ""
read -p " - Added a changelog for "$VERSION" to README.txt file?"
read -p " - Set version in the main file to "$VERSION"?"
read -p " - Set stable tag in the README.txt file to "$VERSION"?"
read -p " - Committed all changes up to GitLab?"
echo ""

# check, if GitLab tag exists
git ls-remote --exit-code --tags origin $VERSION >/dev/null 2>&1
if ! [ $? == 0 ]
then
    echo "Tag $VERSION in GitLab not found"
    exit 1
fi

# validate version in files
GIT_VERSION=`grep "^Stable tag:" $GIT_PATH/readme.txt | awk -F' ' '{print $NF}'`
if [ "$VERSION" != "$GIT_VERSION" ]
then
    echo "Version in README.txt don't match. Exiting."
    exit 1
fi

read -p "PRESS [ENTER] TO BEGIN RELEASING "$VERSION
clear

if ! [ -d "$SVN_PATH" ]
then
    echo "Clone SVN repository... this may take a while"
    svn co https://plugins.svn.wordpress.org/getresponse-integration $SVN_PATH >/dev/null 2>&1
fi

echo "Clear trunk directory"
rm -Rf "$SVN_PATH/trunk" && mkdir "$SVN_PATH/trunk"

echo "Copy files trunk"
git checkout master
git pull
git archive master | tar -x -C "$SVN_PATH/trunk"

if [ -d "$SVN_PATH/tags/$VERSION" ]
then 
    rm -Rf "$SVN_PATH/tags/$VERSION"
fi

echo "Create new tag (tags/$VERSION)"
mkdir -p "$SVN_PATH/tags/$VERSION"
cp -r "$SVN_PATH/trunk/" "$SVN_PATH/tags/$VERSION"

echo ""
echo  "Add new files to SVN repository"
cd $SVN_PATH && svn add tags/$VERSION

echo ""
echo "SVN Status:"
cd $SVN_PATH && svn status

echo ""
read -p "Press [ENTER] to commit release $VERSION to Wordpress.org"
echo ""

echo "Commiting to Wordpress.org... this may take a while"
cd $SVN_PATH && svn commit -m "Release $VERSION" || { echo "Unable to commit."; exit 1; }

echo "Release done."