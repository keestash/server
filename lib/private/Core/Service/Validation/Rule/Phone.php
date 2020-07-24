<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Keestash\Core\Service\Validation\Rule;

use doganoo\PHPUtil\Log\FileLogger;
use Exception;
use Keestash;
use KSP\Core\Service\Core\Locale\ILocaleService;
use Laminas\Validator\AbstractValidator;
use libphonenumber\PhoneNumberUtil;

class Phone extends AbstractValidator {

    private const PHONE = "phone";
    protected $messageTemplates = [
        Phone::PHONE => "%phone% is invalid"
    ];
    /** @var PhoneNumberUtil */
    private $phoneNumberUtl;
    /** @var ILocaleService */
    private $localeService;

    public function __construct() {
        parent::__construct(null);

        $this->phoneNumberUtl = Keestash::getServer()->query(PhoneNumberUtil::class);
        $this->localeService  = Keestash::getServer()->query(ILocaleService::class);
    }

    public function isValid($value) {
        $this->setValue($value);

        try {
            $validNumber = $this->phoneNumberUtl->isValidNumber(
                $this->phoneNumberUtl->parse(
                    $value
                    , $this->localeService->getLocale()
                )
            );

            if (false === $validNumber) {
                $this->error(Phone::PHONE);
                return false;
            }

        } catch (Exception $exception) {
            FileLogger::error(
                $exception->getMessage() . " " .
                $exception->getTraceAsString()
            );
            $this->error(Phone::PHONE);
            return false;
        }

        return true;
    }

}
