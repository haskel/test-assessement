#!/usr/bin/env php
<?php
declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Psr\Log\AbstractLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;
use Wisebits\Event\User\UserCreated;
use Wisebits\Event\User\UserDeleted;
use Wisebits\Event\User\UserUpdated;
use Wisebits\Exception\AppException;
use Wisebits\Exception\ValidationException;
use Wisebits\Listener\UserChangedListener;
use Wisebits\Repository\UserRepository;
use Wisebits\Service\ForbiddenEmailDomainsRegistry;
use Wisebits\Service\UsernameRestrictedWordsRegistry;
use Wisebits\Service\UserService;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as MySQLDriver;
use Wisebits\Validator\Constraint\EmailAllowedDomainNameValidator;
use Wisebits\Validator\Constraint\UserNameRestrictedWordsValidator;
use Wisebits\Validator\Constraint\UserUniqueEmailValidator;
use Wisebits\Validator\Constraint\UserUniqueNameValidator;

if (!is_file(dirname(__DIR__).'/vendor/autoload.php')) {
    throw new LogicException('Run "composer install" to install dependencies');
}

require_once dirname(__DIR__).'/vendor/autoload.php';

$config = require dirname(__DIR__).'/config/configuration.php';
$logger = createLoggerService();
$userService = createUserService($config, $logger);

// run
try {
    $user = $userService->create([
        'name' => 'johndoe',
        'email' => 'john.doe@wisebits.com',
        'notes' => 'This is a test user',
    ]);

} catch (ValidationException $e) {
    foreach ($e->violations as $violation) {
        echo $violation->getPropertyPath() . ": " . $violation->getMessage()."\n";
    }
} catch (AppException $e) {
    $logger->error($e->getMessage());
}

// create services
function createUserService(array $config, AbstractLogger $logger): UserService
{
    // connection
    $connection = new Connection($config['db'], new MySQLDriver());

    // event dispatcher
    $userChangedListener = new UserChangedListener($logger);
    $eventDispatcher = new EventDispatcher();
    $eventDispatcher->addListener(UserCreated::class, [$userChangedListener, 'onUserCreated'], 0);
    $eventDispatcher->addListener(UserUpdated::class, [$userChangedListener, 'onUserUpdated'], 0);
    $eventDispatcher->addListener(UserDeleted::class, [$userChangedListener, 'onUserDeleted'], 0);

    // user repository
    $userRepository = new UserRepository(
        $connection,
        $eventDispatcher
    );

    // validator
    $metadataFactory = new LazyLoadingMetadataFactory(new AttributeLoader());

    $validatorFactory = new ConstraintValidatorFactory([
        EmailAllowedDomainNameValidator::class => new EmailAllowedDomainNameValidator(new ForbiddenEmailDomainsRegistry($config['forbiddenDomains'])),
        UserUniqueEmailValidator::class => new UserUniqueEmailValidator($userRepository),
        UserUniqueNameValidator::class => new UserUniqueNameValidator($userRepository),
        UserNameRestrictedWordsValidator::class => new UserNameRestrictedWordsValidator(new UsernameRestrictedWordsRegistry($config['restrictedWords'])),
    ]);

    $translator = new class() implements TranslatorInterface, LocaleAwareInterface {
        use TranslatorTrait;
    };
    $translator->setLocale('en');

    $contextFactory = new ExecutionContextFactory($translator);
    $validator = new RecursiveValidator($contextFactory, $metadataFactory, $validatorFactory);

    // user service
    return new UserService($userRepository, $validator);
}

function createLoggerService(): AbstractLogger
{
    return new class extends AbstractLogger {
        public function log($level, $message, array $context = []): void
        {
            echo sprintf("[%s] %s\n", $level, $message);
        }
    };
}
