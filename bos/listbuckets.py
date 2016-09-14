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


credentials = testbossign.BceCredentials("AK", "SK")
headers = {
            "host": "bj.bcebos.com",
            # "content-length":40,
            # "content-type":"application/json",
            "x-bce-date": utctime1
            #"x-bce-security-token":"MjUzZjQzNTY4OTE0NDRkNjg3N2E4YzJhZTc4YmU5ZDh8AAAAADgBAADeRoI++eCI26GjuWVqClgnWy+iMIYtD7JvyPPh67/rK9ziC/skgysC/ZelL1+LU7aIXV1tApy4dTWfMHyeJ4ZB9UNnxUrxVg97T4M8WEpfDS11troeshbHZGyCF22Vxn2TvN2QcW93YF28GqvXSbXgquty/0jmhMVSLAH8ASE2cGRHjaidr1N237ecId9x+0sh8Z0zr37Ibgg3y1f06UylS3hvQt+rr+og2WVXVH3Sclj37dAxI7xJkEFr9Fsmg48="


}
params = {
              #"partNumber": 9,
              #"uploadId": "VXBsb2FkIElpZS5tMnRzIHVwbG9hZA"
         }

#data = '{"name":"tom","receiver":["0000"],"contentVar":"{\"code\":\"123456\"}"}'
data = ''
path = "/"
method = 'GET'
result = testbossign.sign(credentials,method,path,headers, params, timestamp)
httpClient = None
host = 'bj.bcebos.com'
headers['Authorization'] = result
print headers
print result
port = 80
try:
    httpClient = httplib.HTTPConnection(host,port)
    httpClient.request(method=method,url=path,headers=headers)
    response = httpClient.getresponse()
    print response.status
    print response.reason
    print response.read()
except Exception,e:
    print e
finally:
    if httpClient:
        httpClient.close()

