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
import {Host} from "../../../../../lib/js/src/Backend/Host";

const PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT = "/password_manager/public_share/decrypt/{hash}/";
const PASSWORD_MANAGER_GENERATE_PASSWORD = "/password_manager/generate_password/{length}/{upperCase}/{lowerCase}/{digit}/{specialChars}/";
const PASSWORD_MANAGER_ADD_COMMENT = "/password_manager/comment/add/";
export const PASSWORD_MANAGER_GET_COMMENT = "/password_manager/comment/get/{nodeId}/";
const USER_PROFILE_PICTURE = "/users/profile_pictures/{token}/{userHash}/{targetId}/";
const PASSWORD_MANAGER_SHAREABLE_USERS = "/password_manager/users/shareable/{nodeId}/";
const PASSWORD_MANAGER_ATTACHMENTS_REMOVE = "/password_manager/attachments/remove/";
const PASSWORD_MANAGER_ATTACHMENTS_GET = "/password_manager/attachments/get/{nodeId}/";
export const PASSWORD_MANAGER_USERS_UPDATE = "/password_manager/users/update/";
const PASSWORD_MANAGER_SHARE_REMOVE = "/password_manager/share/remove/";
export const PASSWORD_MANAGER_COMMENT_REMOVE = "/password_manager/comment/remove/";
export const PASSWORD_MANAGER_NODE_DELETE = "/password_manager/node/delete/";
const PASSWORD_MANAGER_NODE_UPDATE_AVATAR = "/password_manager/node/update/avatar/";
const PASSWORD_MANAGER_NODE_GET_AVATAR = "/password_manager/node/get/avatar/{nodeId}/";
const PASSWORD_MANAGER_ATTACHMENTS_VIEW = "/password_manager/attachments/view/{fileId}/";
const PASSWORD_MANAGER_SHARE = "/password_manager/share/";
const PASSWORD_MANAGER_NODE_CREATE = "/password_manager/node/create/";
const ORGANIZATIONS_ALL = "/organizations/all/";
const PASSWORD_MANAGER_ORGANIZATION_ADD_NODE = "/password_manager/organization/node/add/";

export class Routes {

    constructor() {
        this.host = new Host();
    }

    getPublicShareDecrypt(hash) {
        let route = PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT;
        route = route.replace("{hash}", hash);
        return this.host.getApiHost() + route;
    }

    getPasswordManagerUsersUpdate() {
        return this.host.getApiHost() + PASSWORD_MANAGER_USERS_UPDATE;
    }

    getGeneratePassword(length, upperCase, lowerCase, digit, specialCharacter) {
        let route = PASSWORD_MANAGER_GENERATE_PASSWORD;
        route = route.replace("{length}", length);
        route = route.replace("{upperCase}", upperCase);
        route = route.replace("{lowerCase}", lowerCase);
        route = route.replace("{digit}", digit);
        route = route.replace("{specialChars}", specialCharacter);
        return this.host.getApiHost() + route;
    }

    getAddComment() {
        return this.host.getApiHost() + PASSWORD_MANAGER_ADD_COMMENT;
    }

    getComments(nodeId) {
        let route = PASSWORD_MANAGER_GET_COMMENT;
        route = route.replace("{nodeId}", nodeId);
        return this.host.getApiHost() + route;
    }

    getAttachments(nodeId) {
        let route = PASSWORD_MANAGER_ATTACHMENTS_GET;
        route = route.replace("{nodeId}", nodeId);
        return this.host.getApiHost() + route;
    }

    getUserProfilePicture(token, userHash, targetId) {
        let route = USER_PROFILE_PICTURE;
        route = route.replace("{token}", token);
        route = route.replace("{userHash}", userHash);
        route = route.replace("{targetId}", targetId);
        return this.host.getApiHost() + route;
    }

    getShareableUsers(nodeId) {
        let route = PASSWORD_MANAGER_SHAREABLE_USERS;
        route = route.replace("{nodeId}", nodeId);
        return this.host.getApiHost() + route;
    }

    getPasswordManagerSharePublicly() {
        return this.host.getApiHost() + "/password_manager/share/public/";
    }

    getShare() {
        return this.host.getApiHost() + PASSWORD_MANAGER_SHARE;
    }

    getNode(id) {
        return this.host.getApiHost() + "/password_manager/node/get/" + id + "/";
    }

    putAttachments(token, userHash) {
        return this.host.getApiHost() + "/password_manager/attachments/add/" + token + "/" + userHash + "/";
    }

    getCredential(credentialId) {
        return this.host.getApiHost() + "/password_manager/credential/get/" + credentialId + "/";
    }

    getPasswordManagerFolderCreate() {
        return this.host.getApiHost() + PASSWORD_MANAGER_NODE_CREATE;
    }

    getPasswordManagerCreate() {
        return this.host.getApiHost() + "/password_manager/node/credential/create/";
    }

    getPasswordManagerMoveNode() {
        return this.host.getApiHost() + "/password_manager/node/move/";
    }

    getNodeDelete() {
        return this.host.getApiHost() + PASSWORD_MANAGER_NODE_DELETE;
    }

    getPasswordManagerShareeRemove() {
        return this.host.getApiHost() + PASSWORD_MANAGER_SHARE_REMOVE;
    }

    getPasswordManagerNodeUpdateAvatar() {
        return this.host.getApiHost() + PASSWORD_MANAGER_NODE_UPDATE_AVATAR;
    }

    getPasswordManagerCommentRemove() {
        return this.host.getApiHost() + PASSWORD_MANAGER_COMMENT_REMOVE;
    }

    getPasswordManagerAttachmentRemove() {
        return this.host.getApiHost() + PASSWORD_MANAGER_ATTACHMENTS_REMOVE;
    }

    getNodeAvatar(nodeId) {
        let route = PASSWORD_MANAGER_NODE_GET_AVATAR;
        route = route.replace("{nodeId}", nodeId);
        return this.host.getApiHost() + route;
    }

    getPublicShareLink(hash) {
        return this.host.getHost() + "/s/" + hash + "/";
    }

    getNodeAttachment(fileId) {
        let route = PASSWORD_MANAGER_ATTACHMENTS_VIEW;
        route = route.replace("{fileId}", fileId);
        return this.host.getHost() + route;
    }

    getNodeAttachmentRemove() {
        return this.host.getApiHost() + PASSWORD_MANAGER_ATTACHMENTS_REMOVE;
    }

    getOrganizations() {
        return this.host.getApiHost() + ORGANIZATIONS_ALL;
    }

    getOrganizationsAddNode() {
        return this.host.getApiHost() + PASSWORD_MANAGER_ORGANIZATION_ADD_NODE;
    }
}
