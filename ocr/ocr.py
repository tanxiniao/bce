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

utctime = datetime.datetime.utcfromtimestamp(timestamp)

utctime1 = "%04d-%02d-%02dT%02d:%02d:%02dZ" % (
        utctime.year, utctime.month, utctime.day,
        utctime.hour, utctime.minute, utctime.second)


credentials = testbossign.BceCredentials("b38854e155504f2d8eff707f18a4b4d4", "cfafe82fe16c4a56bf40d990f706b6bf")
headers = {
            "host": "ocr.bj.baidubce.com",
            "content-length":52,
            "content-type":"application/json"
            #"x-bce-date": utctime1

    
}

params = {
              #"partNumber": 9,
              #"uploadId": "VXBsb2FkIElpZS5tMnRzIHVwbG9hZA"
         }

data = '{"bosPath":"wenge11/baidu.png","language":"CHN_ENG"}'

path = "/v1/recognize/text"
method = 'POST'
result = testbossign.sign(credentials,method,path,headers, params, timestamp)
httpClient = None
host = 'ocr.bj.baidubce.com'
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

