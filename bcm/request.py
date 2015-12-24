# -*-coding:UTF-8 -*-
import os
import sys
import time
import socket
import random
import urllib
import httplib
import json
import logging
import testbossign
import datetime
from logging.handlers import RotatingFileHandler

timestamp = int(time.time())
print timestamp
utctime = datetime.datetime.utcfromtimestamp(timestamp)
print utctime
utctime1 = "%04d-%02d-%02dT%02d:%02d:%02dZ" % (
        utctime.year, utctime.month, utctime.day,
        utctime.hour, utctime.minute, utctime.second)
print utctime1

credentials = testbossign.BceCredentials("b38854e155504f2d8eff707f18a4b4d4", "cfafe82fe16c4a56bf40d990f706b6bf")
headers = {
            "host": "bcm.bj.baidubce.com",
	        "content-length":594,
            "content-type":"application/json"
            #"x-bce-date": utctime1    
}
params = {
              #"partNumber": 9,
              #"uploadId": "VXBsb2FkIElpZS5tMnRzIHVwbG9hZA"
         }
data = '{"metricData":[{"metricName":"MemUsedBytes","dimensions":[{"name":"InstanceId","value":"myInstanceId"},{"name":"InstanceType","value":"t.micro"},{"name":"ImageId","value":"centos"},{"name":"GroupName","value":"myService"}],"value":1234567,"timestamp":"2015-12-22T08:30:00Z"},{"metricName":"CPUUsagePercent","dimensions":[{"name":"InstanceId","value":"myInstanceId"},{"name":"InstanceType","value":"t.micro"},{"name":"ImageId","value":"centos"},{"name":"GroupName","value":"myService"}],"statisticValues":{"sum":90,"maximum":80,"minimum":10,"sampleCount":2},"timestamp":"2015-12-22T08:30:00Z"}]}'
jdata = json.dumps(data)
print jdata
#ddata = urllib.urlencode(data)
#print ddata
path = "/json-api/v1/metricdata/7979ae18a80a42c8b133012eb8d31986/testzxd"
method = 'POST'
result = testbossign.sign(credentials,method,path,headers, params, timestamp)
httpClient = None
host = 'bcm.bj.baidubce.com'
#headers = {"Content-Type": "application/json"}
headers['Authorization'] = result
print headers
print result
port = 80
try:
    httpClient = httplib.HTTPConnection(host,port)
    httpClient.request(method=method,url=path,body=data,headers=headers)
    response = httpClient.getresponse()
    print response.status
    print response.reason
    print response.read()
except Exception,e:
    print e
finally:
    if httpClient:
        httpClient.close()


