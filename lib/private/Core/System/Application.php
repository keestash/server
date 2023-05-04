<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace Keestash\Core\System;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;

class Application {

    public function getMetaData(): HashTable {
        $table = new HashTable();
        $table->put("name", "Keestash");
        $table->put("name_internal", "keestash");
        $table->put("slogan", "Open Source Password Manager");
        $dateTime = new DateTime();
        $dateTime->setDate(2018, 11, 01);
        $table->put("start_date", $dateTime);
        $table->put("email", "info@ucar-solutions.de");
        $table->put("phone", "+49 (0) 69 175 111 52");
        $table->put("web", "https://www.keestash.com");
        $table->put("facebookPage", "https://www.facebook.com/keestash");
        $table->put("twitterPage", "https://www.twitter.com/keestash");
        $table->put("linkedInPage", "https://www.linkedin.com/company/keestash");
        return $table;
    }

}