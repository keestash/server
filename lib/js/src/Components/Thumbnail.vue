<template>
  <div class="d-flex flex-column">
    <Skeleton
        v-if="this.state.value === this.state.states.STATE_LOADING"
        height="40px"
        width="40px"
    />
    <img
        :src="this.source"
        class="thumbnail left border-0"
        style="height: 40px !important; width: 40px !important;"
        v-else
        alt="profile"
    >
  </div>
</template>

<script>

import {Skeleton} from 'vue-loading-skeleton';
import {AXIOS, StartUp, TEMPORARY_STORAGE} from "../StartUp";
import {Container} from "../DI/Container";

const STATE_LOADING = 1;
const STATE_LOADED = 2;

export default {
  name: "Thumbnail",
  components: {Skeleton},
  props: {
    source: '',
    skipCache: false
  },
  data() {
    return {
      data: null,
      state: {
        value: STATE_LOADING,
        states: {
          STATE_LOADING: STATE_LOADING,
          STATE_LOADED: STATE_LOADED
        }
      },
      cache: {}
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    const container = startUp.getContainer();
    const axios = container.query(AXIOS);
    const tempCache = container.query(TEMPORARY_STORAGE);
    // const cachedData = this.getCachedData(container);

    // if (cachedData !== null) {
    //   this.data = cachedData;
    //   this.state.value = STATE_LOADED;
    //   return;
    // }

    axios.request(
        this.source
    ).then((response) => {
      // this.data = 'data:' + response.headers['content-type'] + ';base64,' + response.data;
      this.data = response.data;
      if (false === this.skipCache) {
        tempCache.set(
            this.source
            , this.data
        )
      }
      this.state.value = STATE_LOADED;
    })
  },
  methods: {
    getCachedData(container) {
      if (true === this.skipCache) return null;
      const tempCache = container.query(TEMPORARY_STORAGE);
      const cachedData = tempCache.get(this.source);
      if (cachedData === null || typeof cachedData === 'undefined') return null;
      return cachedData;
    }
  }
}
</script>

<style scoped>

</style>