<template>
  <div class="row" id="pwm__detail__part">
    <div class="col d-flex flex-column">
      <div class="col">
        <div class="row mt-4 mb-4">
          <div class="col-md-1 align-self-center">
            <p class="h1"> <b-icon-code-square class="node-logo-color"></b-icon-code-square></p>
          </div>
          <div class="col-md-4 d-flex align-items-center">
            <div class="row">
              <div class="col">
                <input
                    type="text"
                    class="form-control border-0"
                    :value="this.edge.node.name"
                    @change="onNameChange"
                >
              </div>
            </div>
          </div>
        </div>
        <form id="tab__detail__wrapper">
          <div class="form-group">
            <small class="form-text text-muted">{{ $t('credential.detail.userNameLabel') }}</small>
            <input type="text"
                   class="form-control"
                   :value="this.edge.node.username"
                   @change="onUsernameChange"
            >
          </div>
          <div class="form-group">
            <small class="form-text text-muted">{{ $t('credential.detail.passwordLabel') }}</small>
            <div class="input-group">
              <input :type="passwordField.visible ? 'text' : 'password'" class="form-control"
                     id="pwm__login__password"
                     :readonly="!passwordField.visible"
                     :value="this.password"
                     autocomplete="off"
              >
              <div class="input-group-append" id="pwm__password__eye" @click="loadPassword">
                <div class="input-group-text">
                  <i class="fas fa-eye"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <small class="form-text text-muted">{{ $t('credential.detail.websiteLabel') }}</small>
            <div class="input-group">
              <input
                  type="url"
                  class="form-control"
                  :placeholder="this.edge.node.url"
                  :value="this.edge.node.url"
                  id="pwm__login__website"
              >
              <div class="input-group-append" id="pwm__url__button" @click="openUrl">
                <div class="input-group-text">
                  <i class="fas fa-external-link-alt"></i>
                </div>
              </div>

            </div>
          </div>

          <Tab @passwordUsed="passwordUsed"></Tab>

        </form>

      </div>
    </div>
  </div>

</template>

<script>
import {RESPONSE_CODE_OK} from "../../../../../../../lib/js/src/Backend/Request";
import {AXIOS, StartUp, URL_SERVICE} from "../../../../../../../lib/js/src/StartUp";
import {Container} from "../../../../../../../lib/js/src/DI/Container";
import {ROUTES} from "../../../config/routes";
import {RESPONSE_FIELD_MESSAGES} from "../../../../../../../lib/js/src/Backend/Axios";
import Tab from "./Tab";
import {mapState} from "vuex";
import moment from "moment";
import _ from "lodash"

export default {
  name: "EdgeDetail",
  components: {Tab},
  data() {
    return {
      createdFormatted: null,
      passwordField: {
        visible: false
      }
    }
  },
  computed: {
    ...mapState({
      edge: function (state) {
        let edge = state.selectedEdge;
        edge.node.url = encodeURI(edge.node.url);
        edge.node.createdFormatted = moment(edge.node.create_ts.date).format();

        return edge;
      }
    })
  },
  created() {
    this.doWork = _.debounce(this.onChange, 500);
    this.password = this.edge.node.password.placeholder;

    const startUp = new StartUp(
        new Container()
    );
    startUp.setUp();

    this.container = startUp.getContainer();
    this.axios = this.container.query(AXIOS);

  },

  methods: {
    passwordUsed(p) {

      _.debounce(
          () => {
            this.axios.post(
                ROUTES.getPasswordManagerUsersUpdate()
                , {
                  username: this.edge.node.username
                  , url: this.edge.node.url
                  , nodeId: this.edge.node.id
                  , password: p
                }
            ).then((response) => {
              console.log(response)
              if (RESPONSE_CODE_OK in response.data) {
                return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
              }
              return [];
            }).then((data) => {
              if (true === data.has_changes) {
                this.updatePassword(p, true);
              }
            })
          },
          250
      )();

    },
    onNameChange(event) {

      _.debounce(
          () => {
            const newName = event.target.value;
            if (newName === this.edge.node.name || newName === "") return;

            this.$store.dispatch(
                "updateSelectedNode"
                , {
                  name: newName
                }
            );
          }, 500
      )();
    },
    onUsernameChange(event) {
      _.debounce(
          () => {
            const newUserName = event.target.value;
            if (newUserName === this.edge.node.username || newUserName === "") return;

            this.$store.dispatch(
                "updateSelectedNode"
                , {
                  username: newUserName
                }
            );
          }, 500
      )();
    },
    openUrl() {
      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const container = startUp.getContainer();
      const urlService = container.query(URL_SERVICE);
      if (!urlService.isValidURL(this.edge.node.url)) return;

      window.open(
          encodeURI(this.edge.node.url)
          , '_blank'
      ).focus();
    },
    loadPassword() {

      const startUp = new StartUp(
          new Container()
      );
      startUp.setUp();
      const container = startUp.getContainer();
      const axios = container.query(AXIOS);

      if (true === this.passwordField.visible) {
        this.updatePassword(
            this.passwordField
            , false
        );
        return;
      }

      axios.request(
          ROUTES.getCredential(
              this.edge.node.id
          )
      )
          .then((response) => {
            if (RESPONSE_CODE_OK in response.data) {
              return response.data[RESPONSE_CODE_OK][RESPONSE_FIELD_MESSAGES];
            }
            return [];
          })
          .then((data) => {
            this.updatePassword(
                data.decrypted
                , true
            );
          })

    },
    updatePassword(password, visible) {
      this.password = password;
      this.passwordField.visible = visible;
    },
    onChange: function (form) {
      // TODO update on server
      // this.$store.dispatch("updateEdge", edge);
      console.log("test");
      console.log(form)
    }
  }
}
</script>

<style scoped lang="scss">

</style>