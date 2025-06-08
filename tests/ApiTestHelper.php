<?php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Admin;
use App\Models\User;
use Tests\CreatesApplication;


abstract class ApiTestHelper extends BaseTestCase
{
    // use CreatesApplication;

    protected function getAdminToken()
    {
        $admin = Admin::factory()->create();
        return $admin->createToken('test-token')->plainTextToken;
    }

    protected function getUserToken()
    {
        $user = User::factory()->create();
        return $user->createToken('test-token')->plainTextToken;
    }

    protected function actingAsAdmin()
    {
        $token = $this->getAdminToken();
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }

    protected function actingAsUser()
    {
        $token = $this->getUserToken();
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}