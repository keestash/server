<template>
  <div class="results mt-3 rounded border tab_result_box">
    <NoDataFound
        :visible="content.length === 0 && this.state.value === this.state.states.STATE_LOADED"
        :text="noContentText"
        type="user"
    ></NoDataFound>

    <template v-if="content.length > 0 && this.state.value === this.state.states.STATE_LOADED">
      <ul class="list-group list-group-flush">

        <li v-for="data in content" :key="data.id"
            class="list-group-item m-0 ps-0 pe-0 pt-1 pb-1">
          <div class="container">
            <div class="row justify-content-between">
              <div class="col-sm-4">
                <div class="row">
                  <div class="col-2">
                    <Thumbnail
                        :source="getAssetUrl(data.jwt)"
                    ></Thumbnail>
                  </div>
                  <div class="col">
                    <div class="container">
                      <div class="row cropped">
                        {{ data.name }}
                      </div>
                      <div class="row">
                        <small> {{ $t('credential.detail.share.sharedDateTimeLabel') }}
                          {{
                            formatDate(data.user.create_ts.date)
                          }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-4 align-self-center">
                <div class="row justify-content-end pe-1">
                  <div class="col-1 me-2" @click="remove(data)">
                    <i class="fas fa-times remove"></i>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </li>
      </ul>
    </template>
    <Skeleton :count=9 height="25px" v-else/>
  </div>

</template>

<script>
import NoDataFound from "./NoDataFound";
import Thumbnail from "./Thumbnail";
import {AXIOS, DATE_TIME_SERVICE, StartUp} from "../StartUp";
import {Container} from "../DI/Container";
import {Skeleton} from 'vue-loading-skeleton';

const STATE_LOADING = 1;
const STATE_LOADED = 2;
export default {
  name: "ContentList",
  components: {NoDataFound, Thumbnail, Skeleton},
  props: {
    content: null,
    noContentText: ''
  },
  data() {
    return {
      state: {
        value: STATE_LOADING,
        states: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED: STATE_LOADED
        }
      },
      services: {
        container: null,
        dateTimeService: null,
        axios: null
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.services.container = startUp.getContainer();
    this.services.axios = this.services.container.query(AXIOS);
    this.services.dateTimeService = this.services.container.query(DATE_TIME_SERVICE);
    this.state.value = this.state.states.STATE_LOADED;
  },
  methods: {
    getAssetUrl(jwt) {

    },
    remove(data) {
      this.$emit('data-removed', data);
    },
    formatDate(date) {
      return this.dateTimeService.format(date);
    }
  }
}
</script>

<style scoped>

</style>