Flask-BaiduStorage

百度云存储Flask扩展，BaiduStorage for Flask

安装

pip install Flask-BOS

配置

配置项  说明
BOS_ACCESS_KEY    BOS Access Key
BOS_SECRET_KEY    BOS Secret Key
BOS_BUCKET_NAME   BOS bucket名称


使用
from flask import Flask
from flask_bos import BOS

BOS_ACCESS_KEY = 'BOS Access Key'
BOS_SECRET_KEY = 'BOS Secret Key'
BOS_BUCKET_NAME = 'BOS bucket名称'
BOS_HOST = 'BOS对应域名'

def setUp(self):
    self.app = Flask(__name__)
    self.app.config.from_object(__name__)
    self.bos = BOS(self.app)

# 保存文件到BOS
def test_save(self):
    data = 'test_save'
    filename = 'test_save'
    ret = self.bos.save(data,filename)
    return ret

#获得object的链接 
def test_url(self):
    filename = 'testsave'
    timestamp = int(time.time())
    expiration_time = 3600
    url = self.bos.url(filename,timestamp,expiration_time)
    print url

#删除文件
def test_delete(self):
    filename = 'testsave'
    deli = self.bos.delete(filename)
    return deli

参考tests.py
测试

$ python tests.py
许可

The MIT License (MIT). 详情见 License文

