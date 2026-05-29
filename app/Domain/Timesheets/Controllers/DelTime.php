<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Illuminate\Http\RedirectResponse;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

class DelTime extends Controller
{
    private TimesheetRepository $timesheetsRepo;

    private UserRepository $users;

    /**
     * init - initialize private variable
     */
    public function init(TimesheetRepository $timesheetsRepo, UserRepository $users): void
    {
        $this->timesheetsRepo = $timesheetsRepo;
        $this->users = $users;
    }

    /**
     * run - display template and edit data
     */
    public function run(): Response|RedirectResponse
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$teamlead, Roles::$editor], true);

        if (isset($_GET['id']) === true) {
            $id = (int) ($_GET['id']);

            // Ownership check: a user may only delete their own time entries.
            // TL+ / managers / admins may delete any entry.
            $timesheet = $this->timesheetsRepo->getTimesheet($id);

            if ($timesheet === false) {
                return $this->tpl->displayPartial('errors.error404');
            }

            $isOwner = (int) ($timesheet['userId'] ?? 0) === (int) session('userdata.id');
            $isManager = Auth::userIsAtLeast(Roles::$manager, true);
            $isPeopleManager = $this->users->isManagerForUser((int) session('userdata.id'), (int) ($timesheet['userId'] ?? 0));

            $projectService = app()->make(\Leantime\Domain\Projects\Services\Projects::class);
            $projectRoleNum = $projectService->getProjectRole(session('userdata.id'), $timesheet['projectId']);
            $isProjectTL = ($projectRoleNum !== '' && (int) $projectRoleNum >= 25);

            if (! $isOwner && ! $isManager && ! $isPeopleManager && ! $isProjectTL) {
                return $this->tpl->displayPartial('errors.error403');
            }

            if (isset($_POST['del']) === true) {
                $this->timesheetsRepo->deleteTime($id);

                $this->tpl->setNotification('notifications.time_deleted_successfully', 'success');

                if (session()->exists('lastPage')) {
                    return Frontcontroller::redirect(session('lastPage'));
                } else {
                    return Frontcontroller::redirect(BASE_URL.'/timsheets/showMyList');
                }
            }

            $this->tpl->assign('id', $id);

            return $this->tpl->displayPartial('timesheets.delTime');
        } else {
            return $this->tpl->displayPartial('errors.error403');
        }
    }
}
