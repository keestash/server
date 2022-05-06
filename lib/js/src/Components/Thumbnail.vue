<template>
  <div class="container-fluid d-flex flex-column justify-content-center p-0">
    <div class="row">
      <div class="col">
        <div class="is-loading" v-if="this.state.value === this.state.states.STATE_LOADING"></div>
        <img
            :src="this.source"
            class="rounded float-left pwm-thumbnail"
            alt="profile"
            v-else
        >
      </div>
    </div>


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
      cache: {},
      container: {
        tempCache: null
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    const container = startUp.getContainer();
    const axios = container.query(AXIOS);
    this.container.tempCache = container.query(TEMPORARY_STORAGE);
    const cachedData = this.getCachedData();

    if (cachedData !== null) {
      this.data = cachedData;
      this.state.value = STATE_LOADED;
      return;
    }

    axios.request(
        this.source
    ).then((response) => {
      this.data = response.data;
      this.state.value = STATE_LOADED;
      if (true === this.skipCache) {
        return;
      }

      this.container.tempCache.remove(this.source);
      this.container.tempCache.set(
          this.source
          , this.data
      )

    })
  },
  methods: {
    getCachedData() {
      if (true === this.skipCache) return null;
      const tempCache = this.container.tempCache;
      const cachedData = tempCache.get(this.source);
      if (cachedData === null || typeof cachedData === 'undefined') return null;
      return cachedData;
    }
  }
}
</script>

<style scoped lang="scss">
.pwm-thumbnail {
  height: 35px;
  width: 35px;
}

.is-loading {
  background: #eee;
  background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
  border-radius: 2px;
  background-size: 200% 100%;
  animation: 1.5s shine linear infinite;
  display: flex;
  height: 35px;
  width: 35px;
  @keyframes shine {
    to {
      background-position-x: -200%;
    }
  }

}
</style>