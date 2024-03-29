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
import _ from "lodash";

export class DropDownButton {
    constructor(id, targetId) {
        this.id = id;
        this.targetId = targetId;
        this.opened = null;
        this.registered = false;
    }

    listen(eventName = null) {
        const button = document.getElementById(this.id);

        if (button === null || typeof button === 'undefined') return;
        const listener = (event) => {

            event.stopImmediatePropagation();
            event.stopPropagation();
            event.preventDefault();

            const menu = document.getElementById(this.targetId);

            if (!this.opened) {
                this.opened = menu
                this.opened.classList.toggle('show');
            } else if (menu === this.opened) {
                menu.classList.toggle('show')
                this.opened = null
            } else {
                this.opened.classList.toggle('show')
                this.opened = menu
                this.opened.classList.toggle('show')
            }

            if (eventName !== null) {

                for (let i = 0; i < menu.children.length; i++) {
                    menu.children[i].addEventListener(
                        'click'
                        , (event) => {
                            event.stopImmediatePropagation();
                            event.stopPropagation();
                            event.preventDefault();

                            const triggerEvent = () => {
                                const e = new CustomEvent(
                                    eventName
                                    , {
                                        detail: {
                                            target: event.target
                                        }
                                    }
                                );

                                document.dispatchEvent(e);
                            };
                            _.debounce(triggerEvent, 100)();
                        }
                    )
                }

            }

            this.isOutside();
        };
        button.removeEventListener('click', listener);
        button.addEventListener('click', listener);
    }

    isOutside() {

        if (true === this.registered) return;
        this.registered = true;

        const target = document.getElementById(this.targetId);
        const _this = this;

        document.addEventListener(
            'click'
            , (event) => {
                const isClickInside = target.contains(event.target);

                if (!isClickInside && target === _this.opened) {
                    target.classList.toggle('show')
                    _this.opened = null
                }

            }
        );
    }
}