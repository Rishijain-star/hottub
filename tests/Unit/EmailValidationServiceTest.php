<?php

namespace Tests\Unit;

use App\Services\EmailValidationService;
use Tests\TestCase;

class EmailValidationServiceTest extends TestCase
{
    public function test_disposable_email_is_rejected(): void
    {
        $service = app(EmailValidationService::class);

        $this->assertTrue($service->isDisposable('test@mailinator.com'));
        $this->assertTrue($service->isDisposable('user@yopmail.com'));
        $this->assertFalse($service->isDisposable('user@gmail.com'));
    }
}
