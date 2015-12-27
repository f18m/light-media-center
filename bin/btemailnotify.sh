#!/bin/bash

# config
IP=`hostname -i`
HOSTNAME=`hostname -f`
EMAILFILE="/tmp/email.txt"
EMAIL="francesco.montorsi@gmail.com"


# implementation
sleep 60
#/bin/systemctl restart sendmail.service
#/sbin/service sendmail restart

echo "$HOSTNAME has just rebooted; local date: $(date)" > $EMAILFILE
#echo "IP address: $IP" >> $EMAILFILE
echo >> $EMAILFILE
echo '<tt>' >> $EMAILFILE

logfile=/var/log/bthwcheck.log
echo "Last 30 lines of $logfile:" >> $EMAILFILE
echo "--------------------------------------" >> $EMAILFILE
tail -n30 $logfile >> $EMAILFILE
echo "--------------------------------------" >> $EMAILFILE

echo >> $EMAILFILE

logfile=/var/log/btsafeshutdown.log
echo "Last 30 lines of $logfile:" >> $EMAILFILE
echo "--------------------------------------" >> $EMAILFILE
tail -n30 $logfile >> $EMAILFILE
echo "--------------------------------------" >> $EMAILFILE


echo >> $EMAILFILE

echo "Last 30 lines of dmesg:" >> $EMAILFILE
echo "--------------------------------------" >> $EMAILFILE
dmesg | tail -n30 >> $EMAILFILE
echo "--------------------------------------" >> $EMAILFILE
echo '</tt>' >> $EMAILFILE

# send

mail -s "$HOSTNAME online" $EMAIL < $EMAILFILE

#cat $EMAILFILE
#rm -f $EMAILFILE

#/bin/systemctl restart sendmail.service
#/sbin/service sendmail restart

