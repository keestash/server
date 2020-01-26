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

/**
 * @deprecated
 */
export default {
    getLogin: function (id) {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/password/get/" + id + "/";
    },
    getPasswordManagerLoginDelete: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/password/delete/";
    }
    , getPasswordManagerShare: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/share/";
    }
    , getPasswordManagerSharePublicly: function () {
        return Keestash.Main.getApiHost() + "/password_manager/share/public/";
    }
    , getWeightDataCreate: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/weight_data/create/";
    }
    , getCalorieTrackerRemove: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/calorie_tracker/remove/";
    }
    , getCalorieTrackerCreate: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/calorie_tracker/add/";
    }
    , getPasswordManagerImport: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/import/";
    }
    , getPasswordManagerGroupDelete: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/group/delete/";
    }
    , getPasswordManagerGroupGet: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/group/get/";
    }
    , getNode: function (id) {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/password_manager/node/get/" + id + "/";
    }
    , getAppsUpdate() {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/apps/update/";
    }
    , getSeenUsers: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/users/all/seen/";
    }
    , getProfilePictureBase: function () {
        console.log(__filename + " i could be deprecated" + this.caller.name);
        return Keestash.Main.getApiHost() + "/users/profile_pictures/";
    }
}