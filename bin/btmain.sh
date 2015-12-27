#!/bin/bash -x 
# Light Media Center main control loop daemon
# Tries to mount external part and if succeeds, runs rTorrent/Aria2 and miniDLNA
# by Francesco Montorsi (c) 2013
# 
# Install in /usr/local/bin and run this at startup via the LSB init script btmain
# NOTE: this script WILL NOT RETURN, RATHER IT WILL RUN AS AN ENDLESS LOOP

# CONFIGURATION:

source /opt/light-media-center/bin/inc/btmain.inc.sh
LOG_FILE="/var/log/btmain.log"


# IMPLEMENTATION - TORRENTS part
# NOTE: "torrent" part is the one using a filesystem good for torrent downloading and thus having a lot of read/writes

function on_torrent_part_mounted {
    begin_new_logline
    if [ "$enable_rtorrent" = true ] ; then
        echo -n "attempting start of rTorrent..." >>$LOG_FILE
        /etc/init.d/rtorrentdaemon restart >>$LOG_FILE
    fi
    if [ "$enable_aria2" = true ] ; then
        echo -n "attempting start of Aria2..." >>$LOG_FILE
        /etc/init.d/aria2 restart >>$LOG_FILE
    fi
    if [ "$enable_mldonkey" = true ] ; then
        echo -n "attempting start of MLdonkey..." >>$LOG_FILE
        /etc/init.d/mldonkey-server restart >>$LOG_FILE
    fi
    if [ "$enable_samba" = true ] ; then
        echo -n "attempting start of SAMBA..." >>$LOG_FILE
        /etc/init.d/samba restart >>$LOG_FILE
    fi
    echo "completed post-mount sequence." >>$LOG_FILE
}

function on_torrent_part_ok {
    if [ "$enable_rtorrent" = true ] ; then
        rtorr_pid=$(pgrep rtorrent)
        if [[ -z $rtorr_pid ]]; then
            echo -n "WARNING: rTorrent down... trying to restart it..." >>$LOG_FILE
            /etc/init.d/rtorrentdaemon restart >>$LOG_FILE
            problem_found=rTorrent
        else
            echo -n "rTorrent OK..." >>$LOG_FILE
        fi
    fi

    if [ "$enable_aria2" = true ] ; then
        aria2_pid=$(pgrep aria2c)
        if [[ -z $aria2_pid ]]; then
            echo -n "WARNING: Aria2 down... trying to restart it..." >>$LOG_FILE
            /etc/init.d/aria2 restart >>$LOG_FILE
            problem_found=Aria2
        else
            echo -n "Aria2 OK..." >>$LOG_FILE
        fi
    fi
    
    if [ "$enable_mldonkey" = true ] ; then
        mlnet_pid=$(pgrep mlnet)
        if [[ -z $mlnet_pid ]]; then
            echo -n "WARNING: MLdonkey down... trying to restart it..." >>$LOG_FILE
            /etc/init.d/mldonkey-server restart >>$LOG_FILE
            problem_found=MLdonkey
        else
            echo -n "MLdonkey OK..." >>$LOG_FILE
        fi
    fi
}


# IMPLEMENTATION - MAIN part
# NOTE: "main" part is the one using a filesystem compatible with Windows and where the downloaded torrents are moved.

function on_main_part_mounted {
    begin_new_logline
    if [ "$enable_minidlna" = true ] ; then
        echo -n "attempting start of miniDLNA..." >>$LOG_FILE
        /etc/init.d/minidlna restart >>$LOG_FILE
    fi
    if [ "$enable_samba" = true ] ; then
        echo -n "attempting start of SAMBA..." >>$LOG_FILE
        /etc/init.d/samba restart >>$LOG_FILE
    fi
    echo "completed post-mount sequence." >>$LOG_FILE
}

function on_main_part_ok {
    if [ "$enable_minidlna" = true ] ; then
        minidlna_pid=$(pgrep minidlna)
        if [[ -z $minidlna_pid ]]; then
            echo -n "WARNING: miniDLNA down... trying to restart it..." >>$LOG_FILE
            /etc/init.d/minidlna restart >>$LOG_FILE
            problem_found=miniDLNA
        else
            echo -n "miniDLNA OK..." >>$LOG_FILE
        fi
    fi
}


# IMPLEMENTATION - GENERIC

function begin_new_logline {
    echo -n "$(date), part=$CURRENTpart: " >>$LOG_FILE
}

function test_write_permission {
    USER="$1"
    DIR="$2"
    
    # ensure the testfile is not already existing
    TESTFILE="$DIR/testfile-bash-script-dummy"
    rm $TESTFILE 2>/dev/null

    # create a temporary file as if we were a different user
    # NOTE: avoid redirection operators which would NOT execute as $USER!!
    su -c "dd if=/dev/zero of=$TESTFILE bs=512 count=1" $USER
    
    # now test if dd was successful:
    if [[ -e $TESTFILE ]]; then
       FILECREATIONOK=true
       rm $TESTFILE
    else
       FILECREATIONOK=false
    fi
}

function on_part_ok {
    #if ! $already_logged; then
        begin_new_logline
        echo -n "${targetcheck[$CURRENTpart]} OK..." >>$LOG_FILE

        problem_found=false
        
        # call part specific function, which will set $problem_found and $interval
        if $currentdisk_is_maindisk; then
            on_main_part_ok
        else
            on_torrent_part_ok
        fi

        if [ "$problem_found" = false ]; then
            interval=$interval_long
            echo "no problems! Checking every $interval""sec." >>$LOG_FILE
            #already_logged=true
        else
            interval=$interval_short
            echo "problems found with $problem_found, will check again in $interval secs." >>$LOG_FILE
        fi
    #fi
}

function on_part_fail {
    interval=$interval_short
    
    # NOTE: -h returns true if the argument is a symbolic link
    found=false
    if [[ -h "/dev/disk/by-label/${disklabel[$CURRENTpart]}" ]]; then
        found=true
    fi

    if [ "$found" = true ]; then

        begin_new_logline    
        echo -n "found part with label ${disklabel[$CURRENTpart]}... attempting mount..." >>$LOG_FILE
        mount -t ${disktype[$CURRENTpart]}  -o ${mountopt[$CURRENTpart]}  /dev/disk/by-label/${disklabel[$CURRENTpart]}  ${target[$CURRENTpart]}
        sleep 1

        if [[ -d "${target[$CURRENTpart]}" ]]; then
                            
            echo -n "testing write permissions on ${targetcheck[$CURRENTpart]}..." >>$LOG_FILE
            test_write_permission $daemon_user ${targetcheck[$CURRENTpart]}
            if [[ $FILECREATIONOK = true ]]; then
                echo "...mount was successful (test of write permissions was OK)!" >>$LOG_FILE
            
                # now that the external HD is in place, we can run services
                
                if $currentdisk_is_maindisk; then
                    on_main_part_mounted
                else
                    on_torrent_part_mounted
                fi

                
            else
                echo "...mount failed (test of write permissions FAILED)!" >>$LOG_FILE
            fi
        fi
    else
        begin_new_logline
        echo -n "no external part detected or no partition ${disklabel[$CURRENTpart]} found: services depending on it will not be started. " >>$LOG_FILE

        interval=$interval_noextdisk
        echo "Decreasing check frequency to $interval sec." >>$LOG_FILE
    fi
}

function run_loop {
    # make sure that we output dates with the timezone we like the most:
    export TZ=$preferredTZ

    # need to run as root otherwise the external part cannot be mounted:
    if [ "$EUID" -ne 0 ]; then 
      msg_fail "Please run as root"
      exit
    fi

    msg "-----------------------------------"
    
    labels=""
    for (( CURRENTpart=1; CURRENTpart<=$num_disks; CURRENTpart++ )); do
        labels="$labels ${disklabel[$CURRENTpart]}"
    done
                
    
    msg "running this script as $(whoami); will search for external part partitions labeled $labels; rTorrent enable is $enable_rtorrent; Aria2 enable is $enable_aria2; miniDLNA enable is $enable_minidlna."

    interval=$interval_short
    while [ 1 ]; do
    
        already_logged=false
        interval_lowest=$interval_long
        for (( CURRENTpart=1; CURRENTpart<=$num_disks; CURRENTpart++ )); do
                
            classify_currentdisk $CURRENTpart
                
            # on_part_ok and on_part_fail will set $interval
            if [[ -d "${targetcheck[$CURRENTpart]}" ]]; then
                on_part_ok
            else
                on_part_fail
            fi
            
            if (( interval < interval_lowest )); then
                # the routine we called asked us to decrease the interval before next check...
                # give that request higher importance:
                interval_lowest=$interval
            fi
        done

        sleep $interval_lowest

        # the log file date is often wrong at begin, when NTP has not synched system date yet...
        # fix the log file date asap:
        touch $LOG_FILE
    done
}

# init Bash Shell Function Library (BSFL)
source /opt/light-media-center/bin/inc/bsfl
START=`now`

run_loop

