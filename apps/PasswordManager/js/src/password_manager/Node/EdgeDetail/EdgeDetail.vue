<template>
  <div class="row" id="pwm__detail__part">
    <div class="col d-flex flex-column">
      <div class="col">
        <div class="row mt-4 mb-4">
          <div class="col-md-1 align-self-center">
            <form id="my_form">
              <div
                  class="avatar node-avatar"
                  style="width: 60px; height: 60px; border:none; background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeD0iMHB4IiB5PSIwcHgiDQogICAgIHZpZXdCb3g9IjAgMCA0OTAuNjY3IDQ5MC42NjciIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ5MC42NjcgNDkwLjY2NzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBhdGggc3R5bGU9ImZpbGw6IzI2OWRmZjsiIGQ9Ik05NiwyMzQuNjM3SDcxLjgwOGwxMi4wOTYtMjAuOTQ5YzIuOTEyLTUuMTIxLDEuMTItMTEuNjMzLTQuMDAxLTE0LjU0NQ0KCQljLTUuMDczLTIuODg0LTExLjUyMi0xLjE1Ni0xNC40NzQsMy44NzhMNTMuMzMzLDIyMy45N2wtMTIuMDk2LTIwLjk0OWMtMi45NzktNS4wODItOS41MTUtNi43ODctMTQuNTk3LTMuODA3DQoJCWMtNS4wMzQsMi45NTEtNi43NjIsOS40MDEtMy44NzgsMTQuNDc0bDEyLjA5NiwyMC45NDlIMTAuNjY3QzQuNzc2LDIzNC42MzcsMCwyMzkuNDEzLDAsMjQ1LjMwNA0KCQljMCw1Ljg5MSw0Ljc3NiwxMC42NjcsMTAuNjY3LDEwLjY2N2gyNC4xOTJMMjIuNzYzLDI3Ni45MmMtMi45NDUsNS4xMDItMS4xOTgsMTEuNjI1LDMuOTA0LDE0LjU3MQ0KCQljMS42MTksMC45NDMsMy40NiwxLjQzNiw1LjMzMywxLjQyOWMzLjgxOCwwLjAwOCw3LjM0OS0yLjAyNyw5LjI1OS01LjMzM2wxMi4wNzUtMjAuOTQ5bDEyLjA5NiwyMC45NDkNCgkJYzEuOTA5LDMuMzA3LDUuNDQsNS4zNDEsOS4yNTksNS4zMzNjMS44NzMsMC4wMDcsMy43MTUtMC40ODYsNS4zMzMtMS40MjljNS4xMDItMi45NDYsNi44NDktOS40NjksMy45MDQtMTQuNTcxTDcxLjgwOCwyNTUuOTdIOTYNCgkJYzUuODkxLDAsMTAuNjY3LTQuNzc2LDEwLjY2Ny0xMC42NjdDMTA2LjY2NywyMzkuNDEzLDEwMS44OTEsMjM0LjYzNyw5NiwyMzQuNjM3eiIvPg0KICAgIDxwYXRoIHN0eWxlPSJmaWxsOiMyNjlkZmY7IiBkPSJNMjI0LDIzNC42MzdoLTI0LjE5MmwxMi4wOTYtMjAuOTQ5YzIuOTEyLTUuMTIxLDEuMTIxLTExLjYzMy00LTE0LjU0NQ0KCQljLTUuMDczLTIuODg1LTExLjUyMy0xLjE1Ny0xNC40NzQsMy44NzhsLTEyLjA5NiwyMC45NDlsLTEyLjA5Ni0yMC45NDljLTIuOTc5LTUuMDgyLTkuNTE0LTYuNzg3LTE0LjU5Ni0zLjgwOA0KCQljLTUuMDM1LDIuOTUxLTYuNzYzLDkuNDAxLTMuODc4LDE0LjQ3NGwxMi4wOTYsMjAuOTQ5aC0yNC4xOTJjLTUuODkxLDAtMTAuNjY3LDQuNzc2LTEwLjY2NywxMC42NjcNCgkJYzAsNS44OTEsNC43NzYsMTAuNjY3LDEwLjY2NywxMC42NjdoMjQuMTkybC0xMi4wOTYsMjAuOTQ5Yy0yLjk0NSw1LjEwMi0xLjE5OCwxMS42MjUsMy45MDQsMTQuNTcxDQoJCWMxLjYxOSwwLjk0MywzLjQ2LDEuNDM2LDUuMzMzLDEuNDI5YzMuODE4LDAuMDA4LDcuMzQ5LTIuMDI3LDkuMjU5LTUuMzMzbDEyLjA3NS0yMC45NDlsMTIuMDk2LDIwLjk0OQ0KCQljMS45MDksMy4zMDcsNS40NCw1LjM0MSw5LjI1OSw1LjMzM2MxLjg3MywwLjAwNywzLjcxNS0wLjQ4Niw1LjMzMy0xLjQyOWM1LjEwMi0yLjk0Niw2Ljg0OS05LjQ2OSwzLjkwNC0xNC41NzFsLTEyLjExNy0yMC45NDkNCgkJSDIyNGM1Ljg5MSwwLDEwLjY2Ny00Ljc3NiwxMC42NjctMTAuNjY3QzIzNC42NjcsMjM5LjQxMywyMjkuODkxLDIzNC42MzcsMjI0LDIzNC42Mzd6Ii8+DQogICAgPHBhdGggc3R5bGU9ImZpbGw6IzI2OWRmZjsiIGQ9Ik0zNTIsMjM0LjYzN2gtMjQuMTkybDEyLjA5Ni0yMC45NDljMi45MTItNS4xMjEsMS4xMjEtMTEuNjMzLTQtMTQuNTQ1DQoJCWMtNS4wNzMtMi44ODUtMTEuNTIzLTEuMTU3LTE0LjQ3NCwzLjg3OGwtMTIuMDk2LDIwLjk0OWwtMTIuMDk2LTIwLjk0OWMtMi45NzktNS4wODItOS41MTQtNi43ODctMTQuNTk2LTMuODA4DQoJCWMtNS4wMzUsMi45NTEtNi43NjMsOS40MDEtMy44NzgsMTQuNDc0bDEyLjA5NiwyMC45NDloLTI0LjE5MmMtNS44OTEsMC0xMC42NjcsNC43NzYtMTAuNjY3LDEwLjY2Nw0KCQljMCw1Ljg5MSw0Ljc3NiwxMC42NjcsMTAuNjY3LDEwLjY2N2gyNC4xOTJsLTEyLjA5NiwyMC45NDljLTIuOTQ1LDUuMTAyLTEuMTk4LDExLjYyNSwzLjkwNCwxNC41NzENCgkJYzEuNjE5LDAuOTQzLDMuNDYsMS40MzYsNS4zMzMsMS40MjljMy44MTgsMC4wMDgsNy4zNDktMi4wMjcsOS4yNTktNS4zMzNsMTIuMDc1LTIwLjk0OWwxMi4wOTYsMjAuOTQ5DQoJCWMxLjkwOSwzLjMwNyw1LjQ0LDUuMzQxLDkuMjU5LDUuMzMzYzEuODczLDAuMDA3LDMuNzE1LTAuNDg2LDUuMzMzLTEuNDI5YzUuMTAyLTIuOTQ2LDYuODQ5LTkuNDY5LDMuOTA0LTE0LjU3MWwtMTIuMDk2LTIwLjk0OQ0KCQlIMzUyYzUuODkxLDAsMTAuNjY3LTQuNzc2LDEwLjY2Ny0xMC42NjdDMzYyLjY2NywyMzkuNDEzLDM1Ny44OTEsMjM0LjYzNywzNTIsMjM0LjYzN3oiLz4NCiAgICA8cGF0aCBzdHlsZT0iZmlsbDojMjY5ZGZmOyIgZD0iTTQ4MCwyMzQuNjM3aC0yNC4xOTJsMTIuMDk2LTIwLjk0OWMyLjkxMS01LjEyMSwxLjExOS0xMS42MzMtNC4wMDItMTQuNTQ0DQoJCWMtNS4wNzMtMi44ODMtMTEuNTIxLTEuMTU2LTE0LjQ3MywzLjg3OGwtMTIuMDk2LDIwLjk0OWwtMTIuMDk2LTIwLjk0OWMtMi45OC01LjA4Mi05LjUxNS02Ljc4Ni0xNC41OTctMy44MDYNCgkJYy01LjAzMywyLjk1Mi02Ljc2MSw5LjQtMy44NzgsMTQuNDczbDEyLjA5NiwyMC45NDloLTI0LjE5MmMtNS44OTEsMC0xMC42NjcsNC43NzYtMTAuNjY3LDEwLjY2Nw0KCQljMCw1Ljg5MSw0Ljc3NiwxMC42NjcsMTAuNjY3LDEwLjY2N2gyNC4xOTJsLTEyLjA5NiwyMC45NDljLTIuOTQ1LDUuMTAyLTEuMTk4LDExLjYyNSwzLjkwNCwxNC41NzENCgkJYzEuNjE5LDAuOTQzLDMuNDYsMS40MzYsNS4zMzMsMS40MjljMy44MTgsMC4wMDgsNy4zNDktMi4wMjcsOS4yNTktNS4zMzNsMTIuMDc1LTIwLjk0OWwxMi4wOTYsMjAuOTQ5DQoJCWMxLjkwOSwzLjMwNyw1LjQ0LDUuMzQxLDkuMjU5LDUuMzMzYzEuODczLDAuMDA3LDMuNzE1LTAuNDg2LDUuMzMzLTEuNDI5YzUuMTAyLTIuOTQ2LDYuODQ5LTkuNDY5LDMuOTA0LTE0LjU3MWwtMTIuMDk2LTIwLjk0OQ0KCQlINDgwYzUuODkxLDAsMTAuNjY3LTQuNzc2LDEwLjY2Ny0xMC42NjdDNDkwLjY2NywyMzkuNDEzLDQ4NS44OTEsMjM0LjYzNyw0ODAsMjM0LjYzN3oiLz4NCjwvZz4NCiAgICA8cGF0aCBkPSJNOTYsMjU1Ljk3SDEwLjY2N0M0Ljc3NiwyNTUuOTcsMCwyNTEuMTk1LDAsMjQ1LjMwNGMwLTUuODkxLDQuNzc2LTEwLjY2NywxMC42NjctMTAuNjY3SDk2DQoJYzUuODkxLDAsMTAuNjY3LDQuNzc2LDEwLjY2NywxMC42NjdDMTA2LjY2NywyNTEuMTk1LDEwMS44OTEsMjU1Ljk3LDk2LDI1NS45N3oiIHN0eWxlPSJmaWxsOiMyNjlkZmYiLz4NCiAgICA8cGF0aCBkPSJNMzIsMjkyLjkyYy01Ljg5MSwwLTEwLjY2Ny00Ljc3Ni0xMC42NjYtMTAuNjY3YzAtMS44NzIsMC40OTMtMy43MTIsMS40MjktNS4zMzNMNjUuNDI5LDIwMw0KCWMzLjE2LTQuOTcyLDkuNzUzLTYuNDQsMTQuNzI0LTMuMjc5YzQuNjc4LDIuOTc0LDYuMjk5LDkuMDM1LDMuNzI5LDEzLjk0NmwtNDIuNjY3LDczLjkyQzM5LjMxNCwyOTAuODgsMzUuODAzLDI5Mi45MTIsMzIsMjkyLjkyeiINCiAgICAgICAgICBzdHlsZT0iZmlsbDojMjY5ZGZmIg0KICAgIC8+DQogICAgPHBhdGggZD0iTTc0LjY2NywyOTIuOTJjLTMuODExLDAtNy4zMzItMi4wMzMtOS4yMzctNS4zMzNsLTQyLjY2Ny03My45MmMtMi43MzEtNS4yMi0wLjcxMy0xMS42NjUsNC41MDctMTQuMzk2DQoJYzQuOTExLTIuNTY5LDEwLjk3Mi0wLjk0OSwxMy45NDYsMy43MjlsNDIuNjY3LDczLjkyYzIuOTQ2LDUuMTAyLDEuMTk4LDExLjYyNS0zLjkwNCwxNC41NzFjLTEuNjIxLDAuOTM2LTMuNDYxLDEuNDI5LTUuMzMzLDEuNDI5DQoJSDc0LjY2N3oiIHN0eWxlPSJmaWxsOiMyNjlkZmYiLz4NCiAgICA8cGF0aCBkPSJNMjI0LDI1NS45N2gtODUuMzMzYy01Ljg5MSwwLTEwLjY2Ny00Ljc3Ni0xMC42NjctMTAuNjY3YzAtNS44OTEsNC43NzYtMTAuNjY3LDEwLjY2Ny0xMC42NjdIMjI0DQoJYzUuODkxLDAsMTAuNjY3LDQuNzc2LDEwLjY2NywxMC42NjdDMjM0LjY2NywyNTEuMTk1LDIyOS44OTEsMjU1Ljk3LDIyNCwyNTUuOTd6IiBzdHlsZT0iZmlsbDojMjY5ZGZmIi8+DQogICAgPHBhdGggZD0iTTE2MCwyOTIuOTJjLTUuODkxLDAtMTAuNjY3LTQuNzc2LTEwLjY2Ni0xMC42NjdjMC0xLjg3MiwwLjQ5My0zLjcxMiwxLjQyOS01LjMzM0wxOTMuNDI5LDIwMw0KCWMzLjE2LTQuOTcyLDkuNzUzLTYuNDQsMTQuNzI0LTMuMjc5YzQuNjc4LDIuOTc0LDYuMjk4LDkuMDM0LDMuNzI5LDEzLjk0NmwtNDIuNjY3LDczLjkyDQoJQzE2Ny4zMTQsMjkwLjg4LDE2My44MDMsMjkyLjkxMiwxNjAsMjkyLjkyeiIgc3R5bGU9ImZpbGw6IzI2OWRmZiIvPg0KICAgIDxwYXRoIGQ9Ik0yMDIuNjY3LDI5Mi45MmMtMy44MTEsMC03LjMzMi0yLjAzMy05LjIzNy01LjMzM2wtNDIuNjY3LTczLjkyYy0yLjczMS01LjIyLTAuNzEzLTExLjY2NSw0LjUwNy0xNC4zOTYNCgljNC45MTEtMi41NjksMTAuOTcyLTAuOTQ5LDEzLjk0NiwzLjcyOWw0Mi42NjcsNzMuOTJjMi45NDYsNS4xMDIsMS4xOTgsMTEuNjI1LTMuOTA0LDE0LjU3MWMtMS42MjEsMC45MzYtMy40NjEsMS40MjktNS4zMzMsMS40MjkNCglIMjAyLjY2N3oiIHN0eWxlPSJmaWxsOiMyNjlkZmYiLz4NCiAgICA8cGF0aCBkPSJNMzUyLDI1NS45N2gtODUuMzMzYy01Ljg5MSwwLTEwLjY2Ny00Ljc3Ni0xMC42NjctMTAuNjY3YzAtNS44OTEsNC43NzYtMTAuNjY3LDEwLjY2Ny0xMC42NjdIMzUyDQoJYzUuODkxLDAsMTAuNjY3LDQuNzc2LDEwLjY2NywxMC42NjdDMzYyLjY2NywyNTEuMTk1LDM1Ny44OTEsMjU1Ljk3LDM1MiwyNTUuOTd6IiBzdHlsZT0iZmlsbDojMjY5ZGZmIi8+DQogICAgPHBhdGggZD0iTTI4OCwyOTIuOTJjLTUuODkxLDAtMTAuNjY3LTQuNzc2LTEwLjY2Ni0xMC42NjdjMC0xLjg3MiwwLjQ5My0zLjcxMiwxLjQyOS01LjMzM0wzMjEuNDI5LDIwMw0KCWMzLjE2LTQuOTcyLDkuNzUzLTYuNDQsMTQuNzI0LTMuMjhjNC42NzgsMi45NzQsNi4yOTksOS4wMzUsMy43MjksMTMuOTQ2bC00Mi42NjcsNzMuOTJDMjk1LjMxNCwyOTAuODgsMjkxLjgwMywyOTIuOTEyLDI4OCwyOTIuOTINCgl6IiBzdHlsZT0iZmlsbDojMjY5ZGZmIi8+DQogICAgPHBhdGggZD0iTTMzMC42NjcsMjkyLjkyYy0zLjgxMSwwLTcuMzMyLTIuMDMzLTkuMjM3LTUuMzMzbC00Mi42NjctNzMuOTJjLTIuNzMxLTUuMjItMC43MTMtMTEuNjY1LDQuNTA3LTE0LjM5Ng0KCWM0LjkxMS0yLjU2OSwxMC45NzItMC45NDksMTMuOTQ2LDMuNzI5bDQyLjY2Nyw3My45MmMyLjk0Niw1LjEwMiwxLjE5OCwxMS42MjUtMy45MDQsMTQuNTcxYy0xLjYyMSwwLjkzNi0zLjQ2MSwxLjQyOS01LjMzMywxLjQyOQ0KCUgzMzAuNjY3eiIgc3R5bGU9ImZpbGw6IzI2OWRmZiIvPg0KICAgIDxwYXRoIGQ9Ik00ODAsMjU1Ljk3aC04NS4zMzNjLTUuODkxLDAtMTAuNjY3LTQuNzc2LTEwLjY2Ny0xMC42NjdjMC01Ljg5MSw0Ljc3Ni0xMC42NjcsMTAuNjY3LTEwLjY2N0g0ODANCgljNS44OTEsMCwxMC42NjcsNC43NzYsMTAuNjY3LDEwLjY2N0M0OTAuNjY3LDI1MS4xOTUsNDg1Ljg5MSwyNTUuOTcsNDgwLDI1NS45N3oiIHN0eWxlPSJmaWxsOiMyNjlkZmYiLz4NCiAgICA8cGF0aCBkPSJNNDE2LDI5Mi45MmMtNS44OTEsMC0xMC42NjctNC43NzYtMTAuNjY2LTEwLjY2N2MwLTEuODcyLDAuNDkzLTMuNzEyLDEuNDI5LTUuMzMzTDQ0OS40MjksMjAzDQoJYzMuMTYtNC45NzIsOS43NTMtNi40NCwxNC43MjQtMy4yNzljNC42NzgsMi45NzQsNi4yOTgsOS4wMzQsMy43MjksMTMuOTQ2bC00Mi42NjcsNzMuOTINCglDNDIzLjMxNCwyOTAuODgsNDE5LjgwMywyOTIuOTEyLDQxNiwyOTIuOTJ6IiBzdHlsZT0iZmlsbDojMjY5ZGZmIi8+DQogICAgPHBhdGggZD0iTTQ1OC42NjcsMjkyLjkyYy0zLjgxMSwwLTcuMzMyLTIuMDMzLTkuMjM3LTUuMzMzbC00Mi42NjctNzMuOTJjLTIuNzMxLTUuMjItMC43MTMtMTEuNjY1LDQuNTA3LTE0LjM5Ng0KCWM0LjkxMS0yLjU2OSwxMC45NzItMC45NDksMTMuOTQ2LDMuNzI5bDQyLjY2Nyw3My45MmMyLjk0Niw1LjEwMiwxLjE5OCwxMS42MjUtMy45MDQsMTQuNTcxYy0xLjYyMSwwLjkzNi0zLjQ2MSwxLjQyOS01LjMzMywxLjQyOQ0KCUg0NTguNjY3eiIgc3R5bGU9ImZpbGw6IzI2OWRmZiIvPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KICAgIDxnPg0KPC9nPg0KPC9zdmc+DQo=')"
              ></div>
              <input type="file" id="my_file" style="display: none;"/>
            </form>
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