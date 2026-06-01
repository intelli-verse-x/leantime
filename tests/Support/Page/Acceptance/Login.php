<?php

declare(strict_types=1);

namespace Tests\Support\Page\Acceptance;

use Codeception\Util\Fixtures;

class Login
{
    /**
     * @var \Tests\Support\AcceptanceTester;
     */
    protected $I;

    public function __construct(\Tests\Support\AcceptanceTester $I, Install $installPage)
    {
        $this->I = $I;
        $this->installPage = $installPage;
    }

    public function login($username, $password)
    {
        if ($this->loadSessionShapshot('leantime_session')) {
            return;
        }

        if (! Fixtures::exists('installed')) {
            $this->installPage->install(
                'test@leantime.io',
                'Test123456!',
                'John',
                'Smith',
                'Smith & Co'
            );
        }

        $this->I->amOnPage('/auth/login');
        $this->I->fillField(['name' => 'username'], $username);
        $this->I->fillField(['name' => 'password'], $password);
        $this->I->click('Login');
        $this->I->waitForElementVisible('.welcome-widget, .admin-dashboard, .page-head', 120);

        try {
            $this->I->seeElement('.welcome-widget');
            $this->I->see('Hi John');
        } catch (\Exception $e) {
            try {
                $this->I->seeElement('.admin-dashboard');
                $this->I->see('Active Projects');
            } catch (\Exception $e2) {
                $this->I->seeElement('.page-head');
            }
        }

        $this->saveSessionSnapshot('leantime_session');
    }

    protected function loadSessionShapshot(string $name): bool
    {
        if (! Fixtures::exists($name)) {
            return false;
        }

        $this->I->setCookie($name, Fixtures::get($name));

        return true;
    }

    protected function saveSessionSnapshot(string $name): void
    {
        Fixtures::add($name, $this->I->grabCookie($name));
    }
}
