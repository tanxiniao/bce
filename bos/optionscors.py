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


credentials = testbossign.BceCredentials("fm9Cf5SM5UIeX2egIeGMWwGx", "Q8fGdGR64tXD0Y094jBAHTF63tlvHHgI")
headers = {
            "host": "xdtest.bj.bcebos.com",
            "Origin": "*",
            "Access-Control-Request-Method": "GET",
            "Access-Control-Allow-Headers": "*"

            # "content-length":40,
            # "content-type":"application/json",
            # "x-bce-date": utctime1


}
params = {
              #"partNumber": 9,
              #"uploadId": "VXBsb2FkIElpZS5tMnRzIHVwbG9hZA"
         }

#data = '{"name":"tom","receiver":["0000"],"contentVar":"{\"code\":\"123456\"}"}'
data = ''
path = "/a.txt"
method = 'OPTIONS'
result = testbossign.sign(credentials,method,path,headers, params, timestamp)
httpClient = None
host = 'xdtest.bj.bcebos.com'
# headers['Authorization'] = result
#print headers
#print result
port = 80
try:
    httpClient = httplib.HTTPConnection(host,port)
    httpClient.request(method=method,url=path,headers=headers)
    response = httpClient.getresponse()
    print response.status
    print response.reason
    print response.read()
    print response.getheaders()
except Exception,e:
    print e
finally:
    if httpClient:
        httpClient.close()

