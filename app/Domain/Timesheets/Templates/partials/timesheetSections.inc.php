<?php
defined('RESTRICTED') or exit('Restricted access');

$activeTimesheetSection = $timesheetSection ?? 'my';
$showTeamTimesheets = (bool) ($canShowTeamTimesheets ?? false);
?>

<style>
    .timesheet-section-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0 0 16px;
        border-bottom: 1px solid var(--main-border-color);
    }

    .timesheet-section-tabs a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        margin-bottom: -1px;
        border: 1px solid transparent;
        border-radius: var(--box-radius-small) var(--box-radius-small) 0 0;
        color: var(--primary-font-color);
        text-decoration: none;
        font-weight: 600;
    }

    .timesheet-section-tabs a:hover {
        color: var(--accent1);
    }

    .timesheet-section-tabs a.active {
        border-color: var(--main-border-color);
        border-bottom-color: var(--secondary-background);
        background: var(--secondary-background);
        color: var(--accent1);
    }
</style>

<div class="timesheet-section-tabs">
    <?php if ($showTeamTimesheets) { ?>
        <a href="<?= BASE_URL ?>/timesheets/showAll" class="<?= $activeTimesheetSection === 'team' ? 'active' : '' ?>">
            <i class="fa fa-users"></i>
            Team Timesheets
        </a>
    <?php } ?>
    <a href="<?= BASE_URL ?>/timesheets/showMy" class="<?= $activeTimesheetSection === 'my' ? 'active' : '' ?>">
        <i class="fa fa-user-clock"></i>
        My Timesheets
    </a>
</div>
