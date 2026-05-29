<?php

namespace Leantime\Domain\Worktracker\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Leantime\Core\Db\Db as DbCore;

class WorkTracker
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
        $this->ensureTableExists();
    }

    /**
     * Create zp_work_sessions table if it does not exist yet.
     * This is intentionally idempotent so the module self-installs on first use.
     */
    private function ensureTableExists(): void
    {
        if (Schema::hasTable('zp_work_sessions')) {
            return;
        }

        Schema::create('zp_work_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->unsignedInteger('total_duration')->nullable()->comment('Active seconds, excludes paused intervals');
            $table->enum('status', ['running', 'paused', 'completed'])->default('running');
            $table->unsignedInteger('paused_seconds')->default(0)->comment('Total cumulative break time in seconds');
            $table->dateTime('last_paused_at')->nullable()->comment('When the current pause started; NULL when running or completed');
            $table->string('start_screenshot', 512)->nullable();
            $table->string('end_screenshot', 512)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'status']);
            $table->index('start_time');
        });
    }

    /**
     * Create a new running session and return its ID.
     */
    public function createSession(int $userId, string $screenshotPath): int
    {
        return $this->db->table('zp_work_sessions')->insertGetId([
            'user_id'          => $userId,
            'start_time'       => now()->toDateTimeString(),
            'status'           => 'running',
            'paused_seconds'   => 0,
            'last_paused_at'   => null,
            'start_screenshot' => $screenshotPath,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /**
     * Close an active session: save end screenshot, calculate active duration
     * (gross elapsed minus all paused intervals), mark completed.
     *
     * If the session is currently in the `paused` state, the still-open pause
     * delta is folded into paused_seconds before the active duration is computed
     * so we don't count break time toward total_duration.
     */
    public function closeSession(int $sessionId, int $userId, string $screenshotPath): bool
    {
        $session = $this->db->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->whereIn('status', ['running', 'paused'])
            ->first();

        if (! $session) {
            return false;
        }

        $endTime       = new \DateTime();
        $startTime     = new \DateTime($session->start_time);
        $pausedSeconds = (int) ($session->paused_seconds ?? 0);

        // If we're closing while paused, finalize the still-open pause delta first.
        if ($session->status === 'paused' && ! empty($session->last_paused_at)) {
            $pauseStart = new \DateTime($session->last_paused_at);
            $pausedSeconds += max(0, $endTime->getTimestamp() - $pauseStart->getTimestamp());
        }

        $grossSeconds  = $endTime->getTimestamp() - $startTime->getTimestamp();
        $activeSeconds = max(0, $grossSeconds - $pausedSeconds);

        return (bool) $this->db->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->update([
                'end_time'        => $endTime->format('Y-m-d H:i:s'),
                'total_duration'  => $activeSeconds,
                'paused_seconds'  => $pausedSeconds,
                'last_paused_at'  => null,
                'status'          => 'completed',
                'end_screenshot'  => $screenshotPath,
                'updated_at'      => now(),
            ]);
    }

    /**
     * Transition a running session into the `paused` state.
     * Records the pause start so we can compute the delta on resume.
     */
    public function pauseSession(int $sessionId, int $userId): bool
    {
        $affected = $this->db->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->where('status', 'running')
            ->update([
                'status'         => 'paused',
                'last_paused_at' => now()->toDateTimeString(),
                'updated_at'     => now(),
            ]);

        return $affected > 0;
    }

    /**
     * Transition a paused session back to `running`.
     * Folds the just-finished pause interval into the cumulative paused_seconds.
     */
    public function resumeSession(int $sessionId, int $userId): bool
    {
        $session = $this->db->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->where('status', 'paused')
            ->first();

        if (! $session || empty($session->last_paused_at)) {
            return false;
        }

        $pauseStart = new \DateTime($session->last_paused_at);
        $now        = new \DateTime();
        $delta      = max(0, $now->getTimestamp() - $pauseStart->getTimestamp());

        $affected = $this->db->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->where('status', 'paused')
            ->update([
                'status'         => 'running',
                'paused_seconds' => (int) ($session->paused_seconds ?? 0) + $delta,
                'last_paused_at' => null,
                'updated_at'     => now(),
            ]);

        return $affected > 0;
    }

    /**
     * Close every open (running OR paused) session for a user.
     * Called when the user logs out so they don't leave a timer ticking
     * indefinitely after the browser session is gone. No end screenshot —
     * by the time logout fires the screen-capture stream is no longer
     * available and prompting for permission would be a confusing UX.
     *
     * @return int  number of sessions closed
     */
    public function closeOpenSessionsForUser(int $userId): int
    {
        $open = $this->db->table('zp_work_sessions')
            ->where('user_id', $userId)
            ->whereIn('status', ['running', 'paused'])
            ->get();

        $closed = 0;
        foreach ($open as $session) {
            if ($this->closeSession((int) $session->id, $userId, '')) {
                $closed++;
            }
        }

        return $closed;
    }

    /**
     * Retrieve the currently open session (running OR paused) for a user.
     * Renamed semantically from "active" since paused sessions are still
     * the user's current session — they just aren't accruing time.
     *
     * @return object|false
     */
    public function getActiveSession(int $userId): object|false
    {
        $row = $this->db->table('zp_work_sessions')
            ->where('user_id', $userId)
            ->whereIn('status', ['running', 'paused'])
            ->orderByDesc('start_time')
            ->first();

        return $row ?: false;
    }

    /**
     * Retrieve a single session by ID, validated against userId.
     *
     * @return object|false
     */
    public function getSession(int $sessionId, int $userId): object|false
    {
        $row = $this->db->table('zp_work_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        return $row ?: false;
    }

    /**
     * Paginated session history for one employee.
     */
    public function getUserSessions(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->db->table('zp_work_sessions')
            ->where('user_id', $userId)
            ->orderByDesc('start_time')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();
    }

    /**
     * Count all sessions for one employee (for pagination).
     */
    public function countUserSessions(int $userId): int
    {
        return $this->db->table('zp_work_sessions')
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Total tracked seconds for a user on a given IST calendar day.
     * Storage is UTC, so we translate the IST date into the matching UTC
     * datetime range and use whereBetween. This keeps "Today" rolling over
     * at midnight IST instead of midnight UTC (which would be 05:30 IST).
     */
    public function getDayTotal(int $userId, string $istDate): int
    {
        [$fromUtc, $toUtc] = $this->istDateToUtcRange($istDate);

        $result = $this->db->table('zp_work_sessions')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$fromUtc, $toUtc])
            ->sum('total_duration');

        return (int) $result;
    }

    /**
     * Total tracked seconds for a user in the current IST week (Mon–Sun).
     */
    public function getWeekTotal(int $userId): int
    {
        $tz       = \Leantime\Domain\Worktracker\Services\WorkTracker::DISPLAY_TZ;
        $monday   = \Carbon\Carbon::now($tz)->startOfWeek()->format('Y-m-d 00:00:00');
        $sunday   = \Carbon\Carbon::now($tz)->endOfWeek()->format('Y-m-d 23:59:59');
        $mondayUtc = \Carbon\Carbon::parse($monday, $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $sundayUtc = \Carbon\Carbon::parse($sunday, $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');

        $result = $this->db->table('zp_work_sessions')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('start_time', [$mondayUtc, $sundayUtc])
            ->sum('total_duration');

        return (int) $result;
    }

    /**
     * Translate an IST calendar date "Y-m-d" into the matching UTC
     * datetime range [00:00:00 IST → 23:59:59 IST] expressed in UTC.
     *
     * @return array{0:string,1:string}  [fromUtc, toUtc] in "Y-m-d H:i:s"
     */
    private function istDateToUtcRange(string $istDate): array
    {
        $tz = \Leantime\Domain\Worktracker\Services\WorkTracker::DISPLAY_TZ;
        $from = \Carbon\Carbon::parse($istDate . ' 00:00:00', $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $to   = \Carbon\Carbon::parse($istDate . ' 23:59:59', $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');

        return [$from, $to];
    }

    /**
     * All sessions for admin view, joined with user data.
     */
    public function getAllSessions(int $limit = 50, int $offset = 0): array
    {
        return $this->db->table('zp_work_sessions')
            ->leftJoin('zp_user', 'zp_work_sessions.user_id', '=', 'zp_user.id')
            ->select(
                'zp_work_sessions.*',
                'zp_user.firstname',
                'zp_user.lastname',
                'zp_user.username'
            )
            ->orderByDesc('zp_work_sessions.start_time')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();
    }

    /**
     * Count all sessions across all users (for admin pagination).
     */
    public function countAllSessions(): int
    {
        return $this->db->table('zp_work_sessions')->count();
    }

    /**
     * Today's total in seconds across all employees, for admin summary.
     * "Today" is the calendar day in IST (the display/business timezone).
     */
    public function getTodayGrandTotal(): int
    {
        $tz = \Leantime\Domain\Worktracker\Services\WorkTracker::DISPLAY_TZ;
        [$fromUtc, $toUtc] = $this->istDateToUtcRange(\Carbon\Carbon::now($tz)->toDateString());

        $result = $this->db->table('zp_work_sessions')
            ->where('status', 'completed')
            ->whereBetween('start_time', [$fromUtc, $toUtc])
            ->sum('total_duration');

        return (int) $result;
    }

    /**
     * Number of employees currently running a session.
     */
    public function getActiveCount(): int
    {
        return $this->db->table('zp_work_sessions')
            ->where('status', 'running')
            ->distinct('user_id')
            ->count('user_id');
    }
}
