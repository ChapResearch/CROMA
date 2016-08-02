#!/bin/sh

################# /home/pics/scripts/getMedia.sh ###########################
# this is the script called by postfix upon receiving any mail to          #
# pics@chapresearch.com. It is used to process the attachments and put     #
# them in a separate file for Drupal to process (with getIncomingMedia.php #
############################################################################

# script scope vars
mydebug=1

# This script will be called by postfix using the receiving user rights.
# This has an effective umask of 0177./
# We need group access so umask accordingly.
#
umask 0002

# message log
msglog="/home/pics/postfix-messages.log"

# $1 log text
# $2 exit status
#
mylog()
{
    echo "$(date) ${1:-missing}" >> $msglog

    # exit with given status code
    if [ $# -ge "2" ]; then
	    echo "quitting" >> $msglog
	    exit $2
	    fi
}
 
# redirect stdout and std err to append to file (debug only)
if [ $mydebug -eq 0 ]; then
    exec >> $msglog 2>&1
fi

    # create a random number (in order to make a unique filename)
    randNum=$(shuf -i 1-100 -n 1)

    # make message dir using process id and unix time in seconds since 1970
    outputPath='/home/pics/imageimport/'
    dirName="postfix-msg-$$-$randNum-$(date +%s)"
    msgDir=$outputPath$dirName
    if ! mkdir -m 777 $msgDir ; then mylog "cannot make ${msgdir}" 0 ; fi
    chmod g+s $msgDir
    if ! cd $msgDir ; then mylog "cannot cd to ${msgdir}" 0 ; fi
    fileName="$msgDir/message"

    # create the file
    cat > $fileName

    # decode all attachments encoded within the message
    munpack -C $msgDir $fileName -t 2>>$msglog
    mylog "just decoded $fileName"
    chmod 767 *

    # check exit status of last command
    if [ "$?" -ne "0" ]; then
	mylog "error decoding attachments in $msgfile" 0
    fi

mylog "message import complete!" 

# tell postfix all OK
exit 0
