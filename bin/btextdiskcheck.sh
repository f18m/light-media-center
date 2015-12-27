#!/bin/bash

# CONFIGURATION:

source /opt/light-media-center/bin/inc/btmain.inc.sh
LOG_FILE="/var/log/btextdiskcheck.log"



# UTILS:

function check_current_disc {
    CURRENTdiskLABEL="/dev/disk/by-label/${disklabel[$CURRENTdisk]}"
    found=false
    
    msg "Checking for existence of symlink $CURRENTdiskLABEL"
    if [[ -h "$CURRENTdiskLABEL" ]]; then
        found=true
    fi
    
    if [ "$found" = true ]; then
    
        msg "Found partition $CURRENTdiskLABEL... checking it (should be ${disktype[$CURRENTdisk]})"
        
        umount "$CURRENTdiskLABEL" 2>&1 >>$LOG_FILE
        sleep 1
        
        if [[ "${disktype[$CURRENTdisk]}" == "ext4" ]]; then
            #e2fsck -pf /dev/sda5
            fsck_out=$( /sbin/e2fsck -Dftvy "$CURRENTdiskLABEL" 2>&1 )
        elif [[ "${disktype[$CURRENTdisk]}" == "ntfs" ]]; then
            fsck_out=$( /bin/ntfsfix "$CURRENTdiskLABEL" 2>&1 )
        else
            fsck_out=$( /sbin/fsck -pfv "$CURRENTdiskLABEL" 2>&1 )
        fi
        
        msg "$fsck_out"
    fi
}




# IMPLEMENTATION:

source /opt/light-media-center/bin/bsfl
START=`now`

# first of all, shutdown all services relying on ext discs:
/opt/light-media-center/bin/btsafe_shutdown_services.sh        
msg '***************************************************************************'
for (( CURRENTdisk=1 ; CURRENTdisk <= $num_disks ; CURRENTdisk++ )); do
    check_current_disc
    msg '  ---------------  '
done  

msg 'Restarting the main $PORTAL_NAME control loop'
/etc/init.d/btmain restart
