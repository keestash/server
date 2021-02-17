<template>
  <div class="flex-grow-1 d-flex justify-content-center">
    <div style="min-width: 70vw">
      <div class="container mt-2">
        <h3>Keestash</h3>
      </div>
      <div class="container">
        you can edit the organization here. Add new members, change properties, etc.
      </div>
      <div class="mb-4 pl-5 pr-5">
        <hr class="solid">
      </div>
      <b-container class="d-flex mt-2">
        <b-row class="my-1 flex-grow-1 d-flex justify-content-between">
          <b-col sm="3">
            <label>ID</label>
            <b-form-input v-model="organization.id" readonly></b-form-input>
          </b-col>
          <b-col sm="3">
            <label>Name</label>
            <b-form-input id="range-2" value="Keestash" v-model="organization.name" debounce="500"
                          @change="onInputChange"></b-form-input>
          </b-col>
        </b-row>
      </b-container>
      <b-container class="d-flex">
        <b-row class="my-1 flex-grow-1 d-flex justify-content-between">
          <b-col sm="3">
            <label>Active</label>
            <b-form-input v-model="organization.active_ts" readonly></b-form-input>
          </b-col>
          <b-col sm="3">
            <label>Created</label>
            <b-form-input v-model="organization.create_ts" readonly></b-form-input>
          </b-col>
        </b-row>
      </b-container>
      <div class="mb-4 pl-5 pr-5">
        <hr class="solid">
      </div>
      <div class="container mt-2">
        <h4>Members</h4>
      </div>
      <div class="container">
        you can edit the organization here. Add new members, change properties, etc.
      </div>
      <div class="container mt-2">
        <b-select v-model="candidates.selected" @change="optionSelected">
          <b-select-option disabled value="">Please select one</b-select-option>
          <option v-for="size in candidates.values" >{{ size }}</option>
        </b-select>
      </div>
      <div class="container mt-2">
        <b-list-group v-for="user in this.organization.users">
          <b-list-group-item>{{ user.name }}</b-list-group-item>
        </b-list-group>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "App",
  methods: {
    optionSelected(){
      this.organization.users.push(
          {name: this.candidates.selected}
      )

      const v = this.candidates.selected;
      this.candidates.values = this.candidates.values.filter(function(item) {
        return item !== v;
      });

      this.organization.selected = '';
      this.$forceUpdate();
    },
    onInputChange(value) {
      console.log(value)
    }
  },
  mounted() {

  },
  data() {
    return {
      organization: {
        name: '',
        id: '',
        active_ts: '',
        create_ts: '',
        users: [
          {name: "Max Mustermann1"},
          {name: "Max Mustermann2"},
          {name: "Max Mustermann3"},
          {name: "Max Mustermann4"},
          {name: "Max Mustermann5"},
        ]
      },
      candidates: {
        selected: '',
        values: ['Small', 'Medium', 'Large', 'Extra Large']
      },
    }
  }
}
</script>

<style scoped>

</style>