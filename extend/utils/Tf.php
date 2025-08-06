<?php
namespace utils;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use think\exception\ValidateException;
use think\exception\HttpException;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\UnencryptedToken;


class Tf
{

    private static $instance = null;

    private $token;
    private $decodeToken;
    private $iss;  //发送数据端
    private $aud;	  //数据接收
    private $uid; //用户UID
    private $secrect;
    private $expTime;


    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct(){

    }

    public function getToken(){
        return $this->token->toString();
    }

    public function setToken($token){
        $this->token = $token;
        return $this;
    }

    public function setUid($uid){
        $this->uid = $uid;
        return $this;
    }

    public function getUid(){
        return $this->uid;
    }

    public function setExpTime($expTime){
        $this->expTime = $expTime;
        return $this;
    }

    public function setIss($iss){
        $this->iss = $iss;
        return $this;
    }

    public function setAud($aud){
        $this->aud = $aud;
        return $this;
    }

    public function setSecrect($secrect){
        $this->secrect = $secrect;
        return $this;
    }

    public function getConfig()
    {
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($this->secrect)
        );
        return $configuration;
    }

    /**
     * 签发令牌
     */
    public function encode()
    {
        $config = $this->getConfig();
        assert($config instanceof Configuration);

        $now = new DateTimeImmutable();

        $this->token = $config->builder()
            ->issuedBy($this->iss)
            ->permittedFor($this->aud)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify($this->expTime))
            ->withClaim('uid', $this->uid)
            ->getToken($config->signer(), $config->signingKey());

        return $this;
    }


    public function decode(){
        try{
            $config = $this->getConfig();
            assert($config instanceof Configuration);
            $token = $config->parser()->parse($this->token);
            assert($token instanceof Plain);
            $claims=json_decode(base64_decode($token->claims()->toString()),true);
            return $claims['uid'];
        }catch (RuntimeException $e){
            throw new ValidateException('非法token');
        }
    }


    /**
     * 验证令牌
     */
    public function validationToken()
    {
        $config = $this->getConfig();
        try {
            $token = $config->parser()->parse($this->token);
            assert($token instanceof UnencryptedToken);
        } catch (\Exception $e){
            throw new HttpException(config('my.tfErrorCode'),'token解析错误');
        }

        $validate_issued = new IssuedBy($this->iss);
        $config->setValidationConstraints($validate_issued);
        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token,...$constraints);
        } catch (RequiredConstraintsViolated $e){
            throw new HttpException(config('my.tfErrorCode'),'签发错误');
        }

        $validate_permitted_for = new PermittedFor($this->aud);
        $config->setValidationConstraints($validate_permitted_for);
        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token,...$constraints);
        } catch (RequiredConstraintsViolated $e){
            throw new HttpException(config('my.tfErrorCode'),'客户端错误');
        }

        $timezone = new \DateTimeZone('Asia/Shanghai');
        $time = new SystemClock($timezone);
        $validate_exp = new StrictValidAt($time);
        $config->setValidationConstraints($validate_exp);
        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token,...$constraints);
        } catch (RequiredConstraintsViolated $e){
            throw new HttpException(config('my.tfExpireCode'),'token过期');
        }

        $validate_signed = new SignedWith(new Sha256(),InMemory::base64Encoded($this->secrect));
        $config->setValidationConstraints($validate_signed);
        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token,...$constraints);
        } catch (RequiredConstraintsViolated $e){
            throw new HttpException(config('my.tfErrorCode'),'签名错误');
        }

        return true;

    }

}
