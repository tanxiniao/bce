#!/bin/env python

import sys

#settings for non-asicii chars
reload(sys)
sys.setdefaultencoding("utf-8")

import logging
from multiprocessing.dummy import Pool

from baidubce import protocol
from baidubce.auth import bce_credentials
from baidubce.services.bos import bos_client
from baidubce.retry_policy import NoRetryPolicy
from baidubce.bce_client_configuration import BceClientConfiguration

ak = "abd1ee9a77f642fc8203c8c2adc72e50"
sk = "dbd8ea0cf2a64d238ff8d48d97ca68da"
host = "http://bj.bcebos.com"

config = BceClientConfiguration(
    bce_credentials.BceCredentials(ak, sk),
    host,
    protocol.HTTP,
    retry_policy=NoRetryPolicy())

def print_usage():
    print "Usage: 'python %s $BUCKETNAME $DIRNAME' to clear a directory in bucket" % sys.argv[0]
    print "       'python %s $BUCKETNAME' to clear a bucket" % sys.argv[0]

logger = None
def set_logger():
    global logger
    logger = logging.getLogger(None)
    sh = logging.StreamHandler(sys.stdout)
    sh.setLevel(logging.DEBUG)
    formatter = logging.Formatter(
        fmt="%(levelname)s: %(asctime)s: %(filename)s:%(lineno)d * %(thread)d %(message)s",
        datefmt="%m-%d %H:%M:%S")
    sh.setFormatter(formatter)
    logger.addHandler(sh)
    logger.setLevel(logging.INFO)

def main():
    bucket_name = None
    prefix = None

    if len(sys.argv) == 2:
        bucket_name = sys.argv[1]
    elif len(sys.argv) == 3:
        bucket_name = sys.argv[1]
        prefix = sys.argv[2]
    else:
        print_usage()
        return -1

    set_logger()
    client = bos_client.BosClient(config)

    # inner function for multi-threading
    def __delete_object(object_name):
        logger.info("Deleting %s" % (object_name.key.encode("utf-8"),))
        try:
            client.delete_object(bucket_name, object_name.key)
        except Exception as e:
            logger.warning("Failed to delete %s, reason:%s" % (object_name.key.encode("utf-8"), e))

    logger.info( "Start to clear bos:/%s/%s*" % (bucket_name, prefix))

    #run multi-threading
    all_objects = client.list_all_objects(bucket_name, prefix=prefix)
    pool = Pool(20)
    pool.imap_unordered(__delete_object, all_objects)
    pool.close()
    pool.join()
    logger.info("Clear bos:/%s/%s* done" % (bucket_name, prefix))

if __name__ == "__main__":
    sys.exit(main())
