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
import {CredentialParser} from "../../password_manager/Node/Credential/Parser/CredentialParser";
import {FolderParser} from "../../password_manager/Node/Folder/Parser/FolderParser";
import {
    APP_STORAGE,
    ARRAYLIST_HELPER,
    AXIOS, BREADCRUMB_SERVICE,
    CONFIRMATION_MODAL,
    DATE_TIME_SERVICE,
    LONG_MODAL,
    MINI_MODAL,
    REQUEST,
    STRING_LOADER,
    TEMPLATE_LOADER,
    TEMPLATE_PARSER,
    TEMPORARY_STORAGE,
    URL_SERVICE
} from "../../../../../../lib/js/src/StartUp";
import {Routes} from "../../Public/Routes";
import {CredentialListener} from "../../password_manager/Node/Credential/Listener/CredentialListener";
import {Node} from "../../password_manager/Node/Node";
import {Credential} from "../../password_manager/ActionBar/Credential/Credential";
import {Folder} from "../../password_manager/ActionBar/Folder/Folder";
import {SearchPasswordList} from "../../password_manager/Search/SearchPasswordList";
import {Share} from "../../password_manager/Node/Credential/Tab/Tab/Share/Share";
import {PwGenerator} from "../../password_manager/Node/Credential/Tab/Tab/PwGenerator";
import {Comment} from "../../password_manager/Node/Credential/Tab/Tab/Comment";
import {Attachments} from "../../password_manager/Node/Credential/Tab/Tab/Attachments";
import {RegularShare} from "../../password_manager/Node/Credential/Tab/Tab/Share/RegularShare/RegularShare";
import {PublicShare} from "../../password_manager/Node/Credential/Tab/Tab/Share/PublicShare/PublicShare";
import {ShareService} from "../../password_manager/Service/Share/ShareService";
import {FolderListener} from "../../password_manager/Node/Folder/Listener/FolderListener";

export const CREDENTIAL_PARSER = "credentialparser.parser.credential.node.password_manager";
export const FOLDER_PARSER = "folderparser.parser.folder.node.password_manager";
export const PWM_ROUTES = "routes.pwm";
export const FOLDER_LISTENER = "listener.folder";
export const CREDENTIAL_LISTENER = "listener.credential";
export const PWM_NODE = "node.pwm";
export const ACTION_BAR_CREDENTIAL = "credential.bar.action";
export const ACTION_BAR_FOLDER = "folder.bar.action";
export const PASSWORD_LIST_SEARCH = "search.list.password";
export const PWM_TABS = "tabs.pwm";
export const PWM_TABS_SHARE = "share.tabs.pwm";
export const PWM_TABS_SHARE_REGULAR_SHARE = "share.regular.share.tabs.pwm";
export const PWM_TABS_SHARE_PUBLIC_SHARE = "share.public.share.tabs.pwm";
export const PWM_TABS_PW_GENERATOR = "generator.pw.tabs.pwm";
export const PWM_TABS_COMMENT = "comment.tabs.pwm";
export const SHARE_SERVICE = "service.share";
export const PWM_TABS_ATTACHMENT = "attachment.tabs.pwm";

export class Container {
    register() {
        const diContainer = Keestash.Main.getContainer();

        diContainer.register(
            CREDENTIAL_PARSER
            , () => {
                return new CredentialParser();
            }
        );

        diContainer.register(
            FOLDER_PARSER
            , (container) => {
                return new FolderParser(
                    container.query(TEMPLATE_PARSER)
                );
            }
        );

        diContainer.register(
            PWM_ROUTES
            , () => {
                return new Routes();
            }
        );

        diContainer.register(
            CREDENTIAL_LISTENER
            , (container) => {
                return new CredentialListener(
                    container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                    , container.query(APP_STORAGE)
                    , container.query(LONG_MODAL)
                    , container.query(TEMPLATE_LOADER)
                    , container.query(STRING_LOADER)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(CONFIRMATION_MODAL)
                    , container.query(SHARE_SERVICE)
                    , container.query(AXIOS)
                    , container.query(ARRAYLIST_HELPER)
                    , container.query(URL_SERVICE)
                );
            }
        );

        diContainer.register(
            PWM_NODE
            , (container) => {
                return new Node(
                    container.query(PWM_ROUTES)
                    , container.query(APP_STORAGE)
                    , container.query(REQUEST)
                    , container.query(LONG_MODAL)
                    , container.query(CREDENTIAL_PARSER)
                    , container.query(FOLDER_PARSER)
                    , container.query(TEMPLATE_LOADER)
                    , container.query(TEMPORARY_STORAGE)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(STRING_LOADER)
                    , container.query(DATE_TIME_SERVICE)
                    , container.query(CONFIRMATION_MODAL)
                    , container.query(SHARE_SERVICE)
                    , container.query(AXIOS)
                    , container.query(ARRAYLIST_HELPER)
                    , container.query(FOLDER_LISTENER)
                    , container.query(CREDENTIAL_LISTENER)
                    , container.query(BREADCRUMB_SERVICE)
                );
            }
        );

        diContainer.register(
            FOLDER_LISTENER
            , () => {
                return new FolderListener()
            }
        );
        diContainer.register(
            ACTION_BAR_CREDENTIAL
            , (container) => {
                return new Credential(
                    container.query(TEMPLATE_LOADER)
                    , container.query(LONG_MODAL)
                    , container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                    , container.query(PWM_NODE)
                    , container.query(STRING_LOADER)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(TEMPORARY_STORAGE)
                )
            }
        );

        diContainer.register(
            ACTION_BAR_FOLDER
            , (container) => {
                return new Folder(
                    container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                    , container.query(PWM_NODE)
                    , container.query(LONG_MODAL)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(TEMPORARY_STORAGE)
                )
            }
        );

        diContainer.register(
            PASSWORD_LIST_SEARCH
            , () => {
                return new SearchPasswordList();
            }
        );

        diContainer.register(
            PWM_TABS_SHARE
            , (container) => {
                return new Share(
                    container.query(APP_STORAGE)
                    , container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                    , container.query(CONFIRMATION_MODAL)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(SHARE_SERVICE)
                    , container.query(AXIOS)
                    , container.query(ARRAYLIST_HELPER)
                );
            }
        );

        diContainer.register(
            PWM_TABS_PW_GENERATOR
            , (container) => {
                return new PwGenerator(
                    container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                );
            }
        );

        diContainer.register(
            PWM_TABS_COMMENT
            , (container) => {
                return new Comment(
                    container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                    , container.query(APP_STORAGE)
                    , container.query(ARRAYLIST_HELPER)
                    , container.query(TEMPLATE_PARSER)
                );
            }
        );

        diContainer.register(
            PWM_TABS_ATTACHMENT
            , (container) => {
                return new Attachments(
                    container.query(APP_STORAGE)
                    , container.query(REQUEST)
                    , container.query(PWM_ROUTES)
                    , container.query(ARRAYLIST_HELPER)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(MINI_MODAL)
                );
            }
        );

        diContainer.register(
            PWM_TABS_SHARE_REGULAR_SHARE
            , (container) => {
                return new RegularShare(
                    container.query(AXIOS)
                    , container.query(PWM_ROUTES)
                    , container.query(ARRAYLIST_HELPER)
                    , container.query(TEMPLATE_PARSER)
                    , container.query(SHARE_SERVICE)
                    , container.query(APP_STORAGE)
                    , container.query(MINI_MODAL)
                    , container.query(REQUEST)
                )
            }
        );

        diContainer.register(
            PWM_TABS_SHARE_PUBLIC_SHARE
            , (container) => {
                return new PublicShare(
                    container.query(CONFIRMATION_MODAL)
                    , container.query(AXIOS)
                    , container.query(PWM_ROUTES)
                )
            }
        );

        diContainer.register(
            SHARE_SERVICE
            , (container) => {
                return new ShareService(
                    container.query(AXIOS)
                )
            }
        );

    }
}
