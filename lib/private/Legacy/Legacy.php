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

namespace Keestash\Legacy;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;

class Legacy {

    public function getVendor(): HashTable {
        $table = new HashTable();
        $table->put("name", "Doğan Uçar");
        $table->put("web", "https://www.dogan-ucar.de");
        $table->put("email", "dogan@dogan-ucar.de");
        $table->put("vendor", "Dogan Ucar");
        return $table;
    }

    public function getApplication(): HashTable {
        $table = new HashTable();
        $table->put("name", "Keestash");
        $table->put("name_internal", "keestash");
        $table->put("slogan", "Open Source Password Safe");
        $dateTime = new DateTime();
        $dateTime->setDate(2018, 11, 01);
        $table->put("start_date", $dateTime);
        $table->put("email", "dogan@dogan-ucar.de");
        $table->put("web", "https://www.dogan-ucar.de");
        $table->put("facebookPage", "https://www.facebook.com/DoganUcar2");
        $table->put("twitterPage", "https://twitter.com/DoganUcar9");
        $table->put("linkedInPage", "https://www.linkedin.com/in/dogan-ucar/");
        return $table;
    }

}