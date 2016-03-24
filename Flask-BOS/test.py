#!/usr/bin/env python
# -*- coding: utf-8 -*-

'''Tests for Flask-BOS'''

import unittest
import urllib
import time
from flask import Flask
from flask_bos import BOS

BOS_ACCESS_KEY = 'fm9Cf5SM5UIeX2egIeGMWwGx'
BOS_SECRET_KEY = 'Q8fGdGR64tXD0Y094jBAHTF63tlvHHgI'
BOS_BUCKET_NAME = 'flask-zxd'
BOS_HOST = 'http://bj.bcebos.com'

class FlaskBosTestCase(unittest.TestCase):
    def setUp(self):
       self.app = Flask(__name__)
       self.app.config.from_object(__name__)
       self.bos = BOS(self.app)
#     
#    def test_save(self):
#       data = 'test_save'
#       filename = 'test_save'
#       ret = self.bos.save(data,filename)
#       return ret
    def test_url(self):
	   filename = 'testsave'
	   timestamp = int(time.time())
	   expiration_time = 3600
	   url = self.bos.url(filename,timestamp,expiration_time)
	   print url

    def test_delete(self):
       filename = 'testsave'
       deli = self.bos.delete(filename)
       return deli
if __name__ == '__main__':
    unittest.main()
