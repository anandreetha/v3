<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 19/10/2017
 * Time: 09:25
 */

namespace Multiple\Core\Utils;

use Phalcon\Mvc\User\Component;

/**
 * NB: Phalcon/PHP strips out the URL encoding before it 'comes' to our code, hence
 */
class SecurityUtils extends Component
{
    /**
     * @param $data - what data you would like encrypted
     * @return string - hashed data
     */
    public function encrypt($data)
    {
        $bniCmsEncryptionKey = $this->config->general->bniCmsEncryptionKey;
        return openssl_encrypt($data, "AES-128-ECB", $bniCmsEncryptionKey);
    }

    /**
     * @param $encrypted - a hash that has not been url encoded
     * @return string - original data
     */
    public function decrypt($encrypted)
    {
        $bniCmsEncryptionKey = $this->config->general->bniCmsEncryptionKey;
        return openssl_decrypt($encrypted, "AES-128-ECB", $bniCmsEncryptionKey);
    }

    /**
     * @param $data - what data you would like encrypted
     * @return string - url safe hashed data
     */
    public function encryptUrlEncoded($data)
    {
        return urlencode($this->encrypt($data));
    }
}
