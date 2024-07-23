<?php

namespace GlsItaly\Tests;

use MarkoSirec\GlsItaly\SDK\Exceptions\AuthException;
use PHPUnit\Framework\TestCase;
use MarkoSirec\GlsItaly\SDK\Adapters\AuthAdapter as AuthAdapter;
use MarkoSirec\GlsItaly\SDK\Adapters\RequestData as RequestData;
use MarkoSirec\GlsItaly\SDK\Models\Auth as Auth;

class AuthAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingDataValidation(): void
    {
        $auth = new Auth();
        $authAdapter = new AuthAdapter($auth);
        $this->expectException(AuthException::class);
        $this->assertTrue((bool)$authAdapter->get());
    }

    public function testMissingBranchValidation(): void
    {
        $auth = new Auth();
        $auth->setClientId('1');
        $auth->setPassword('1');
        $authAdapter = new AuthAdapter($auth);

        $this->expectException(AuthException::class);
        $this->assertTrue((bool)$authAdapter->get());
    }

    public function testMissingClientValidation(): void
    {
        $auth = new Auth();
        $auth->setBranchId('1');
        $auth->setPassword('1');
        $authAdapter = new AuthAdapter($auth);

        $this->expectException(AuthException::class);
        $this->assertTrue((bool)$authAdapter->get());
    }

    public function testMissingPasswordValidation(): void
    {
        $auth = new Auth();
        $auth->setBranchId('1');
        $auth->setClientId('1');
        $authAdapter = new AuthAdapter($auth);

        $this->expectException(AuthException::class);
        $this->assertTrue((bool)$authAdapter->get());
    }

    public function testValidationSuccess(): void
    {
        $auth = new Auth();
        $auth->setBranchId('1');
        $auth->setClientId('1');
        $auth->setPassword('1');
        $authAdapter = new AuthAdapter($auth);
        $this->assertInstanceOf(RequestData::class, $authAdapter->get());
    }
}