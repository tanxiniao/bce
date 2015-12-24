package testsign;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.nio.charset.Charset;
import java.util.Date;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

import org.apache.commons.codec.binary.Hex;

import com.baidubce.BceClientException;
import com.baidubce.util.DateUtils;



public class testsign {
	private static final String DEFAULT_ENCODING = "UTF-8";
	private static final Charset UTF8 = Charset.forName(DEFAULT_ENCODING);
    public static void main(String[] args) throws UnsupportedEncodingException {
	
    	// System.out.println(DateUtils.formatAlternateIso8601Date(new Date()));
		String accessKey = "";
		String secretKey = "";
		//String timestamp = datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%SZ")
		String host = "bj.bcebos.com";
		String path = "/v1/zxdtestbae/index.html";
		//String host = "sms.bj.baidubce.com";
		//String path = "/v1/message";
		//String timestamp = DateUtils.formatAlternateIso8601Date(new Date());
		
		String timestamp = "2015-08-28T05:49:59Z";
		int expiresInSeconds = 3600;

		String CanonicalURI = URLEncoder.encode(path, "UTF-8").replace("%2F",
				"/");
       System.out.println("CanonicalURI: "+CanonicalURI);
		
		String canonicalQueryString = "";
		//如果请求是get，只加host
		//String canonicalHeaders = "host:bj.bcebos.com\nx-bce-date:"+URLEncoder.encode(timestamp,"UTF-8");
		String canonicalHeaders = "host:bj.bcebos.com\nx-bce-date:"+URLEncoder.encode(timestamp,"UTF-8");
		System.out.print(canonicalHeaders);
		String CanonicalRequest = joinN("\n", new String[] { "PUT",
				CanonicalURI, canonicalQueryString, canonicalHeaders });
		System.out.println("CanonicalRequest: "+CanonicalRequest);

		String signKeyInfo = "bce-auth-v1" + "/" + accessKey + "/" + timestamp
				+ "/" + expiresInSeconds;

		System.out.println("signKeyInfo: "+signKeyInfo);

		String signingKey = sha256Hex(secretKey, signKeyInfo);
		System.out.println("signingKey: "+signingKey);

		String signature = sha256Hex(signingKey, CanonicalRequest);

		String authorization = joinN("/", new String[] { "bce-auth-v1",accessKey, timestamp, String.valueOf(expiresInSeconds), "host;x-bce-date",signature });

		System.out.println("authorization: "+authorization);
		System.out.println("http://"+host+CanonicalURI+"?"+"authorization:"+authorization);

	}

	private static String joinN(String split, String[] args) {
		String ret = args[0];
		for (int i = 1; i < args.length; i++)
			ret = ret + split + args[i];
		return ret;
	}

	public static String sha256Hex(String signingKey, String stringToSign) {
		try {
			Mac mac = Mac.getInstance("HmacSHA256");
			mac.init(new SecretKeySpec(signingKey.getBytes(UTF8), "HmacSHA256"));
			return new String(Hex.encodeHex(mac.doFinal(stringToSign
					.getBytes(UTF8))));
		} catch (Exception e) {
			throw new BceClientException("Fail to generate the signature", e);
		}
	}
	

}