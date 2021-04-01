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
import 'corejs-typeahead'
import {PWM_TABS_SHARE_PUBLIC_SHARE, PWM_TABS_SHARE_REGULAR_SHARE} from "../../../../../../Common/Container/Container";

export const EVENT_NAME_PASSWORD_SHARE_PUBLICLY = "publicly.share.password.name.event";

export class Share {

    constructor() {
        this.container = Keestash.Main.getContainer();
    }

    init(node, strings, templates) {
        this.initRegularShare(node, strings, templates);
        this.initPublicShare(node, strings);
    }

    initRegularShare(node, strings, templates) {
        const regularShare = this.container.query(PWM_TABS_SHARE_REGULAR_SHARE);
        regularShare.init(node, strings, templates);
    }

    initPublicShare(node, strings) {
        const publicShare = this.container.query(PWM_TABS_SHARE_PUBLIC_SHARE);
        publicShare.init(node, strings);
    }

}
