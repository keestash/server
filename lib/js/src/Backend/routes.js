export default {
    getCredential: function (credentialId) {
        return Keestash.Main.getApiHost() + "/password_manager/credential/get/" + credentialId + "/";
    },
    getLogin: function (id) {
        return Keestash.Main.getApiHost() + "/password_manager/password/get/" + id + "/";
    },
    getPasswordManagerLoginDelete: function () {
        return Keestash.Main.getApiHost() + "/password_manager/password/delete/";
    }
    , getPasswordManagerShare: function () {
        return Keestash.Main.getApiHost() + "/password_manager/share/";
    }
    , getPasswordManagerSharePublicly: function () {
        return Keestash.Main.getApiHost() + "/password_manager/share/public/";
    }
    , getPasswordManagerCreate: function () {
        return Keestash.Main.getApiHost() + "/password_manager/node/credential/create/";
    }
    , getWeightDataCreate: function () {
        return Keestash.Main.getApiHost() + "/weight_data/create/";
    }
    , getCalorieTrackerRemove: function () {
        return Keestash.Main.getApiHost() + "/calorie_tracker/remove/";
    }
    , getCalorieTrackerCreate: function () {
        return Keestash.Main.getApiHost() + "/calorie_tracker/add/";
    }
    , getPasswordManagerFolderCreate: function () {
        return Keestash.Main.getApiHost() + "/password_manager/node/create/";
    }
    , getPasswordManagerImport: function () {
        return Keestash.Main.getApiHost() + "/password_manager/import/";
    }
    , getPasswordManagerGroupDelete: function () {
        return Keestash.Main.getApiHost() + "/password_manager/group/delete/";
    }
    , getPasswordManagerGroupGet: function () {
        return Keestash.Main.getApiHost() + "/password_manager/group/get/";
    }
    , getNode: function (id) {
        return Keestash.Main.getApiHost() + "/password_manager/node/get/" + id + "/";
    }
    , getAppsUpdate() {
        return Keestash.Main.getApiHost() + "/apps/update/";
    }
    , getSeenUsers: function () {
        return Keestash.Main.getApiHost() + "/users/all/seen/";
    }
    , getProfilePictureBase: function () {
        return Keestash.Main.getApiHost() + "/users/profile_pictures/";
    }
}