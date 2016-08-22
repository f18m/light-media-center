#!/bin/bash
# Light Media Center 
# https://github.com/f18m/light-media-center
#
# Main control loop CONFIGURATION CONSTANTS


# CONFIGURATION:

PORTAL_NAME="Light Media Center";

# services to start as soon as the external disk is mounted:
enable_rtorrent=false
enable_aria2=true
enable_mldonkey=true
enable_minidlna=true
enable_samba=true
enable_noip2=true

daemon_user="debian"
daemon_user_uid="$(id -u $daemon_user)"
daemon_user_gid="$(id -g $daemon_user)"

use_systemctl=false

# see my notes about best partitioning for Light Media Center:
MAIN1disk=1
disklabel[$MAIN1disk]="LMC"
disktype[$MAIN1disk]="xfs"
target[$MAIN1disk]="/media/extdisc"
targetcheck[$MAIN1disk]="${target[$MAIN1disk]}/.in-download/torrents"

if [[ ${disktype[$MAIN1disk]} == "ext3" ]]; then
    mountopt[$MAIN1disk]="rw,noatime,nodiratime,errors=remount-ro"   # for ext
elif [[ ${disktype[$MAIN1disk]} == "xfs" ]]; then
    mountopt[$MAIN1disk]="rw"   # for xfs
fi


# MAIN2disk=2
# disklabel[$MAIN2disk]="MAIN2"
# disktype[$MAIN2disk]="vfat"
# mountopt[$MAIN2disk]="rw,exec,gid=$daemon_user_gid,uid=$daemon_user_uid,umask=000"                  # for vfat and ntfs
# target[$MAIN2disk]="/media/extdiscMAIN2"
# targetcheck[$MAIN2disk]="${target[$MAIN2disk]}/films"

# MAIN3disk=3
# disklabel[$MAIN3disk]="MAIN3"
# disktype[$MAIN3disk]="vfat"
# mountopt[$MAIN3disk]="rw,exec,gid=$daemon_user_gid,uid=$daemon_user_uid,umask=000"                  # for vfat and ntfs
# target[$MAIN3disk]="/media/extdiscMAIN3"
# targetcheck[$MAIN3disk]="${target[$MAIN3disk]}/films"

# TORRENTSdisk=4
# disklabel[$TORRENTSdisk]="TORRENTS"
# disktype[$TORRENTSdisk]="ext4"
# mountopt[$TORRENTSdisk]="rw,noatime,nodiratime,errors=remount-ro"   # for ext
# target[$TORRENTSdisk]="/media/extdiscTORRENTS"
# targetcheck[$TORRENTSdisk]="${target[$TORRENTSdisk]}/torrents"

num_disks=1

LOG_ENABLED=y
preferredTZ="Europe/Rome"

# operation intervals, in seconds
interval_long=600                   # 10min
interval_noextdisk=30
interval_short=5



# NOTE: "main" disk is the one using a filesystem compatible with Windows and where the downloaded torrents are moved.

function classify_currentdisk {

    CURRENTdisk=$1

    if [[ "$CURRENTdisk" = "$MAIN1disk" ]]; then
        currentdisk_is_maindisk=true
    elif [[ "$CURRENTdisk" = "$MAIN2disk" ]]; then
        currentdisk_is_maindisk=true
    elif [[ "$CURRENTdisk" = "$MAIN3disk" ]]; then
        currentdisk_is_maindisk=true
    else
        currentdisk_is_maindisk=false
    fi
}


