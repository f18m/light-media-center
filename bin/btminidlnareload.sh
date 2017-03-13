#!/bin/bash

# CONFIGURATION:

source /opt/light-media-center/bin/inc/btmain.inc.sh
LOG_FILE="/var/log/btminidlnareload.log"



# UTILS:

nmounted=0

function count_mounted_partitions {
    for (( CURRENTpart=1; CURRENTpart <= $num_disks; CURRENTpart++ )); do
            
        classify_currentdisk $CURRENTpart
        if [[ -d "${targetcheck[$CURRENTpart]}" ]]; then
        
            # this partition/disk is correctly mounted:
            nmounted=$(( $nmounted + 1 ))
            msg "Found partition ${targetcheck[$CURRENTpart]}... nmounted=$nmounted"
        fi
    done
}



# IMPLEMENTATION:

source /opt/light-media-center/bin/inc/bsfl
START=`now`

# first of all, shutdown all services relying on ext discs:
msg '***************************************************************************'
count_mounted_partitions

if (( $nmounted > 0 )); then
    msg "Found $nmounted partitions mounted... regenerating minidlna DB"
    /etc/init.d/minidlna force-reload >$LOG_FILE 2>&1
else
    msg 'No mounted partitions found... skipping minidlna DB regeneration'
fi
