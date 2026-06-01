<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SetDarkModeAllCommand
 *
 * Sets colorMode to 'dark' for all existing users, so users previously on
 * a legacy theme get the TT Portal dark theme on their next login.
 * Users can still freely switch between light and dark via the toggle at any time.
 */
#[AsCommand(
    name: 'theme:set-darkmode-all',
    description: 'Sets dark mode for all existing users (they can still switch to light at any time)',
)]
class SetDarkModeAllCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'color-mode',
            null,
            InputOption::VALUE_OPTIONAL,
            'Color mode to set for all users: "dark" or "light"',
            'dark'
        );
    }

    /**
     * Execute the command.
     *
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ! defined('BASE_URL') && define('BASE_URL', '');
        ! defined('CURRENT_URL') && define('CURRENT_URL', '');

        $io = new SymfonyStyle($input, $output);

        $colorMode = $input->getOption('color-mode');
        if (! in_array($colorMode, ['dark', 'light'])) {
            $io->error('Invalid color-mode. Use "dark" or "light".');

            return Command::INVALID;
        }

        try {
            $usersRepo = app()->make(Users::class);
            $settingRepo = app()->make(Setting::class);

            $allUsers = $usersRepo->getAll(activeOnly: false);

            if (empty($allUsers)) {
                $io->warning('No users found in the database.');

                return Command::SUCCESS;
            }

            $io->info(sprintf('Setting colorMode to "%s" for %d users...', $colorMode, count($allUsers)));

            $updated = 0;
            foreach ($allUsers as $user) {
                $userId = $user['id'] ?? null;
                if (! $userId) {
                    continue;
                }

                $key = 'usersettings.'.$userId.'.colorMode';
                $settingRepo->saveSetting($key, $colorMode);
                $updated++;
            }

            $io->success(sprintf('Done! %d user(s) updated to "%s" mode.', $updated, $colorMode));
            $io->note('Users can change their preference at any time via the light/dark toggle in the app.');

            return Command::SUCCESS;
        } catch (\Exception $ex) {
            $io->error($ex->getMessage());

            return Command::FAILURE;
        }
    }
}
