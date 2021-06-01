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
const PASSWORD_MANAGER_GET_COMMENT = "/password_manager/comment/get/{nodeId}/";
const USER_PROFILE_PICTURE = "/users/profile_pictures/{userId}/";
const PASSWORD_MANAGER_SHAREABLE_USERS = "/password_manager/users/shareable/{nodeId}/{query}/";
const PASSWORD_MANAGER_ATTACHMENTS_REMOVE = "/password_manager/attachments/remove/";
const PASSWORD_MANAGER_ATTACHMENTS_GET = "/password_manager/attachments/get/{nodeId}/";
const PASSWORD_MANAGER_USERS_UPDATE = "/password_manager/users/update/";
const PASSWORD_MANAGER_SHARE_REMOVE = "/password_manager/share/remove/";
const PASSWORD_MANAGER_COMMENT_REMOVE = "/password_manager/comment/remove/";
const PASSWORD_MANAGER_NODE_DELETE = "/password_manager/node/delete/";
const PASSWORD_MANAGER_NODE_UPDATE_AVATAR = "/password_manager/node/update/avatar/";
const PASSWORD_MANAGER_NODE_GET_AVATAR = "/password_manager/node/get/avatar/{nodeId}/";
const PASSWORD_MANAGER_ATTACHMENTS_VIEW = "/password_manager/attachments/view/{fileId}/";
const PASSWORD_MANAGER_SHARE = "/password_manager/share/";
const PASSWORD_MANAGER_NODE_CREATE = "/password_manager/node/create/";
const ORGANIZATIONS_ALL = "/organizations/all/";
const PASSWORD_MANAGER_ORGANIZATION_ADD_NODE = "/password_manager/organization/node/add/";
const PASSWORD_MANAGER_THUMBNAIL_EXTENSION = "/thumbnail/{extension}/";

const host = new Host();

export const ROUTES = {

    getNode: (id) => {
        return host.getApiHost() + "/password_manager/node/get/" + id + "/";
    },

    getPublicShareDecrypt(hash) {
        let route = PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT;
        route = route.replace("{hash}", hash);
        return host.getApiHost() + route;
    },

    getPasswordManagerUsersUpdate() {
        return host.getApiHost() + PASSWORD_MANAGER_USERS_UPDATE;
    },

    getGeneratePassword(length, upperCase, lowerCase, digit, specialCharacter) {
        let route = PASSWORD_MANAGER_GENERATE_PASSWORD;
        route = route.replace("{length}", length);
        route = route.replace("{upperCase}", upperCase);
        route = route.replace("{lowerCase}", lowerCase);
        route = route.replace("{digit}", digit);
        route = route.replace("{specialChars}", specialCharacter);
        return host.getApiHost() + route;
    },

    getAddComment() {
        return host.getApiHost() + PASSWORD_MANAGER_ADD_COMMENT;
    },

    getComments(nodeId) {
        let route = PASSWORD_MANAGER_GET_COMMENT;
        route = route.replace("{nodeId}", nodeId);
        return host.getApiHost() + route;
    },

    getAttachments(nodeId) {
        let route = PASSWORD_MANAGER_ATTACHMENTS_GET;
        route = route.replace("{nodeId}", nodeId);
        return host.getApiHost() + route;
    },

    getUserProfilePicture(userId) {
        let route = USER_PROFILE_PICTURE;
        route = route.replace("{userId}", userId);
        return host.getApiHost() + route;
    },

    getAssetUrl(jsonWebToken) {
        return host.getAssetUrl() + '?token=' + jsonWebToken;
    },

    getShareableUsers(nodeId, query) {
        let route = PASSWORD_MANAGER_SHAREABLE_USERS;
        route = route.replace("{nodeId}", nodeId);
        route = route.replace("{query}", query);
        return host.getApiHost() + route;
    },

    getPasswordManagerSharePublicly() {
        return host.getApiHost() + "/password_manager/share/public/";
    },

    getShare() {
        return host.getApiHost() + PASSWORD_MANAGER_SHARE;
    },

    putAttachments() {
        return host.getApiHost() + "/password_manager/attachments/add/";
    },

    getCredential(credentialId) {
        return host.getApiHost() + "/password_manager/credential/get/" + credentialId + "/";
    },

    getPasswordManagerFolderCreate() {
        return host.getApiHost() + PASSWORD_MANAGER_NODE_CREATE;
    },

    getPasswordManagerCreate() {
        return host.getApiHost() + "/password_manager/node/credential/create/";
    },
    //
    // getPasswordManagerMoveNode() {
    //     return host.getApiHost() + "/password_manager/node/move/";
    // }
    //
    // getNodeDelete() {
    //     return host.getApiHost() + PASSWORD_MANAGER_NODE_DELETE;
    // }
    //
    getPasswordManagerShareeRemove() {
        return host.getApiHost() + PASSWORD_MANAGER_SHARE_REMOVE;
    },
    //
    // getPasswordManagerNodeUpdateAvatar() {
    //     return host.getApiHost() + PASSWORD_MANAGER_NODE_UPDATE_AVATAR;
    // }
    //
    getPasswordManagerCommentRemove() {
        return host.getApiHost() + PASSWORD_MANAGER_COMMENT_REMOVE;
    },

    getPasswordManagerAttachmentRemove() {
        return host.getApiHost() + PASSWORD_MANAGER_ATTACHMENTS_REMOVE;
    },
    //
    // getNodeAvatar(nodeId) {
    //     let route = PASSWORD_MANAGER_NODE_GET_AVATAR;
    //     route = route.replace("{nodeId}", nodeId);
    //     return host.getApiHost() + route;
    // }
    //
    getPublicShareLink(hash) {
        return host.getHost() + "/s/" + hash + "/";
    },

    getNodeAttachment(fileId) {
        let route = PASSWORD_MANAGER_ATTACHMENTS_VIEW;
        route = route.replace("{fileId}", fileId);
        return host.getHost() + route;
    },
    //
    // getNodeAttachmentRemove() {
    //     return host.getApiHost() + PASSWORD_MANAGER_ATTACHMENTS_REMOVE;
    // }
    //
    // getOrganizations() {
    //     return host.getApiHost() + ORGANIZATIONS_ALL;
    // }
    //
    // getOrganizationsAddNode() {
    //     return host.getApiHost() + PASSWORD_MANAGER_ORGANIZATION_ADD_NODE;
    // }
    getThumbNailByExtension(extension) {
        let route = PASSWORD_MANAGER_THUMBNAIL_EXTENSION;
        route = route.replace("{extension}", extension);
        return host.getApiHost() + route;
    }
}
