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
import {
    PWM_TABS_ATTACHMENT,
    PWM_TABS_COMMENT,
    PWM_TABS_PW_GENERATOR,
    PWM_TABS_SHARE
} from "../../../../Common/Container/Container";

export const TAB_COMMENT = "comment";
export const TAB_PW_GENERATOR = "pw-generator";
export const TAB_SHARES = "shares";
export const TAB_ATTACHMENTS = "attachments";

export class Tab {

    init(tabName, node, strings, templates) {
        const tab = this.getTab(tabName);
        tab.init(node, strings, templates);
    }

    getTab(tabName) {
        const diContainer = Keestash.Main.getContainer();
        switch (tabName) {
            case TAB_SHARES:
                return diContainer.query(PWM_TABS_SHARE);
            case TAB_PW_GENERATOR:
                return diContainer.query(PWM_TABS_PW_GENERATOR);
            case TAB_COMMENT:
                return diContainer.query(PWM_TABS_COMMENT);
            case TAB_ATTACHMENTS:
                return diContainer.query(PWM_TABS_ATTACHMENT);
            default:
                return null;
        }
    }
}
