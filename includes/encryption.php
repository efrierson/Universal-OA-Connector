<?php
class MyEncryption
{

    public $pubkey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLa7lVQ9kYoqrrqPIUv2dhDvyg
hraW4lgquGOLM59+G03F65uSXtom+lOVt/Wam2ROtrdW/JOpIIk7KUuk+byBBO1a
e0YZof7Q5YHIRGvMbLC2Z+fbTd/a0fp4SY3HZH5GDv8dcxJR8ZhSMBhy0x+VaLdO
M68I/cdG7IQrXDXXYQIDAQAB
-----END PUBLIC KEY-----';
    public $privkey = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDLa7lVQ9kYoqrrqPIUv2dhDvyghraW4lgquGOLM59+G03F65uS
Xtom+lOVt/Wam2ROtrdW/JOpIIk7KUuk+byBBO1ae0YZof7Q5YHIRGvMbLC2Z+fb
Td/a0fp4SY3HZH5GDv8dcxJR8ZhSMBhy0x+VaLdOM68I/cdG7IQrXDXXYQIDAQAB
AoGAMMGVHlawxjLW/Lz1qPtnb+ADtQYU5X1C3JptYYPyCmvI7FNYanDJoOYG+q+o
8nGkTSmGMBdB3RurSL7RHq2s/GGvDm5Z//mY06ewhIEj24nuN6O44KWug35WK1bX
83AeWc4Ncu8Kwf83Ok9RsLOKqzozkvCqrzIPiv3h3N587TECQQD6jUmzhXa2bAgZ
mPETDAwOEtoxncvUThzBrooHvOpQVTwYudGM4FJtBVkst40i3i0MuKeCGJbCYt3O
Wr7HFzaDAkEAz9gT854+VswhguTtEdD5k7e3Bbbr1u9FQNX5phbXFIW1FBMVlpIz
gdDxeZFEhPrTxx73dbeQmSfVDHtB8VV1SwJBAMaIEfBYPvrJm5l84PlwwFSeh5pt
KMfvpUWrYeBDx38kKtyE0RDJ50ZPyJtwTjtkxVmhL8ocZcldwdfze9wR/rUCQHHs
TDNSX3UP+qZWeKM1WjdfkZAuTWLIT7tUDby99DIpf7F7LHAVvum+7zzlJRuGqKIS
FS2O6lEohhyLSv/PCbUCQHWoaM6cYgYVGIugtI/bQTPLFnK0JXZOW6KINj8IQil4
ktYD1mqvPPOoThl5q08dsUDdM9qWy4wFttJRVxlc7qw=
-----END RSA PRIVATE KEY-----';

    public function encrypt($data)
    {
        if (openssl_public_encrypt($data, $encrypted, $this->pubkey))
            $data = base64_encode($encrypted);
        else
            throw new Exception('Unable to encrypt data. Perhaps it is bigger than the key size?');

        return $data;
    }

    public function decrypt($data)
    {
        if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->privkey))
            $data = $decrypted;
        else
            $data = '';

        return $data;
    }
}
?>