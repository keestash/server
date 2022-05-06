<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col p-0">
        <form enctype="multipart/form-data" novalidate v-if="isInitial || isSaving" class="d-flex flex-column">
          <div class="dropbox btn btn-primary flex-grow-1 text-white d-flex justify-content-center flex-column">
            <div class="text-center">
              <div class="h4" v-if="isInitial">
                {{ message }}
              </div>
              <div v-if="isSaving">
                {{ uploadingMessage() }}
              </div>
            </div>
            <input
                type="file"
                :name="uploadFieldName"
                :disabled="isSaving"
                @change="filesChange($event.target.name, $event.target.files); fileCount = $event.target.files.length"
                class="p-2 input-file"
                multiple
            >
          </div>
        </form>
        <!--SUCCESS-->
        <div v-if="isSuccess">
          <h2>{{ uploadedMessage() }}</h2>
          <p>
            <a href="javascript:void(0)" @click="reset()">Upload again</a>
          </p>
        </div>
        <!--FAILED-->
        <div v-if="isFailed">
          <h2>{{ $t('credential.detail.attachment.fileUpload.uploadFailedMessage') }}</h2>
          <p>
            <a href="javascript:void(0)" @click="reset()">Try again</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

const STATUS_INITIAL = 0, STATUS_SAVING = 1, STATUS_SUCCESS = 2, STATUS_FAILED = 3;
export default {
  name: 'FileUpload',
  props: {
    message: '',
  },
  data() {
    return {
      uploadedFiles: [],
      uploadError: null,
      currentStatus: null,
      uploadFieldName: 'photos',
      fileCount: 0
    }
  },
  computed: {
    isInitial() {
      return this.currentStatus === STATUS_INITIAL;
    },
    isSaving() {
      return this.currentStatus === STATUS_SAVING;
    },
    isSuccess() {
      return this.currentStatus === STATUS_SUCCESS;
    },
    isFailed() {
      return this.currentStatus === STATUS_FAILED;
    }
  },
  methods: {
    uploadingMessage() {
      return $t('credential.detail.attachment.fileUpload.uploadingMessage').replace('{number}', fileCount);
    },
    uploadedMessage() {
      return $t('credential.detail.attachment.fileUpload.uploadedMessage').replace('{number}', fileCount);
    },
    reset() {
      // reset form to initial state
      this.currentStatus = STATUS_INITIAL;
      this.uploadedFiles = [];
      this.uploadError = null;
    },
    save(formData) {
      // upload data to the server
      this.currentStatus = STATUS_SAVING;
      this.$emit("upload", formData);
      // upload(formData)
      //     .then(wait(1500)) // DEV ONLY: wait for 1.5s
      //     .then(x => {
      //       this.uploadedFiles = [].concat(x);
      //       this.currentStatus = STATUS_SUCCESS;
      //     })
      //     .catch(err => {
      //       this.uploadError = err.response;
      //       this.currentStatus = STATUS_FAILED;
      //     });
    },
    filesChange(fieldName, fileList) {
      // handle file changes
      const formData = new FormData();
      if (!fileList.length) return;
      // append the files to FormData
      Array
          .from(Array(fileList.length).keys())
          .map(x => {
            formData.append(fieldName, fileList[x], fileList[x].name);
          });
      // save it
      this.$emit("upload", formData);
    }
  },
  mounted() {
    this.reset();
  },
}
</script>

<style scoped lang="scss">

.input-file {
  opacity: 0;
  cursor: pointer;
}

</style>