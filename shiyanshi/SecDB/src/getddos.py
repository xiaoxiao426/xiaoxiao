#-*- coding: UTF-8 -*-
import socket
import threading
import struct
import os
import random
import time
import logging
import signal
import string

logging.basicConfig(level=logging.DEBUG,
                format='%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s',
                datefmt='%a, %d %b %Y %H:%M:%S',
                filename='nbos_chairs.log',
                filemode='w')
				
BUF_SIZE = 1024
IS_ALIVE = True

def getfilename(fpth):
	last_line = ''
	last_lines = ''
	fname = ''
	ftime = open(fpth+'lastTime','ab+')
	
	if not ftime:
		pass
	
	last_lines = ftime.read(17)
	if not last_lines:
		fname = "dos_"+time.strftime("%Y%m%d",time.localtime())+"_0000"
	else:		
		last_line = last_lines.split("_")	
		
		if last_line[1]==time.strftime("%Y%m%d",time.localtime()):
			num = int(last_line[2])+1
			fname = last_line[0] + "_" + last_line[1] + "_" + str(num).zfill(4)
		if last_line[1]!=time.strftime("%Y%m%d",time.localtime()):
			fname = last_line[0]+"_"+time.strftime("%Y%m%d",time.localtime())+"_0000"
	ftime.close()
	ftime = open(fpth+'lastTime','wb')
	ftime.write(fname)
	ftime.close()
	return fname
	
def response(sock, addr):
    try:	
	ss = str(addr).split(',')
	s = ss[0].split('.')
	dirname = s[3].strip("'")
	if not os.path.exists(dirname):
	    os.mkdir(dirname)	
	fpth = "./"+dirname + "/"
	finame = getfilename(fpth)
	fp = open(fpth+finame,'wb+')
	
    except Exception,e:
	logging.debug(e)  
    while 1:
        try:	   
	   filedata = sock.recv(BUF_SIZE)	  	
	   if not filedata:
		break
	   fp.write(filedata)
	
	except Exception,e:
		logging.debug(e)  
    fp.close()
    sock.close()

def main():
    try:
	    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
	    s.bind(('0.0.0.0', 34567))
	    s.listen(50)
	    while IS_ALIVE:
	       try:	   
		   sock, addr = s.accept()		 
		   t = threading.Thread(target=response, args=(sock,addr))		   	           
		   t.start()
	       except Exception,e:
	           logging.debug(e)         
       	     
    except Exception,e:
	    logging.debug(e)
if __name__ == '__main__':
    main()
        
