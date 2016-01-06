#!/bin/bash

# CONFIGURATION:

source /opt/light-media-center/bin/inc/btmain.inc.sh
LOG_FILE="/var/log/btsafeshutdown.log"


# IMPLEMENTATION:

function try_clean_stop {
        if [ "$use_systemctl" = true ] ; then
            service btmain stop
        else
            /etc/init.d/btmain stop
        fi
         #/etc/init.d/rtorrentdaemon stop
         /etc/init.d/aria2 stop
         /etc/init.d/mldonkey-server stop
        if [ "$use_systemctl" = true ] ; then
            service minidlnad stop
        else
            /etc/init.d/minidlna stop
        fi
        if [ "$use_systemctl" = true ] ; then
            service smbd stop
            service nmbd stop
        else
            /etc/init.d/samba stop
        fi
         sleep 4
}

function check_if_something_still_running {
         still_running=""
         pgrep btmain; if [[ "$?" = "0" ]]; then still_running=btmain; return; fi
         pgrep rtorrent; if [[ "$?" = "0" ]]; then still_running=rtorrent; return; fi
         pgrep aria2c; if [[ "$?" = "0" ]]; then still_running=aria2c; return; fi
         pgrep mlnet; if [[ "$?" = "0" ]]; then still_running=mlnet; return; fi
         pgrep minidlna; if [[ "$?" = "0" ]]; then still_running=minidlna; return; fi
}

function do_force_stop {
         killall -9 btmain.sh
         killall -9 rtorrent
         killall -9 aria2c
         killall -9 mlnet
         killall -9 minidlnad
         killall -9 smbd
         killall -9 nmbd
         sleep 2
}



# get arguments
FINAL_ACTION="$1"


# init Bash Shell Function Library (BSFL)
source /opt/light-media-center/bin/inc/bsfl
START=`now`

sleep 2

msg '*** Shutting down all core services in the correct order:'
try_clean_stop
check_if_something_still_running
if [ ! -z "$still_running" ]; then
     msg "*** Detected some service still running: $still_running.... retrying a clean shutdown"
     try_clean_stop  
fi

check_if_something_still_running
if [ ! -z "$still_running" ]; then
     msg "*** Stuff is still running: $still_running... killing them"
     do_force_stop
fi

check_if_something_still_running
if [ ! -z "$still_running" ]; then
     msg "*** Stuff is still running: $still_running... all attempts failed. aborting."
     exit 1
fi

msg '*** All services correctly shut down... unmounting external discs'
umount /media/extdisc*
sleep 5
       
msg '*** Shut down of services completed'
sync
sleep 1

if [[ $FINAL_ACTION == "reboot" ]]; then
    msg '*** Performing final reboot'
    shutdown -r now
elif [[ $FINAL_ACTION == "halt" ]]; then
    msg '*** Performing final halt'
    shutdown -h now
fi

