#! /usr/bin/env python

"""Gets called whenever an Aria2 download has been completed.

 from 
    http://aria2.sourceforge.net/manual/en/html/aria2c.html#event-hook
    
 Aria2 passes 3 arguments to specified command when it is executed. These arguments are: GID, the number of files and file path. For HTTP, FTP downloads, usually the number of files is 1. BitTorrent download can contain multiple files. If number of files is more than one, file path is first one. In other words, this is the value of path key of first struct whose selected key is true in the response of aria2.getFiles() RPC method. If you want to get all file paths, consider to use JSON-RPC/XML-RPC. Please note that file path may change during download in HTTP because of redirection or Content-Disposition header.

 Example:
 
 Generic hook script:
 
 aria2c --on-download-complete hook.sh http://example.org/file.iso
 hook.sh is called with [1] [1] [/path/to/file.iso]
 
 
 Test this specific script:
 
 aria2c --on-download-complete /home/debian/aria2hooks/move-to-downloaded.py --dir="/media/extdiscTORRENTS/torrents/folder with spaces" http://frm.users.sourceforge.net/macros.html
"""

import sys
import argparse
import xmlrpclib
import os.path
#from rsync import main_rsync
import subprocess
import logging
from pprint import pprint

secret = "token:ubuntu"

mb = 1024*1024;
gb = 1024*1024*1024;
MAX_SIZE_THRESHOLD = 4*gb; 

DL_PREFIX = "/media/extdisc/torrents/"
DEST_PREFIX = ( "/media/extdisc/" )
DEST_FOLDER_NAME = "to_reorder"
LOGFILE = "/var/log/aria2hooks.log"


def main(options):
    logging.basicConfig(filename=LOGFILE,level=logging.DEBUG,format='%(asctime)s - %(levelname)s - %(message)s')

    logging.info("***********************************************************");
    logging.info("move-to-downloaded.py has been called with GID=%s, NFILES=%d, PATH=%s" % (options.GID, options.nfiles, options.path))
    
    #completed_job = get_completed_job_info_from_server(options.GID)    # the download may have already been removed so GID is kind of useless
    completed_job = get_completed_job_info_from_filesystem(options.path)

    # if the download was larger than a certain threshold, do nothing
    totsize = float(completed_job['totalLength']) 
    if totsize > MAX_SIZE_THRESHOLD:
        logging.info("Downloaded size is %s (bigger than %s)... skipping rsynch." % (sizeof_fmt(totsize), sizeof_fmt(MAX_SIZE_THRESHOLD)))
        return
        
    if totsize == 0:
        logging.info("Cannot retrieve total size... rsynching anyway.")
    else:
        logging.info("Downloaded size is %s (smaller than %s)... rsynching." % (sizeof_fmt(totsize), sizeof_fmt(MAX_SIZE_THRESHOLD)))
    
    # ok, COPY the stuff   (TODO: REMOVE THE ORIGINAL TO FREE UP SPACE!!!)
    destfolder = get_dest_folder()
    if len(destfolder)==0:
        tmpstr = ", "
        logging.error("Cannot find folder " + DEST_FOLDER_NAME + " in one of the paths: " + tmpstr.join(DEST_PREFIX))
        return
        
    args = "'" + completed_job['fullpath_folder'] + "' '" + destfolder + "'"
    logging.info("Calling rsync with: %s" % (args))
    #main_rsync(args)
    #main_rsync("")
    #os.system("rsync -auvPz ")
    return_code = subprocess.call("rsync -aP --bwlimit 1000 " + args, shell=True)  
    logging.info("Subprocess rsync completed with return code: %d" % (return_code))
    
    destpath = os.path.join(destfolder, completed_job['folder'])
    destSize = get_size(destpath)
    logging.info("Destination folder '%s' has size %s" % (destpath, sizeof_fmt(destSize)))
    
    return
    
def get_dest_folder():
    for prefix in DEST_PREFIX:
        folder = os.path.join(prefix, DEST_FOLDER_NAME)
        if os.path.exists(folder):
           return folder
    
    return ""
    
    
def get_completed_job_info_from_server(gid):
    # connect to Aria2 and ask a few more things    
    url = os.getenv('ARIA2Q_URL', 'http://localhost:6800/rpc')
    server = xmlrpclib.ServerProxy(url)
    keys = ['gid', 'totalLength', 'completedLength', 'status', 'downloadSpeed', 'uploadSpeed', 'bittorrent']
    return server.aria2.tellStatus(secret, gid, keys)

def get_completed_job_info_from_filesystem(path):
    # create output type
    completed_job = {'totalLength': 0, 'folder': '', 'fullpath_folder':'' }
    
    if not path.startswith(DL_PREFIX):
        logging.error("Downloaded file '%s' has not been placed in default prefix '%s'?" % (path, DL_PREFIX))
        return completed_job
       
    folder = path[len(DL_PREFIX):]
    #pprint(folder.split(os.path.sep))
    folder = folder.split(os.path.sep)[0]
    
    completed_job['folder'] = folder
    completed_job['fullpath_folder'] = os.path.join(DL_PREFIX, folder)
    logging.info("The Aria2 job has been saved in '%s'" % (completed_job['fullpath_folder']))
    
    completed_job['totalLength'] = get_size(completed_job['fullpath_folder'])
    return completed_job

def get_size(start_path = '.'):
    total_size = 0
    for dirpath, dirnames, filenames in os.walk(start_path):
        for f in filenames:
            fp = os.path.join(dirpath, f)
            partial_size = os.path.getsize(fp)
            logging.info("...getting the size of '%s': %s" % (f, sizeof_fmt(partial_size)))
            total_size += partial_size
    return total_size

def sizeof_fmt(num, suffix='B'):
    for unit in ['','Ki','Mi','Gi','Ti','Pi','Ei','Zi']:
        if abs(num) < 1024.0:
            return "%3.1f%s%s" % (num, unit, suffix)
        num /= 1024.0
    return "%.1f%s%s" % (num, 'Yi', suffix)

def get_options():
    parser = argparse.ArgumentParser(description="Post-download hook script for Aria2")
    
    parser.add_argument("GID",
                        help="the GID of the completed download")
    parser.add_argument("nfiles", type=int,
                        help="the num of files of the completed download")
    parser.add_argument("path",
                        help="the path of the completed download")
    options = parser.parse_args()
    
    # example of the result:
    # options.GID=55933f47abdb64e3
    # options.nfiles=1
    # options.path=/media/extdiscTORRENTS/torrents/Wolfenstein.The New Order.v 1.0.0.1.(1\u0421-\u0421\u043e\u0444\u0442\u041a\u043b\u0430\u0431).(2014).Repack/video-3.bin
 
    return options

if __name__ == "__main__":
    main(get_options())

    
    
    
    
#
# UNUSED FUNCTIONS
#   

def list_jobs(server, job_list):
    if len(job_list) == 0:
        return

    logging.info("GID                 STATUS  PERCENT TOTSIZE DLSPEED  ULSPEED   NFILES  TORRENT NAME/FILENAME")

    for dl in job_list:
        logging.info(job_details(server, dl))

def job_details(server, job_status):
    #pprint(job_status)
        
    gid = job_status['gid']
    status = job_status['status']
    percent = percentage(job_status)
    totsize = sizeof_fmt(float(job_status['totalLength'])) 
    dlspeed = b2kb(job_status['downloadSpeed'])
    ulspeed = b2kb(job_status['uploadSpeed'])
    
    # these two make RPC calls:
    nfiles = getfilecount(server,gid)
    filename = getname(server,job_status,gid)
    
    return "%4s  %8s  %5.1f%%  %s % 4dkB/s % 4dkB/s  %d     %s" % (gid, status, percent, totsize, dlspeed, ulspeed, nfiles, filename)

def b2kb(sz):
    return int(sz)/1024
    
def getname(server, job_status, gid):
    try:
        filename = job_status['bittorrent']['info']['name']
    except:
        filename = os.path.basename(top_file_name(server, gid))
    
    return filename


def percentage(job_status):
    length = float(job_status['totalLength'])
    completed = float(job_status['completedLength'])
    if length > 0:
        return (completed * 100) / length
    return 0

def top_file_name(server, gid):
    return server.aria2.getFiles(secret,gid)[0]['path']

def getfilecount(server, gid):
    files = server.aria2.getFiles(secret,gid)
    #pprint(files)
    return len(files)


