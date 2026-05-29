<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Worktracker\Services\WorkTracker as WorkTrackerService;
use Symfony\Component\HttpFoundation\Response;

class Logout extends Controller
{
    private AuthService $authService;

    private WorkTrackerService $workTrackerService;

    /**
     * init - initialize private variables
     */
    public function init(AuthService $authService, WorkTrackerService $workTrackerService): void
    {
        $this->authService = $authService;
        $this->workTrackerService = $workTrackerService;
    }

    /**
     * get - handle get requests
     *
     * Before destroying the session we auto-close any open WorkTracker session
     * for this user. Otherwise an employee who forgets to click Stop and then
     * logs out (or just closes the tab where a final logout fires) would leave
     * a session ticking forever on the server while their UI looks idle.
     */
    public function get(array $params): Response
    {
        $userId = (int) session('userdata.id');
        if ($userId > 0) {
            try {
                $this->workTrackerService->closeAllForUser($userId);
            } catch (\Throwable $e) {
                // Never block logout on a WorkTracker hiccup — log and continue.
                Log::warning('WorkTracker auto-close on logout failed', [
                    'userId' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->authService->logout();

        return FrontcontrollerCore::redirect(BASE_URL.'/');
    }
}
