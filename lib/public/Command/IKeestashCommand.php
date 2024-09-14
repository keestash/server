<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSP\Command;

use Symfony\Component\Console\Command\Command;

interface IKeestashCommand {

    public const int    RETURN_CODE_NOT_RAN_SUCCESSFUL = Command::FAILURE;
    public const int    RETURN_CODE_INVALID_ARGUMENT   = Command::INVALID;
    public const int    RETURN_CODE_RAN_SUCCESSFUL     = Command::SUCCESS;
    public const string OPTION_NAME_SILENT             = 'silent';

}
