<template>
  <div>
    <div class="results mt-3 rounded border tab_result_box d-flex flex-column mb-2">
      <NoDataFound
          :visible="data.length === 0"
          :text="noDataFoundText"
          :type="type"
      ></NoDataFound>
      <div class="container overflow-auto row-container">
        <div class="row border-bottom"
             v-for="share in data"
             :key="share.id"
        >

          <div class="col">
            <div class="row justify-content-between">
              <div class="col-10">
                <div class="row">
                  <div class="col-1 d-flex">
                    <Thumbnail
                        :source="getAssetUrl(getJwt(share))"
                        :skip-cache="false"
                    ></Thumbnail>
                  </div>
                  <div class="col-11 p-0">
                    <div class="container">
                      <div class="row cropped">
                        <a :href="getAttachmentUrl(share.file.id)"
                           target="_blank"
                           v-if="type==='attachment'"
                        >
                          <div class="container-fluid" :title="getName(share)">
                            <div class="row">
                              {{ getName(share) }}
                            </div>
                            <div class="row">
                              <small>
                                {{ getDescription() }}
                                {{ formatDate(getCreateTs(share)) }}
                              </small>
                            </div>
                          </div>
                        </a>
                        <div class="container-fluid" v-else :title="getName(share)">
                          <div class="row">
                            <strong>{{ getName(share) }}</strong>
                          </div>
                          <div class="row">
                            <small>
                              {{ getDescription() }}
                              {{ formatDate(getCreateTs(share)) }}
                            </small>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-2 align-self-center" v-if="canRemove">
                <div class="row justify-content-end">
                  <div class="col-3">
                    <i class="fas fa-times remove" @click="remove()"></i>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <Modal
              :open="removeModalOpened"
              :has-description="false"
              @saved="this.$emit('onRemove', share);this.removeModalOpened=false"
              @closed="this.removeModalOpened=false"
              :unique-id="getUniqueId()"
          >
            <template v-slot:title>
              <slot name="title"></slot>
            </template>
            <template v-slot:body-description>
              <slot name="body-description"></slot>
            </template>
            <template v-slot:body>
              <slot name="body"></slot>
            </template>
            <template v-slot:button-text>
              <slot name="button-text"></slot>
            </template>
            <template v-slot:negative-button-text>
              <slot name="negative-button-text"></slot>
            </template>
          </Modal>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import {ROUTES} from "../../../../../config/routes/index";
import {DATE_TIME_SERVICE, StartUp} from "../../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../../lib/js/src/DI/Container";
import Thumbnail from "../../../../../../../../lib/js/src/Components/Thumbnail";
import NoDataFound from "../../../../../../../../lib/js/src/Components/NoDataFound";
import Modal from "../../../../../../../../lib/js/src/Components/Modal";
import IsLoading from "../../../../../../../../lib/js/src/Components/IsLoading";

export default {
  name: "ResultBox",
  components: {IsLoading, Modal, Thumbnail, NoDataFound},
  props: {
    noDataFoundText: '',
    removing: false,
    data: [],
    canRemove: false,
    isLoading: {
      type: Boolean,
      default: true
    },
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
      removeModalOpened: false,
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
    getUniqueId() {
      return "resultbox" + this.type
    },
    getAssetUrl(jsonWebToken) {
      return ROUTES.getAssetUrl(jsonWebToken);
    },
    formatDate(date) {
      return this.container.services.dateTimeService.format(date);
    },
    remove() {
      this.removeModalOpened = true;
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
        return data.user.create_ts.date;
      } else if (this.type === 'comment') {
        return data.create_ts.date;
      } else if (this.type === 'attachment') {
        return data.create_ts.date;
      }

      throw 'unknown' + this.type;
    },

    getDescription() {
      if (this.type === 'user') {
        return this.$t('credential.detail.share.sharedDateTimeLabel');
      } else if (this.type === 'comment') {
        return this.$t('credential.detail.comment.commentDateTimeLabel');
      } else if (this.type === 'attachment') {
        return this.$t('credential.detail.attachment.attachmentDateTimeLabel');
      }
      throw 'unknown' + this.type;
    },
    getAttachmentUrl: function (fileId) {
      return ROUTES.getNodeAttachment(fileId);
    }
  }
}
</script>

<style scoped lang="scss">
.row-container {
  max-height: 30vh;
}
</style>