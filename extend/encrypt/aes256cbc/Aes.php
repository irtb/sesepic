<?php
namespace encrypt\aes256cbc;

class Aes
{
    /**
     * Strongest AES encryption method available at this time
     */
    const METHOD = 'aes-256-cbc';

    /**
     * @var the key used to encrypt/decrypt the data with. The first 32 bytes are used.
     */
    private $key;

    /**
     * @var the initialisation vector used encrypting an decrypting the data. You never want to use the same key and iv for different encryptions. The first 16 bytes are used
     */
    private $iv;


    /**
     * AesEncryption constructor.
     *
     * @param $key
     * @param $iv
     */
    public function __construct($key, $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    /**
     * Encrypts the data passed in
     *
     * @param string $data the data you want to encrypt
     *
     * @return string
     */
    public function encrypt($data)
    {
        return openssl_encrypt(
            $data,
            self::METHOD,
            $this->getBytesFromString($this->key, 32),
            OPENSSL_RAW_DATA,
            $this->getBytesFromString($this->iv, 16)
        );
    }

    /**
     * Decrypts the data passed in
     *
     * @param $data
     * @return string
     */
    public function decrypt($data)
    {
        $openssl_output = openssl_decrypt(
            $data,
            self::METHOD,
            $this->getBytesFromString($this->key, 32),
            OPENSSL_RAW_DATA,
            $this->getBytesFromString($this->iv, 16)
        );

        if ($openssl_output !== false) {
            return $openssl_output;
        }

        return openssl_error_string();
    }

    /**
     * Return a piece of a string based on the number of bytes
     *
     * @param $string
     * @param $size
     *
     * @return mixed
     */
    private function getBytesFromString($string, $size) {
        return mb_substr($string, 0, $size);
    }
}
