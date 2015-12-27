#!/bin/bash

# CONFIGURATION:

source /opt/light-media-center/bin/inc/btmain.inc.sh
LOG_FILE="/var/log/btminidlnareload.log"



# UTILS:

nmounted=0

function check_current_disc {
    CURRENTdiskLABEL="/dev/disk/by-label/${disklabel[$CURRENTdisk]}"
    found=false
    
    msg "Checking for existence of symlink $CURRENTdiskLABEL"
    if [[ -h "$CURRENTdiskLABEL" ]]; then
        found=true
    fi
    
    if [ "$found" = true ]; then
    
        msg "Found partition $CURRENTdiskLABEL..."
        (( nmounted++ ))
    fi
}




# IMPLEMENTATION:

source /opt/light-media-center/bin/bsfl
START=`now`

# first of all, shutdown all services relying on ext discs:
msg '***************************************************************************'
for (( CURRENTdisk=1 ; CURRENTdisk <= $num_disks ; CURRENTdisk++ )); do
    check_current_disc
    msg '  ---------------  '
done  

if (( "$nmounted" > "2" )); then
    msg "Found $nmounted partitions mounted... regenerating minidlna DB"
    /etc/init.d/minidlna force-reload >$LOG_FILE 2>&1
else
    msg 'No mounted partitions found... skipping minidlna DB regeneration'
fi
