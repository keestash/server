<?php

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\ConfigProvider;
use KSP\Core\Repository\Payment\IPaymentLogRepository;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Encryption\IBase64Service;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Config\Config;
use Laminas\I18n\Validator\PhoneNumber as PhoneValidator;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\Uri as UriValidator;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;

require_once __DIR__ . '/vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require_once __DIR__ . '/lib/start.php';

/** @var IUserRepository $userRepository */
$userRepository = $container->get(IUserRepository::class);
/** @var IUserRepositoryService $userRepositoryService */
$userRepositoryService = $container->get(IUserRepositoryService::class);
/** @var IPaymentLogRepository $paymentLogRepository */
$paymentLogRepository = $container->get(IPaymentLogRepository::class);
/** @var IUserService $userService */
$userService = $container->get(IUserService::class);
/** @var IQueueService $queueService */
$queueService = $container->get(IQueueService::class);
/** @var IQueueRepository $queueRepository */
$queueRepository = $container->get(IQueueRepository::class);
/** @var IBase64Service $base64Service */
$base64Service = $container->get(IBase64Service::class);
/** @var IEventService $eventService */
$eventService = $container->get(IEventService::class);
/** @var IDateTimeService $dateTimeService */
$dateTimeService = $container->get(IDateTimeService::class);
/** @var Config $config */
$config = $container->get(Config::class);
/** @var PhoneValidator $phoneValidator */
$phoneValidator = $container->get(PhoneValidator::class);
/** @var EmailValidator $emailValidator */
$emailValidator = $container->get(EmailValidator::class);
/** @var UriValidator $uriValidator */
$uriValidator = $container->get(UriValidator::class);

/** @var \KSP\Core\Repository\LDAP\ILDAPUserRepository $ldapRepository */
$ldapRepository = $container->get(\KSP\Core\Repository\LDAP\ILDAPUserRepository::class);
return;

dump((int)'2M');exit();
dump($userService->validateWithAllCountries("+49 (0) 69 175 111 52"));
dump($uriValidator->isValid('https://www.keestash.com'));
return;
$eventService->registerAll($config->get(ConfigProvider::EVENTS)->toArray());

$d = new DateTimeImmutable();
$d = $d->setTimestamp(1672225761);
$x = $d->modify('+3 month');
dump($d->format(DateTimeInterface::ATOM));
dump($x->format(DateTimeInterface::ATOM));
exit();
$l = $paymentLogRepository->getByx§User(
    $userRepository->getUserById("2")
);
dump($l);

return;

$messageArray = $queueRepository->getByUuid('a77bd8b9-d13d-4b87-aaea-01857fd1cc9b');

$user = $userService->toNewUser(
    [
        'user_name'    => Uuid::uuid4()->toString()
        , 'email'      => 'n@keestash.com'
        , 'last_name'  => md5(Uuid::uuid4()->toString())
        , 'first_name' => md5('n@keestash.com')
        , 'password'   => Uuid::uuid4()->toString()
        , 'phone'      => '004914567889'
        , 'website'    => 'https://keestash.com'
        , 'locked' => true
    ]
);

$userRepositoryService->createUser($user);
dump($user->isLocked());