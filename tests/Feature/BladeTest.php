<?php

namespace NabilHassen\LaravelUsageLimiter\Tests\Feature;

use NabilHassen\LaravelUsageLimiter\Tests\TestCase;

class BladeTest extends TestCase
{
    public function test_limit_directive_evaluates_false_if_limit_does_not_exist(): void
    {
        $view = $this->view('index', ['user' => $this->user]);

        $view->assertSee('User does not have enough limit to create locations');
        $view->assertDontSee('User has enough limit to create locations');
    }

    public function test_limit_directive_evaluates_false_if_limit_is_not_set_on_model(): void
    {
        $this->createLimit();

        $view = $this->view('index', ['user' => $this->user]);

        $view->assertSee('User does not have enough limit to create locations');
        $view->assertDontSee('User has enough limit to create locations');
    }

    public function test_limit_directive_evaluates_true(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit);

        $view = $this->view('index', ['user' => $this->user]);

        $view->assertSee('User has enough limit to create locations');
        $view->assertDontSee('User does not have enough limit to create locations');
    }

    public function test_limit_directive_evaluates_true_with_limit_instance(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit);

        $view = $this->view('index', [
            'limit' => $limit,
            'user' => $this->user,
        ]);

        $view->assertSee('User has enough limit to create locations');
        $view->assertDontSee('User does not have enough limit to create locations');
    }
}
