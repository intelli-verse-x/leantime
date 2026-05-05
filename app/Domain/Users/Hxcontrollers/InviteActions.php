<?php

namespace Leantime\Domain\Users\Hxcontrollers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Services\Users as UserService;

/**
 * InviteActions - HTMX controller for invite management actions (resend, copy link).
 */
class InviteActions extends HtmxController
{
    protected static string $view = 'users::partials.inviteActions';

    private UserService $userService;

    /**
     * init - initialise dependencies
     */
    public function init(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * resend - resend an invite email to a pending user (with 5-minute throttle per user)
     *
     * @param  array  $params  Route parameters; expects 'id' = userId
     */
    public function resend(array $params): void
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        $userId = (int) ($params['id'] ?? 0);

        if ($userId === 0) {
            $this->tpl->setNotification($this->language->__('notification.error'), 'error');
            $this->setHTMXEvent('HTMX.ShowNotification');

            return;
        }

        $throttleKey = 'invite_resend_user_'.$userId;

        if (Cache::has($throttleKey)) {
            $this->tpl->setNotification($this->language->__('notification.invite_too_soon'), 'error');
            $this->setHTMXEvent('HTMX.ShowNotification');

            return;
        }

        $result = $this->userService->resendInvite($userId);

        if ($result) {
            Cache::put($throttleKey, true, 300); // throttle for 5 minutes
            Log::info('Invite resent via HxController', ['userId' => $userId, 'admin' => session('userdata.id')]);
            $this->tpl->setNotification($this->language->__('notification.user_invited_successfully'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notification.error'), 'error');
        }

        $this->setHTMXEvent('HTMX.ShowNotification');
    }

    /**
     * getLink - return the invite link partial so the admin can copy it
     *
     * @param  array  $params  Route parameters; expects 'id' = userId
     */
    public function getLink(array $params): void
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        $userId = (int) ($params['id'] ?? 0);

        if ($userId === 0) {
            $this->tpl->setNotification($this->language->__('notification.error'), 'error');
            $this->setHTMXEvent('HTMX.ShowNotification');

            return;
        }

        $user = $this->userService->getUser($userId);

        if (! $user || strtolower((string) $user['status']) !== 'i' || empty($user['pwReset'])) {
            $this->tpl->setNotification($this->language->__('notification.error'), 'error');
            $this->setHTMXEvent('HTMX.ShowNotification');

            return;
        }

        $inviteLink = BASE_URL.'/auth/userInvite/'.$user['pwReset'];

        $this->tpl->assign('inviteLink', $inviteLink);
        $this->tpl->assign('userId', $userId);
    }
}
