import {APP_STORAGE, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ShareService} from "../Service/ShareService";
import {SystemService} from "../Service/SystemService";

/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

const startUp = new StartUp(
    new Container()
);
startUp.setUp();

export const SHARE_SERVICE = 'service.share';
export const SYSTEM_SERVICE = 'service.system';

class PWMContainer {
    constructor(container) {
        this.container = container;
    }

    setUp() {
        this.container.register(
            SHARE_SERVICE
            , (container) => {
                return new ShareService(
                    container.query(APP_STORAGE)
                );
            }
        )

        this.container.register(
            SYSTEM_SERVICE
            , (container) => {
                return new SystemService();
            }
        )
    }

    getContainer() {
        return this.container;
    }
}

export default new PWMContainer(
    startUp.getContainer()
);