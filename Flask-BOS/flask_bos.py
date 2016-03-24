# -*- coding: utf-8 -*-

import logging
from baidubce import exception
from baidubce.services.bos import canned_acl
from baidubce.auth.bce_credentials import BceCredentials
from baidubce.bce_client_configuration import BceClientConfiguration
from baidubce.services.bos.bos_client import BosClient

class BOS(object):
    def __init__(self, app=None):
        self.app = app
        if app is not None:
           self.init_app(app)

    def init_app(self, app):
        self._host = app.config.get('BOS_HOST', '')
        self.access_key = app.config.get('BOS_ACCESS_KEY', '')
        self.secret_key = app.config.get('BOS_SECRET_KEY', '')
        self._bucket_name = app.config.get('BOS_BUCKET_NAME', '')
        self.bos = self.createBosClient()
    
    def createBosClient(self):
        auth = BceCredentials(self.access_key, self.secret_key)
        config = BceClientConfiguration(auth, self._host)
        return BosClient(config)
 
    def save(self, data,filename=None):
        ret =  self.bos.put_object_from_string(self._bucket_name,data,filename) 
        return ret

    def delete(self,filename):
        return self.bos.delete_object(self._bucket_name,filename)
    
    def url(self,filename,timestamp,expiration_time):
        return self.bos.generate_pre_signed_url(self._bucket_name,filename,timestamp,expiration_time)

    def list(self):
	print self._bucket_name
        ret = self.bos.list_objects(self._bucket_name)
        for obj in ret.contents:
            __logger.debug("[Sample] list objects key:%s", obj.key) 

