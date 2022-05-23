<template>
  <div>
    <div class="container rounded bg-white mt-5 mb-5">
      <div class="row">
        <div class="col-md-3 border-right">
          <div class="d-flex flex-column align-items-center text-center p-3 py-5">
            <IsLoading class="img img-fluid rounded-circle mt-5 profile-loading-square" v-if="loading"></IsLoading>
            <img
                class="img img-fluid rounded-circle mt-5"
                :src="getProfileImage()"
                v-else
                @click="$refs.file.click()"
                :alt="this.user.name"
            >
            <form enctype="multipart/form-data" novalidate>
              <input
                  type="file"
                  ref="file"
                  style="display: none"
                  name="profile_image"
                  @change="uploadProfileImage($event.target.name, $event.target.files); fileCount = $event.target.files.length"
              >
            </form>
            <IsLoading class="profile-loading" v-if="loading"></IsLoading>
            <span class="font-weight-bold" v-else>{{ this.user.name }}</span>
            <IsLoading class="profile-loading" v-if="loading"></IsLoading>
            <span class="text-black-50" v-else>{{ this.user.email }}</span>
          </div>
        </div>
        <div class="col-md-5 border-right">
          <div class="p-3 py-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="text-right">{{ $t('settingsTitle') }}</h4>
            </div>
            <div class="row mt-2">
              <div class="col-md-6">
                <label class="labels">{{ $t('settings.firstName') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.settingsTitle')"
                    v-model="user.first_name"
                    v-else
                    @blur="updateUser"
                >
              </div>
              <div class="col-md-6">
                <label class="labels">{{ $t('settings.lastName') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.lastName')"
                    v-model="user.last_name"
                    v-else
                    @blur="updateUser"
                >
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-12">
                <label class="labels">{{ $t('settings.email') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.settingsTitle')"
                    v-model="user.email"
                    v-else
                    @blur="updateUser"
                >
              </div>
              <div class="col-md-12">
                <label class="labels">{{ $t('settings.phone') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.phone')"
                    v-model="user.phone"
                    v-else
                    @blur="updateUser"
                >
              </div>
              <div class="col-md-12">
                <label class="labels">{{ $t('settings.website') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.website')"
                    v-model="user.website"
                    v-else
                    @blur="updateUser"
                >
              </div>
              <div class="col-md-12">
                <label class="labels">{{ $t('settings.created') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.created')"
                    v-model="user.create_ts.date"
                    v-else
                    @blur="updateUser"
                >
              </div>
              <div class="col-md-12">
                <label class="labels">{{ $t('settings.locale') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>

                <select
                    class="form-control form-select"
                    :aria-label="$t('settings.locale')"
                    v-model="user.locale"
                    v-else
                    @change="updateUser"
                >
                  <option v-for="(locale, key) in this.locales" :value="key" :selected="key === user.locale">
                    {{ locale }}
                  </option>
                </select>

              </div>

              <div class="col-md-12">
                <label class="labels">{{ $t('settings.language') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>

                <select
                    class="form-control form-select"
                    :aria-label="$t('settings.language')"
                    v-model="user.language"
                    v-else
                    @change="updateUser"
                >
                  <option v-for="(language, key) in this.languages" :value="key" :selected="key === user.language">
                    {{ language }}
                  </option>
                </select>

              </div>

              <div class="col-md-12">
                <label class="labels">{{ $t('settings.hash') }}</label>
                <IsLoading class="profile-loading-long" v-if="loading"></IsLoading>
                <input
                    type="text"
                    class="form-control"
                    :placeholder="$t('settings.hash')"
                    v-model="user.hash"
                    v-else
                    disabled
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {APP_STORAGE, AXIOS, StartUp} from "../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../config/routes";
import IsLoading from "../../../../../lib/js/src/Components/IsLoading";
import _ from "lodash";
import {EVENT_NAME_GLOBAL_SEARCH} from "../../../../../lib/js/src/base";

export default {
  name: "App",
  components: {IsLoading},
  data() {
    return {
      loading: true,
      user: null,
      axios: null,
      appStorage: null,
      fileCount: 0,
      languages: {
        "": "",
        "en_US": "English (US)",
        "de_DE": "German (Germany)",
      },
      locales: {
        "": "",
        "US": "USA",
        "DE": "Germany",
      }
    }
  },
  methods: {
    uploadProfileImage(fieldName, fileList) {
      this.loading = true;
      let formData = new FormData();
      if (!fileList.length) {
        this.loading = false;
        return;
      }

      Array
          .from(Array(fileList.length).keys())
          .map(x => {
            formData.append(fieldName, fileList[x], fileList[x].name);
          });

      formData.append(
          'user_hash', this.user.hash
      )

      _.debounce(
          () => {
            this.axios.post(
                ROUTES.getUpdateProfileImage()
                , formData
            )
                .then(
                    (response) => {
                      this.loading = false;
                      this.user.jwt = response.data.jwt;
                      this.$refs.file.value = null;

                      document.dispatchEvent(
                          new CustomEvent(
                              "listenToProfileImageChange"
                              , {
                                detail:
                                    {jwt: this.getProfileImage()}
                              }
                          )
                      )
                    }
                )
                .catch(
                    (r) => {
                      console.error(r);
                      this.$refs.file.value = null;
                      this.loading = false;
                    }
                )
          }, 500
      )();
    },
    updateUser() {
      this.loading = true;
      _.debounce(
          () => {
            this.axios.post(
                ROUTES.getUpdateUser()
                , {
                  user: this.user
                }
            )
                .then(
                    (response) => {
                      this.loading = false;
                      const user = response.data.user;
                      this.user.first_name = user.first_name;
                      this.user.last_name = user.last_name;
                      this.user.email = user.email;
                      this.user.phone = user.phone;
                      this.user.website = user.website;
                    }
                )
          }, 500
      )();
    },
    getProfileImage() {
      if (null === this.user) return '';
      return ROUTES.getAssetUrl(this.user.jwt)
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);
    this.appStorage = this.container.query(APP_STORAGE);
    const userHash = this.appStorage.getUserHash();

    this.axios.request(
        ROUTES.getUsersGet(userHash)
    )
        .then(
            (r) => {
              this.loading = false;
              this.user = r.data.user;

              console.log(r.data.user)
            }
        )
        .catch(
            (r) => {
              this.loading = false;
              console.error(r)
            }
        )
  }

}
</script>

<style scoped lang="scss">
.profile-loading-square {
  height: 150px;
  width: 150px;
}

.profile-loading {
  height: 10px;
  width: 150px;
}

.profile-loading-long {
  height: 25px;
  width: 150px;
}
</style>