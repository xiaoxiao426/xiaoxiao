#-*- coding: UTF-8 -*-
import socket
import threading
from time import sleep
import struct
import os
import pymongo
import random
import time
import logging
import json
from collections import OrderedDict
import datetime
import types

conn = pymongo.Connection('127.0.0.1',27017)
conn.admin.authenticate("root","chairs246531")
db = conn.CHAIRS
collection = db.y2017_evidence

logging.basicConfig(level=logging.DEBUG,
                format='%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s',
                datefmt='%a, %d %b %Y %H:%M:%S',
                filename='insertmongo.log',
                filemode='w')

MAX_NUM = 60000	
#MAX_NUM = 2			
def is_auth(ThreatType):
	auth = 1 #默认是真实的
	lis = list(str(ThreatType))	
	binar = bin(int(lis[3],16))	
	li = list(str(binar).zfill(6)) 
	if (lis[2]=='0') or (lis[2]=='1') or (lis[2]=='6') or (lis[2]=='7'): 
		if (li[5] == '1'): 
			auth = 0	 
	 
	return auth  
	
#要插入固定格式的文件 
def mongo_insert(dir): 
	#while 1:
	#flog = open("insertmongo.log","w")
	#flog.truncate()
	serverID = 0 
	siteID = 0	
	
	#Analyzerlist = {"189":"26"}  #个位数的前面补0存储,写好38个节点的，仔细！
	Analyzerlist  = {"2":"01","6":"02","10":"03","14":"04","18":"05","22":"06","26":"07","30":"08","34":"09","38":	"10","42":"11","46":"12","50":"13","54":"14","58":"15","62":"16","66":"17","70":"18","78":"19","74":"20","82":"21","86":"22","90":"23","94":"24","98":"25","189":"26","106":"27","110":"28","114":"29","118":"30","122":"31","126":"32","130":"33","134":"34","138":"35","142":"36","146":"37","150":"38"}
	#for dir in Analyzerlist:
	#print dir
	serverID = Analyzerlist.get(dir)
		#print "-------"+dir+"-------"
	#print serverID
	
#	try:			       												
	lists = os.listdir('./'+dir)  #获取dir文件夹下所有的文件 
	orderfilename = []		
	while len(lists) < 2:
		#print threading.currentThread().getName() + " sleeping in dir " + dir
		break
		#time.sleep(600)  #等待5分钟再取数据 38节点同时运行时 是不是要调整  如果只sleep这一个线程 其他不变 就不影响		
		#lists = os.listdir('./'+dir)		
						
	for filename in lists: 		
		if len(filename) == 17:#去掉时间记录文件
			lastmodifytime=os.stat('./'+dir+'/'+filename).st_mtime
			
			if time.time()-lastmodifytime > 60:#判断写入时间与现在相隔大于60s再读取
				filename = './'+dir+'/'+filename
				orderfilename.append(filename)				
	#except Exception,e:
		#	logging.debug(e)
#	try:		
	for filename in orderfilename:	#orderfilename 对文件名进行排序，依次处理	
		#print "filename========="
		#print filename
		f = open(filename,'rb') 
		Context = f.read()			
		ct = Context.strip().split('\n\n')	
		if(len(ct)==1):
			if filename:
				os.remove(filename)
			continue
		NBOSDDOS_ID = ''
		ThreatDstIP = ''
		DstLocation = ''
		Reporter = ''
		ThreatType = 0	
		SubType = 0		
		avg_pps = 0
		max_pps = 0
		avg_kbps = 0 
		max_kbps = 0
		StartTime = 0
		EndTime = 0
		ThreatSrcNum = 0
		lis0 = ''
		subtypelist={}		
		
		for lines in ct:
			overflag = 0  #是否超过存储最大数量的标志
			linecount = 0
			inner_arr = []		
			ins_data = OrderedDict()
			linecount2 = lines.strip().split('\n')  #计算参与攻击的IP				
			for linecount3 in linecount2:
				if len(linecount3) > 2:
					linecount = linecount + 1
					
			#storageNum = min(linecount,MAX_NUM)
			if linecount > MAX_NUM:    #set the max num of storing,the toplimit = MAX_NUM
				storageNum = MAX_NUM
				stored = 0
				overflag = 1
				
			for line in lines.split('\n'):
				if overflag == 1:
					stored = stored + 1
					if stored > MAX_NUM:
						break
				
				line = line.split()
				#isauth = 1
				#if len(line) == 10 and (line[3]=="0x2f" or line[3]=="0x3f"):
				#	break
				if len(line) == 10:
					#isauth = 1
					#if is_auth(line[3]) == 0: #再测试？判断是否伪造，伪造的不存 
					#	isauth = 0
					#	break
					NBOSDDOS_ID = serverID+line[0]
					#print "=======||||"
					#print NBOSDDOS_ID
					results = db.y2017_evidence.find({"NBOSDDOS_ID":NBOSDDOS_ID})
					if not results or results.count()==0:							
						pass
					else:							
						db.y2017_evidence.remove({"NBOSDDOS_ID":NBOSDDOS_ID})  #remove duplicate data	
					#NBOSDDOS_ID = NBOSDDOS_ID	
					ThreatDstIP = line[1]
					DstLocation = line[2]
					ThreatType = line[3]
					#print ThreatType
					lis = list(str(ThreatType))[2].strip()
					lis0 = list(str(ThreatType))[0].strip()
					if(lis0 == '0'):					
						if(lis != 'F'):
							subtypelist = {"0":1001,"1":1001,"2":1023,"3":1023,"4":1045,"5":1045,"6":1067,"7":1067,"":0000}
							SubType = int(subtypelist.get(lis))
						else:
							subtypelist = {"0xF0":1001,"0xF1":1023,"0xF2":1034,"0xF3":1045,"0xFc":1045,"0xFd":1034,"0xFe":1023,"0xFf":1001,"":0000}
							SubType = int(subtypelist.get(ThreatType))
						#print SubType
					else:
						break
					Reporter = 'NBOS-'+str(serverID).zfill(2)+'-01'
					avg_pps = int(line[4])
					max_pps = int(line[5])
					avg_kbps = int(line[6]) 
					max_kbps = int(line[7])
					
					StartTime = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(int(line[8])))
					EndTime = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(int(line[9])))
					ThreatSrcNum = int(linecount-1)	

				#elif len(line) > 3 and len(line)<10:   # and isauth == 1:
				elif len(line) ==8: 
					
					inner_arr_i = OrderedDict()				
					inner_arr_i['ThreatSrcIP'] = line[0]
					inner_arr_i['SrcLocation'] = line[1]
					inner_arr_i['TotalOutPkts'] = int(line[2])
					inner_arr_i['TotalInPkts'] = int(line[3])
					inner_arr_i['TotalOutBytes'] = int(line[4])
					inner_arr_i['TotalInBytes'] = int(line[5])
					inner_arr_i['ThreatSrcPort'] = int(line[6])
					inner_arr_i['ThreatDstPort'] = int(line[7])
				
					inner_arr.append(inner_arr_i) 				
				elif len(line) > 10:
					len2 = 0
					sline=''
					len2 = len(line)
					inner_arr_i = OrderedDict()				
					inner_arr_i['ThreatSrcIP'] = line[0]
					for i in range(1,len2-6):
							sline = sline+" "+line[i]
					inner_arr_i['SrcLocation'] = sline
					inner_arr_i['TotalOutPkts'] = int(line[len2-6])
					inner_arr_i['TotalInPkts'] = int(line[len2-5])
					inner_arr_i['TotalOutBytes'] = int(line[len2-4])
					inner_arr_i['TotalInBytes'] = int(line[len2-3])
					inner_arr_i['ThreatSrcPort'] = int(line[len2-2])
					inner_arr_i['ThreatDstPort'] = int(line[len2-1])
					
					inner_arr.append(inner_arr_i) 	
					
				#print ins_data	
				ins_data['NBOSDDOS_ID'] = NBOSDDOS_ID			
				ins_data['ThreatDstIP'] = ThreatDstIP			
				ins_data['DstLocation'] = DstLocation			
				ins_data['Type'] = '10' 
				ins_data['ThreatType'] = ThreatType
				ins_data['SubType'] = SubType
				ins_data['Reporter'] = Reporter
				ins_data['avg_pps'] = int(avg_pps)
				ins_data['max_pps'] = int(max_pps)			
				ins_data['avg_kbps'] = int(avg_kbps)
				ins_data['max_kbps'] = int(max_kbps)
				ins_data['StartTime'] = StartTime
				ins_data['EndTime'] = EndTime
				ins_data['ThreatIPNum'] = int(ThreatSrcNum)	
				ins_data['ThreatSrc'] = inner_arr							
			
			#if isauth==1:	
			db.y2017_evidence.insert(ins_data)
			#print "insert successfully..."
		if filename:
			os.remove(filename)   #慎重执行
		f.close()	
	#except Exception,e:
		#	pass 
def main():
	#建立数据库文件，访问 修改数据库
	#dos_20160719_0000   dos_20160506_96
	#mongo_insert('./111/dos_20160719_0006','8')	
	
	#dirlist = ['2','14','18','22','26','138','142','150','146','189','110','114','106','118','122','130','134','126','90','98','94','70', '86','78','74','82','50','54','58','62','66','30','34','38','42','46','6','10']
	#try:
	while 1:
		#dirlist = ['189']		
		dirlist = ['2','14','18','22','26','138','142','150','146','189','110','114','106','118','122','130','134','126','90','98','94','70', '86','78','74','82','50','54','58','62','66','30','34','38','42','46','6','10']
		for dir in dirlist:
			mongo_insert(dir)
			#t = threading.Thread(target=mongo_insert, args=(dir,))				
			#t.setDaemon(True) #待定
			#t.start()
			#t.join()
			#t.stop()
		#print "start sleep..."
		#print threading.currentThread().getName()
		sleep(300)
#	except Exception,e:
	 #   pass  
		
if __name__ == '__main__':
    main()
	
	
	
	
	
	
	
	
