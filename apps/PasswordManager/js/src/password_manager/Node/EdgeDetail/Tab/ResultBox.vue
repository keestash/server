<template>
  <div>
    <div class="results mt-3 rounded border tab_result_box d-flex flex-column mb-2">
      <NoDataFound
          :visible="data.length === 0"
          :text="noDataFoundText"
          :type="type"
      ></NoDataFound>
      <div class="container">
        <div class="row border-bottom"
             v-for="share in data"
             :key="share.id"
        >

          <div class="col">
            <div class="row justify-content-between">
              <div class="col-10">
                <div class="row">
                  <div class="col-1">
                    <Thumbnail
                        :source="getAssetUrl(getJwt(share))"
                    ></Thumbnail>
                  </div>
                  <div class="col-11">
                    <div class="container">
                      <div class="row cropped">
                        <a :href="getAttachmentUrl(share.file.id)"
                           target="_blank"
                           v-if="type==='attachment'"
                        >{{ getName(share) }}</a>
                        <template v-else>

                          {{ getName(share) }}
                        </template>
                      </div>
                      <div class="row">
                        <small> {{ $t('credential.detail.share.sharedDateTimeLabel') }}
                          {{
                            formatDate(getCreateTs)
                          }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-2 align-self-center" v-if="canRemove">
                <div class="row justify-content-end">
                  <div class="col-3" @click="remove(share)">
                    <i class="fas fa-times remove"></i>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import {ROUTES} from "../../../../config/routes";
import {DATE_TIME_SERVICE, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";

export default {
  name: "ResultBox",
  components: {Thumbnail, NoDataFound},
  props: {
    noDataFoundText: '',
    removing: false,
    data: [],
    canRemove: false,
    type: null,
    strings: {
      modal: {
        title: '',
        content: '',
        buttonText: '',
      }
    }
  },
  data() {
    return {
      container: {
        services: {
          dateTimeService: null
        }
      }
    }
  },
  created() {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container.container = startUp.getContainer();
    this.container.services.dateTimeService = this.container.container.query(DATE_TIME_SERVICE);
  },
  methods: {
    getAssetUrl(jsonWebToken) {
      return ROUTES.getAssetUrl(jsonWebToken);
    },
    formatDate(date) {
      return this.container.services.dateTimeService.format(date);
    },
    remove(data) {
      this.$emit('onRemove', data);
    },

    // temporary, until i find a good solution how to differ clean
    getJwt(data) {
      if (this.type === 'user') {
        return data.user.jwt;
      } else if (this.type === 'comment') {
        return data.jwt;
      } else if (this.type === 'attachment') {
        return data.jwt;
      }

      throw 'unknown' + this.type;
    },
    getName(data) {
      if (this.type === 'user') {
        return data.user.name;
      } else if (this.type === 'comment') {
        return data.comment;
      } else if (this.type === 'attachment') {
        return data.file.name;
      }


      throw 'unknown' + this.type;
    },
    getCreateTs(data) {
      if (this.type === 'user') {
        return data.user.createTs;
      } else if (this.type === 'comment') {
        return data.createTs;
      }

      throw 'unknown' + this.type;
    },
    getAttachmentUrl: function (fileId) {
      return ROUTES.getNodeAttachment(fileId);
    }
  }
}
</script>

<style scoped>

</style>