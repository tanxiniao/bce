import hashlib
import hmac
import string
import datetime


AUTHORIZATION = "authorization"
BCE_PREFIX = "x-bce-"
DEFAULT_ENCODING = 'UTF-8'


class BceCredentials(object):
    def __init__(self, access_key_id, secret_access_key):
        self.access_key_id = access_key_id
        self.secret_access_key = secret_access_key


RESERVED_CHAR_SET = set(string.ascii_letters + string.digits + '.~-_')
def get_normalized_char(i):
    char = chr(i)
    if char in RESERVED_CHAR_SET:
        return char
    else:
        return '%%%02X' % i
NORMALIZED_CHAR_LIST = [get_normalized_char(i) for i in range(256)]


def normalize_string(in_str, encoding_slash=True):
    if in_str is None:
        return ''

    in_str = in_str.encode(DEFAULT_ENCODING) if isinstance(in_str, unicode) else str(in_str)

    if encoding_slash:
        encode_f = lambda c: NORMALIZED_CHAR_LIST[ord(c)]
    else:
        encode_f = lambda c: NORMALIZED_CHAR_LIST[ord(c)] if c != '/' else c

    return ''.join([encode_f(ch) for ch in in_str])


def get_canonical_time(timestamp=0):
    if timestamp == 0:
        utctime = datetime.datetime.utcnow()
    else:
        utctime = datetime.datetime.utcfromtimestamp(timestamp)

    return "%04d-%02d-%02dT%02d:%02d:%02dZ" % (
        utctime.year, utctime.month, utctime.day,
        utctime.hour, utctime.minute, utctime.second)


def get_canonical_uri(path):
    return normalize_string(path, False)


def get_canonical_querystring(params):
    if params is None:
        return ''

    result = ['%s=%s' % (k, normalize_string(v)) for k, v in params.items() if k.lower != AUTHORIZATION]

    result.sort()

    return '&'.join(result)


def get_canonical_headers(headers, headers_to_sign=None):
    headers = headers or {}

    if headers_to_sign is None or len(headers_to_sign) == 0:
        headers_to_sign = {"host", "content-md5", "content-length", "content-type"}

    f = lambda (key, value): (key.strip().lower(), str(value).strip())

    result = []
    for k, v in map(f, headers.iteritems()):
        if k.startswith(BCE_PREFIX) or k in headers_to_sign:
            result.append("%s:%s" % (normalize_string(k), normalize_string(v)))

    result.sort()

    return '\n'.join(result)


def sign(credentials, http_method, path, headers, params,
         timestamp=0, expiration_in_seconds=1800, headers_to_sign=None):
    headers = headers or {}
    params = params or {}

    sign_key_info = 'bce-auth-v1/%s/%s/%d' % (
        credentials.access_key_id,
        get_canonical_time(timestamp),
        expiration_in_seconds)
    sign_key = hmac.new(
        credentials.secret_access_key,
        sign_key_info,
        hashlib.sha256).hexdigest()
    print sign_key

    canonical_uri = get_canonical_uri(path)

    canonical_querystring = get_canonical_querystring(params)

    canonical_headers = get_canonical_headers(headers, headers_to_sign)

    string_to_sign = '\n'.join(
        [http_method, canonical_uri, canonical_querystring, canonical_headers])

    sign_result = hmac.new(sign_key, string_to_sign, hashlib.sha256).hexdigest()

    if headers_to_sign:
        result = '%s/%s/%s' % (sign_key_info, ';'.join(headers_to_sign), sign_result)
    else:
        result = '%s//%s' % (sign_key_info, sign_result)

    return result

if __name__ == "__main__":
    credentials = BceCredentials("b38854e155504f2d8eff707f18a4b4d4","cfafe82fe16c4a56bf40d990f706b6bf")
    http_method = "GET"
    path = "/v1/zxdtestbae/apitest"
    headers = {"host": "bj.bcebos.com"
               #"content-length": 594,
               #"content-md5": "0a52730597fb4ffa01fc117d9e71e3a9",
               #"content-type":"application/json"
               #"x-bce-date": "2015-12-22T08:59:00Z"
	      }
    params = {
              #"partNumber": 9,
              #"uploadId": "VXBsb2FkIElpZS5tMnRzIHVwbG9hZA"
	     }
    
    timestamp = 1450852800 
    
    result = sign(credentials, http_method, path, headers, params, timestamp)
    print result
