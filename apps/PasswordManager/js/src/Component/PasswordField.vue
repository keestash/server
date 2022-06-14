<template>
  <div class="form-group">
    <small class="form-text text-muted">{{ $t('credential.detail.passwordLabel') }}</small>
    <div class="input-group">
      <input :type="passwordField.visible ? 'text' : 'password'" class="form-control"
             id="pwm__login__password"
             :readonly="!passwordField.visible"
             v-model="passwordField.value"
      >
      <div class="input-group-append" @click="eyeClick">
        <div class="input-group-text">
          <i class="fas fa-eye"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "PasswordField",
  data() {
    return {
      passwordField: {
        visible: false,
        value: null
      }
    }
  },
  methods: {
    eyeClick() {
      this.$emit("eyeClick", this.passwordField)
    }
  }
}
</script>

<style scoped>

</style>