<?php

namespace Leantime\Domain\Users\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Ramsey\Uuid\Uuid;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
class Users
{
    use DispatchesEvents;

    public function __construct(
        protected UserRepository $userRepo,
        protected LanguageCore $language,
        protected ProjectRepository $projectRepository,
        protected ClientRepository $clientRepo,
        protected AuthService $authService,
        protected Files $fileService,
        protected Avatarcreator $avatarcreator
    ) {}

    // GET

    /**
     * @return SVG|Response|string Returns either an SVG file, a file response or a path to a file
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getProfilePicture($id): SVG|Response|string
    {

        // Get profile picture definition from db
        $profile = $this->userRepo->getProfilePicture($id);

        // If can't find user, return ghost
        if (empty($profile)) {
            return $this->avatarcreator->getAvatar('👻');
        }

        // If user uploaded return uploaded file
        if (! empty($profile['profileId'])) {

            $file = $this->fileService->getFileById($profile['profileId']);
            if ($file) {
                return $file;
            }

        }

        // Otherwise return avatar
        $name = $profile['firstname'].' '.$profile['lastname'];

        return $this->avatarcreator->getAvatar($name);

    }

    /**
     * @api
     */
    public function editUser($values, $id): bool
    {

        $results = $this->userRepo->editUser($values, $id);
        self::dispatch_event('editUser', ['id' => $id, 'values' => $values]);

        return $results;
    }

    /**
     * @api
     */
    public function getNumberOfUsers(bool $activeOnly = false, bool $includeApi = true): int
    {
        $filters = [];

        if ($activeOnly) {
            $filters[] = ['status', '=', 'a'];
        }

        if (! $includeApi) {
            $filters[] = ['source', '!=', 'api'];
        }

        return $this->userRepo->getNumberOfUsers($filters);
    }

    /**
     * @param  false  $activeOnly
     *
     * @api
     */
    public function getAll(bool $activeOnly = false): mixed
    {
        $users = $this->userRepo->getAll($activeOnly);

        $users = self::dispatch_filter('getAll', $users);

        return $users;
    }

    /**
     * Request-level cache for getUser() results.
     * Prevents duplicate DB queries when the same user is fetched
     * multiple times within a single request (common in view composers).
     *
     * @var array<int|string, array|bool>
     */
    private array $userCache = [];

    /**
     * @api
     */
    public function getUser($id): array|bool
    {
        if (isset($this->userCache[$id])) {
            return $this->userCache[$id];
        }

        $user = $this->userRepo->getUser($id);
        $this->userCache[$id] = $user;

        return $user;
    }

    /**
     * @api
     */
    public function getUserByEmail($email, $status = 'a'): false|array
    {
        return $this->userRepo->getUserByEmail($email, $status);
    }

    /**
     * @api
     */
    public function getAllBySource($source): false|array
    {
        return $this->userRepo->getAllBySource($source);
    }

    // POST

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function setProfilePicture($photo, $id): void
    {
        $user = $this->getUser($id);

        // Save the path to the old picture
        if (isset($user['profileId']) && $user['profileId'] > 0) {
            $oldPicture = $user['profileId'];
        }

        $leantimeFile = $this->fileService->upload($photo, 'user', $id);

        if ($leantimeFile
            && $this->userRepo->setPicture($leantimeFile['fileId'], $id)
            && $oldPicture) {

            try {
                $this->fileService->deleteFile($oldPicture);
            } catch (\Exception $e) {
                Log::warning('Could not delete old profile picture: '.$e->getMessage());
                Log::warning($e);
            }

        }

    }

    /**
     * @api
     */
    public function updateUserSettings($category, $setting, $value): bool
    {

        $filteredInput = htmlspecialchars($setting);
        $filteredValue = htmlspecialchars($value);

        session(['usersettings.'.$category.'.'.$filteredInput => $filteredValue]);

        $serializeSettings = serialize(session('usersettings'));

        return $this->userRepo->patchUser(session('userdata.id'), ['settings' => $serializeSettings]);
    }

    /**
     * checkPasswordStrength - Checks password strength for minimum requirements
     * Current requirements are:
     * Password must be at least 8 characters in length.
     * Password must include at least one upper case letter.
     * Password must include at least one number.
     * Password must include at least one special character.
     *
     * @param  string  $password  The string to be checked
     * @return bool returns true if password meets requirements
     *
     * @api
     */
    public function checkPasswordStrength(string $password): bool
    {

        // Validate password strength
        // Password must be at least 8 characters in length.
        // Password must include at least one upper case letter.
        // Password must include at least one number.
        // Password must include at least one special character.

        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if (! $uppercase || ! $lowercase || ! $number || ! $specialChars || strlen($password) < 8) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * createUserInvite - generates a new invite token, creates the user in the db and sends the invitation email TODO: Should accept userModel
     *
     * @param  array  $values  basic user values
     * @return bool|int returns new user id on success, false on failure
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function createUserInvite(array $values): bool|int
    {

        // Generate strong password
        $tempPasswordVar = Uuid::uuid4()->toString();
        $inviteCode = Uuid::uuid4()->toString();

        $values['password'] = $tempPasswordVar;
        $values['status'] = 'i';
        $values['pwReset'] = $inviteCode;
        $values['pwResetExpiration'] = now()->addDays(30)->format('Y-m-d H:i:s');

        $result = $this->userRepo->addUser($values);

        if ($result === false) {
            Log::warning('Failed to create invited user', ['email' => $this->maskEmail($values['user'] ?? '')]);

            return false;
        }

        Log::info('User invite created', ['userId' => $result, 'email' => $this->maskEmail($values['user'] ?? '')]);

        $emailSent = $this->sendUserInvite($inviteCode, $values['user']);

        if (! $emailSent) {
            Log::warning('Invite email could not be sent for new user', ['userId' => $result, 'email' => $this->maskEmail($values['user'] ?? '')]);
        }

        return $result;
    }

    /**
     * sendUserInvite - sends an invite email for the given invite code
     *
     * @param  string  $inviteCode  The UUID invite token
     * @param  string  $user        The recipient email address
     * @return bool True on success, false on failure
     */
    public function sendUserInvite(string $inviteCode, string $user): bool
    {

        try {
            $mailer = app()->make(MailerCore::class);
            $mailer->setContext('new_user');

            $mailer->setSubject($this->language->__('email_notifications.new_user_subject'));
            $actual_link = BASE_URL.'/auth/userInvite/'.$inviteCode;

            $message = sprintf(
                $this->language->__('email_notifications.user_invite_message'),
                session('userdata.name') ?? 'Leantime',
                $actual_link,
                $user
            );

            $mailer->setHtml($message);

            $to = [$user];

            $mailer->sendMail($to, session('userdata.name') ?? 'Leantime');

            Log::info('Invite email sent successfully', ['recipient' => $this->maskEmail($user)]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send invite email', ['recipient' => $this->maskEmail($user), 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * resendInvite - regenerates an invite token for an existing pending user and resends the email
     *
     * @param  int  $userId  The user ID to resend the invite to
     * @return bool True on success, false on failure
     *
     * @api
     */
    public function resendInvite(int $userId): bool
    {
        $user = $this->getUser($userId);

        if (! $user || strtolower((string) $user['status']) !== 'i') {
            Log::warning('resendInvite called for non-invited user', ['userId' => $userId]);

            return false;
        }

        $inviteCode = Uuid::uuid4()->toString();
        $expiry = now()->addDays(30)->format('Y-m-d H:i:s');

        $this->userRepo->patchUser($userId, [
            'pwReset' => $inviteCode,
            'pwResetExpiration' => $expiry,
        ]);

        Log::info('Invite resent', ['userId' => $userId, 'email' => $this->maskEmail($user['username'])]);

        return $this->sendUserInvite($inviteCode, $user['username']);
    }

    /**
     * clearInviteToken - removes the invite token and expiry after a user accepts their invite
     *
     * @param  int  $userId  The user ID whose invite token should be cleared
     *
     * @api
     */
    public function clearInviteToken(int $userId): void
    {
        $this->userRepo->patchUser($userId, [
            'pwReset' => null,
            'pwResetExpiration' => null,
        ]);

        Log::info('Invite token cleared after acceptance', ['userId' => $userId]);
    }

    /**
     * addUser - simple service wrapper to create a new user
     *
     * TODO: Should accept userModel
     *
     * @param  array  $values  basic user values
     * @return bool|int returns new user id on success, false on failure
     *
     * @api
     */
    public function addUser(array $values): bool|int
    {
        $values = [
            'firstname' => $values['firstname'] ?? '',
            'lastname' => $values['lastname'] ?? '',
            'phone' => $values['phone'] ?? '',
            'user' => $values['username'] ?? $values['user'],
            'role' => $values['role'],
            'notifications' => $values['notifications'] ?? 1,
            'clientId' => $values['clientId'] ?? '',
            'password' => $values['password'],
            'source' => $values['source'] ?? '',
            'pwReset' => $values['pwReset'] ?? '',
            'status' => $values['status'] ?? '',
            'createdOn' => $values['createdOn'] ?? '',
            'jobTitle' => $values['jobTitle'] ?? '',
            'jobLevel' => $values['jobLevel'] ?? '',
            'department' => $values['department'] ?? '',
        ];

        return $this->userRepo->addUser($values);
    }

    /**
     * usernameExist - Checks if a given username (email) is already in the db
     *
     * TODO: Should accept userModel
     *
     * @param  string  $username  username
     * @param  int|string  $notUserId  optional userId to skip. (used when changing email addresses to a new one, skips checking the old one)
     * @return bool returns true or false
     *
     * @api
     */
    public function usernameExist(string $username, int|string $notUserId = ''): bool
    {
        return $this->userRepo->usernameExist($username, $notUserId);
    }

    /**
     * getUsersWithProjectAccess - gets all users who can access a project
     *
     * TODO: Should return usermodel
     *
     * @param  int  $currentUser  user who is trying to access the project
     * @param  int  $projectId  project id
     * @return array returns array of users
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getUsersWithProjectAccess(int $currentUser, int $projectId): array
    {
        $users = [];

        if ($this->projectRepository->isUserAssignedToProject($currentUser, $projectId)) {
            $project = $this->projectRepository->getProject($projectId);

            if ($project['psettings'] == 'all') {
                return $this->getAll();
            }

            if ($project['psettings'] == 'clients') {
                $clientUsers = $this->clientRepo->getClientsUsers($project['clientId']);
                $projectUsers = $this->projectRepository->getUsersAssignedToProject($projectId);
                $users = $clientUsers;

                foreach ($projectUsers as $user) {
                    $column = array_column($users, 'id');
                    $search = array_search($user['id'], $column);
                    if (array_search($user['id'], $column) === false) {
                        $users[] = $user;
                    }
                }

                return $users;
            }

            if ($project['psettings'] == 'restricted' || $project['psettings'] == '') {
                $users = $this->projectRepository->getUsersAssignedToProject($projectId);

                return $users;
            }
        }

        return [];
    }

    /**
     * @api
     */
    public function editOwn($values, $id): void
    {
        $this->userRepo->editOwn($values, $id);

        $user = $this->getUser($id);

        $this->authService->setUserSession($user);

        self::dispatch_event('editUser', ['id' => $id, 'values' => $values]);
    }

    /**
     * Delete the user with the specified id.
     *
     * @param  int  $id  The id of the user to delete.
     * @return bool True if the user was deleted successfully, false otherwise.
     *
     * @throws \Exception If the user is not authorized to delete the user.
     *
     * @api
     */
    public function deleteUser(int $id): bool
    {

        if (Auth::userIsAtLeast(Roles::$admin, true)) {
            $this->userRepo->deleteUser($id);
            $this->projectRepository->deleteAllProjectRelations($id);

            return true;
        }

        throw new \Exception('Not authorized');
    }

    /**
     * maskEmail - masks an email address for safe logging (e.g. jo***@example.com)
     *
     * @param  string  $email  The email to mask
     * @return string The masked email
     */
    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($local === '') {
            return '***@'.$domain;
        }

        $visibleChars = min(2, strlen($local));
        $maskedLocal = substr($local, 0, $visibleChars).'***';

        return $maskedLocal.'@'.$domain;
    }
}
