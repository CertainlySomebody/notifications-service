<?php

namespace App\Notification\Interface\Cli;

use App\Notification\Application\Service\NotificationSender;
use App\Notification\Domain\Model\Notification;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:create-notification',
    description: 'Create and send test notification'
)]
final class SendNotificationCommand extends Command
{
    public function __construct(private NotificationSender $sender)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = new QuestionHelper();

        // 1st channel question
        $channel = new ChoiceQuestion(
            'Choose notification channels (comma separated, e.g. email, sms, push):',
            ['email', 'sms', 'push'],
            0
        );

        $channel->setMultiselect(true);
        $channels = $helper->ask($input, $output, $channel);

        // 2nd email question
        $userId = new Question(
            'Write email address in order to send test notification: ',
        );
        $userId = $helper->ask($input, $output, $userId);

        // SMS number input (if needed)
        $phone = null;
        if (in_array('sms', $channels)) {
            $phone = new Question(
                'Enter phone number for SMS: '
            );
            $phone = $helper->ask($input, $output, $phone);
        }

        // 3rd message question
        $message = new Question(
            'Write message in order to send test notification: ',
        );
        $message = $helper->ask($input, $output, $message);

        $notification = new Notification($userId, $message, $channels, $phone);

        try {
            $this->sender->send($notification);
            $output->writeln('<info>Notification sent!</info>');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Error sending notification: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
