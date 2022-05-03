<template>
  <div class="pwm__row container-fluid pt-1 pb-1" :class="isFirst ? '' : 'pwm__row__border'" @click="$emit('wasClicked')">
    <div class="row align-items-center">
      <div class="col-6 col-md-2 h2 m-0 d-flex">
        <img
            :src="this.imageUrlPassword"
            class="img-fluid flex-grow-1 flex-shrink-0 node-logo-color"
            :alt="edge.node.name"
            v-if="edge.node.type === 'credential'"
        >

        <img
            :src="this.imageUrl"
            :alt="edge.node.name"
            v-else-if="edge.node.type === 'folder' && isOwner"
            class="img-fluid flex-grow-1 flex-shrink-0 node-logo-color"
        >

        <img
            :src="this.imageUrlShared"
            :alt="edge.node.name"
            v-else-if="edge.node.type === 'folder' && !isOwner"
            class="img-fluid flex-grow-1 flex-shrink-0 node-logo-color"
        >
        <p class="h6 align-self-center" v-if="edge.type === 'share' || edge.type === 'organization'">
          <i class="bi bi-share-fill" :title="this.showOwnerName()" v-if="edge.type === 'organization'"></i>
          <i class="bi bi-share" :title="this.showOwnerName()" v-if="edge.type === 'share'"></i>
        </p>
      </div>
      <div class="col flex-grow-1 cropped" :title="edge.node.name">
        {{ edge.node.name }}
      </div>
      <div id="contextMenu" class="col-md-1 justify-content-end" @click="onContextMenu($event)">
        <i class="fas fa-ellipsis-h box"></i>
      </div>

    </div>

    <SelectableListModal
        :ref="'modal1' + this.edge.node.id"
        :id="'modal1' + this.edge.node.id"
        :options="organizations"
        :loading="organizationsLoading"
        :no-data-text="$t('edge.contextMenu.addToOrganization.noOrganizationsAvailable')"
        :modal-title="$t('edge.contextMenu.addToOrganization.title')"
        :description="$t('edge.contextMenu.addToOrganization.description')"
        @onSubmit="submitOrganization"
        @onOpen="openModal"
    ></SelectableListModal>

  </div>

</template>

<script>
import {APP_STORAGE, AXIOS, DATE_TIME_SERVICE, StartUp,} from "../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes/index";
import {RESPONSE_CODE_OK, RESPONSE_FIELD_MESSAGES} from "../../../../../../lib/js/src/Backend/Axios";
import SelectableListModal from "../Component/Modal/SelectableListModal";
import {Host} from "../../../../../../lib/js/src/Backend/Host";
import Modal from "../../../../../../lib/js/src/Components/Modal";

export default {
  name: "Edge",
  components: {Modal, SelectableListModal},
  props: {
    edge: {},
    isFirst: {
      type: Boolean,
      default: true
    }
  },
  data: function () {
    return {
      container: {
        container: null,
        services: {
          axios: null,
          appStorage: null,
          dateTimeService: null,
        },
      },
      organizations: [],
      selectedOrganization: null,
      imageUrl: '',
      imageUrlShared: '',
      imageUrlPassword: '',
      organizationsLoading: true,
    }
  },

  computed: {
    isOwner: function () {
      const userHash = this.container.services.appStorage.getUserHash();
      if (this.edge.node.user.hash === userHash) return true;

      for (let i = 0; i < this.edge.node.shared_to.content.length; i++) {
        const share = this.edge.node.shared_to.content[i];
        if (userHash === share.user.hash) return false;
      }
      return true;
    },
  },
  created: function () {
    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();
    this.container.container = startUp.getContainer();
    this.container.services.axios = this.container.container.query(AXIOS);
    this.container.services.dateTimeService = this.container.container.query(DATE_TIME_SERVICE);
    this.container.services.appStorage = this.container.container.query(APP_STORAGE);

    const host = new Host();
    const url = host.getHost().replace('index.php', '');
    this.imageUrl = url + 'asset/svg/folder.svg';
    this.imageUrlShared = url + 'asset/svg/folder-shared.svg';
    this.imageUrlPassword = url + 'asset/svg/password.svg';
  },
  methods: {
    onContextMenu(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      const items = [];

      if (this.edge.node.organization === null) {
        items.push(
            {
              label: this.$t('edge.selection.addToOrganization'),
              onClick: () => {
                this.$refs['modal1' + this.edge.node.id].showModal();
              },
            }
        )
      }

      items.push(
          {
            label: this.$t('edge.selection.remove'),
            onClick: () => {
              this.$emit('wasDeleted');
            },
          }
      )
      //shou our menu
      this.$contextmenu({
        x: e.x,
        y: e.y,
        items: items,
      });
    },
    copyPassword: function () {
      console.log("copy");
    },
    formatDate: function (date) {
      return this.container.services.dateTimeService.format(date);
    },
    openModal: function () {
      this.organizationsLoading = true;

      this.container.services.axios.get(
          ROUTES.getAllOrganizations(
              this.container.services.appStorage.getUserHash(),
              false
          )
      )
          .then((r) => {
            if (RESPONSE_CODE_OK in r.data) {
              return r.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {

            const organizations = [];

            for (let index in data.organizations) {
              const organization = data.organizations[index];

              organizations.push(
                  {
                    value: organization.id
                    , text: organization.name
                  }
              )
            }
            this.organizationsLoading = false;
            this.organizations = organizations;
          });
    },
    resetModal: function () {
      this.selectedOrganization = null;
    },
    removeNode: function () {
      this.container.services.axios.delete(
          ROUTES.getNodeDelete(),
          {
            node_id: this.edge.node.id
          }
      ).then(
          (r) => {
            // TODO remove node
          }
      )
      ;
    },
    showOwnerName: function () {
      if (this.edge.type === 'organization') {
        return 'Shared by ' + this.edge.node.user.name + ' with ' + this.edge.node.organization.name;
      }
      if (this.edge.type === 'share') {
        return 'Shared by ' + this.edge.node.user.name + ' with you';
      }
    },
    submitOrganization: function (selected) {
      this.container.services.axios.httpPut(
          ROUTES.getOrganizationsAddNode(),
          {
            node_id: this.edge.node.id
            , organization_id: selected
          }
      ).then(
          (r) => {
            this.$store.dispatch(
                'updateSelectedNode'
                , {
                  organization: r.data.organization
                }
            );

            this.$store.dispatch(
                'updateSelectedEdge'
                , {
                  type: r.data.type
                }
            )
          }
      )
      ;
    }
  }
}
</script>

<style scoped>

</style>