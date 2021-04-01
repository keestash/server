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

export class ContextMenu {
    menuBuilder(data) {
        // <li className="divider"></li> can be used to divide the menu
        let menu = '<ul class="dropdown-menu" role="menu" style="display:none; padding:0;">';
        for (let i = 0; i < data.length; i++) {
            const object = data[i];
            menu = menu + '<li class="dropdown-item" style="padding: 0;margin-left: 10px; width: 0"><a href="#" data-value="' + object.name + '" id="' + object.id + '">' + object.name + '</a></li>';
        }
        menu = menu + '</ul>';
        return menu;
    }

    getMenuPosition(mouse, direction, scrollDir, element) {
        const win = $(window)[direction](),
            scroll = $(window)[scrollDir](),
            menu = element[direction]();
        let position = mouse + scroll;

        // opening menu would pass the side of the page
        if (mouse + menu > win && menu < mouse)
            position -= menu;

        return position;
    }

    handler(settings, event, onClose) {
        const xy = $(this.menuBuilder((settings.data)));
        $("body").append(xy);

        //open menu
        const menu = xy
            .show()
            .css({
                position: "absolute",
                left: this.getMenuPosition(
                    event.clientX
                    , 'width'
                    , 'scrollLeft'
                    , xy
                ),
                top: this.getMenuPosition(
                    event.clientY
                    , 'height'
                    , 'scrollTop'
                    , xy
                )
            })
            .off('one')
            .one('click', "li", (e) => {
                e.preventDefault();

                const target = $(e.target);

                const id = $(e.target).attr("id");
                const value = $(e.target).data("value");

                settings.menuSelected(id, value);
                menu.hide();
            });

        $('body').click(() => {
            xy.hide();
            xy.remove();
            menu.hide();
            onClose();
        });

        return xy;
    }

    registerContextMenu(element, settings, onClose) {
        const _this = this;
        element
            .off("one")
            .one("click", (event) => {
                event.preventDefault();
                event.stopPropagation();
                _this.handler(settings, event, onClose);
            })
    }

    registerRightClick(element, settings, onClose) {
        const _this = this;
        let xy = null;
        element.contextmenu((event) => {
            xy = _this.handler(settings, event, onClose);
            return false;
        });
    }
}