<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users;

class PatchUserSettings extends Controller
{
    private Auth $authService;

    private Users $userRepository;

    private SettingService $settingsService;

    private ThemeCore $themeCore;

    public function init(
        Auth $authService,
        Users $userRepository,
        SettingService $settingsService,
        ThemeCore $themeCore
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->settingsService = $settingsService;
        $this->themeCore = $themeCore;
    }

    /**
     * Handle PATCH requests
     */
    public function patch($params)
    {
        // Check if user is logged in
        if (! $this->authService->isLoggedIn()) {
            return $this->tpl->displayJson(['status' => 'error', 'message' => 'Not authorized'], 401);
        }

        $userId = $this->authService->getUserId();

        // Handle dynamic colorMode (theme switcher) updates
        if (isset($params['colorMode'])) {
            $colorMode = htmlentities($params['colorMode']);
            if (in_array($colorMode, ['light', 'dark'])) {
                $this->settingsService->saveSetting('usersettings.'.$userId.'.colorMode', $colorMode);
                $this->themeCore::clearCache();
                $this->themeCore->setColorMode($colorMode);

                return $this->tpl->displayJson(['status' => 'success']);
            }
        }

        // Handle modal settings updates
        if (isset($params['patchModalSettings']) && $params['patchModalSettings'] == 1) {
            if (isset($params['settings'])) {
                $modalKey = htmlspecialchars($params['settings']);
                $permanent = isset($params['permanent']) && $params['permanent'] == 1;

                // Store in session
                if (! session()->exists('usersettings.modals')) {
                    session(['usersettings.modals' => []]);
                }

                session(['usersettings.modals.'.$modalKey => 1]);

                // If permanent, also store in user settings
                if ($permanent) {
                    $this->userRepository->updateUserSettings($userId, ['modals.'.$modalKey => 1]);
                }

                return $this->tpl->displayJson(['status' => 'success']);
            }
        }

        return $this->tpl->displayJson(['status' => 'error', 'message' => 'Invalid request'], 400);
    }
}
