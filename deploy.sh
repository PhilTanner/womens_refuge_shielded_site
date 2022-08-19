#! /bin/bash
# A modification of Sal Ferrarello's deploy script as found here: https://github.com/salcode/stop-emails/blob/master/deploy.sh

# main config
PLUGINSLUG="womens_refuge_shielded_site" # Used for main plugin filename, as well as SVN repo link.
CURRENTDIR=`pwd`
MAINFILE="$PLUGINSLUG.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG/" # Remote SVN repo on wordpress.org, with no trailing slash
SVNUSER=`grep "^Contributors:" $GITPATH/README.txt | awk -F' ' '{print $NF}'` # This won't work if there's more than one or a comma etc.
#SVNUSER="fychan66" # your svn username (uncomment & use instead if you have >1 contributor)


# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy wordpress plugin"
echo
echo ".........................................."
echo

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
NEWVERSION1=`grep "^Stable tag:" $GITPATH/README.txt | awk -F' ' '{print $NF}'`
echo "README.txt version: $NEWVERSION1"
#echo "$GITPATH$MAINFILE"
NEWVERSION2=`grep "Version:" $GITPATH$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $NEWVERSION2"

# Tweaked to check string equality, as I use semver versioning, not integers.
if [ "$NEWVERSION1" != "$NEWVERSION2" ]
    then
    echo "Version in README.txt & $MAINFILE don't match. Exiting....";
    read -n 1 -s -r -p "Press any key to continue";
    exit 1;
fi

echo "Versions match in readme.txt and $MAINFILE. Let's proceed..."

if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
    then
		echo "Version $NEWVERSION1 already exists as git tag. Exiting....";
    read -n 1 -s -r -p "Press any key to continue";
		exit 1;
	else
		echo "Git version does not exist. Let's proceed..."
fi

cd $GITPATH
echo -e "Enter a commit message for this new version: \c"
read COMMITMSG
if [ "$COMMITMSG" = "" ]
    then
    echo "No commit message. Exiting....";
    read -n 1 -s -r -p "Press any key to continue"
fi

git commit -am "$COMMITMSG"

echo "Tagging new version in git"
git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

echo "Pushing latest commit to origin, with tags"
git push origin main
git push origin main --tags

echo
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Ignoring github specific files and deployment script"
svn propset svn:ignore "deploy.sh
README.md
.git
.gitignore" "$SVNPATH/trunk/"

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn commit --username=$SVNUSER -m "$COMMITMSG"

echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/

echo "*** FIN ***"
read -n 1 -s -r -p "Press any key to continue"
