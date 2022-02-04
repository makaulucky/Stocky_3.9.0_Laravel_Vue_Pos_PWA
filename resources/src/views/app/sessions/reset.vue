<template>
  <div class="auth-layout-wrap">
    <div class="auth-content">
      <div class="card o-hidden mt-5 mb-5">
        <div class="row">
          <div class="col-md-12">
            <div class="p-4">
              <div class="auth-logo text-center mb-30">
                <img :src="'/images/logo.png'" alt>
              </div>
              
              <h1 class="mb-3 text-18">{{$t('Reset_Password')}}</h1>
              <validation-observer ref="Reset_password">
                <b-form @submit.prevent="Submit_Reset">
                  <validation-provider
                    name="Email Address"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Email_Address')" class="text-12">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Email-feedback"
                        class="form-control-rounded"
                        type="text"
                        v-model="email"
                        email
                      ></b-form-input>
                      <b-form-invalid-feedback id="Email-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>

                  <validation-provider
                    name="password"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('password')" class="text-12">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="password-feedback"
                        class="form-control-rounded"
                        type="password"
                        v-model="password"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="password-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>

                  <validation-provider
                    name="confirmation"
                    rules="confirmed:password|required:true"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Confirm_password')" class="text-12">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="confirmation-feedback"
                        class="form-control-rounded"
                        type="password"
                        v-model="password_confirmation"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="confirmation-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>

                  <button
                    type="submit"
                    :disabled="loading"
                    class="btn btn-primary btn-block btn-rounded mt-3"
                  >{{$t('submit')}}</button>
                  <div v-once class="typo__p" v-if="loading">
                    <div class="spinner sm spinner-primary mt-3"></div>
                  </div>
                </b-form>
              </validation-observer>
              <div class="mt-3 text-center">
                <a href="/login"  class="text-muted">
                  <u>{{$t('SignIn')}}</u>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import NProgress from "nprogress";
export default {
  props: ['token'],
  metaInfo: {
    // if no subcomponents specify a metaInfo.title, this title will be used
    title: "Reset Password"
  },
  data() {
    return {
      token : this.token,
      email: null,
      password: null,
      password_confirmation: null,
      loading: false
    };
  },
  methods: {
    //------------- Submit Reset Password
    Submit_Reset() {
      this.$refs.Reset_password.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_Email_Adress"),
            this.$t("Failed")
          );
        } else {
          this.Reset_Password();
        }
      });
    },

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    Reset_Password() {
      let self = this;
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      self.loading = true;
      axios
        .post("/api/password/reset", {
          token: self.token,
          email: self.email,
          password: self.password,
          password_confirmation: self.password_confirmation
        })
        .then(response => {
          if (response.data.code === 1) {
            this.makeToast(
              "success",
              this.$t("Your_Password_has_been_changed"),
              this.$t("Success")
            );
            window.location = '/';
          } else if (response.data.code === 2) {
            this.makeToast(
              "danger",
              this.$t("We_cant_find_a_user_with_that_email_addres"),
              this.$t("Failed")
            );
          } else if (response.data.code === 3) {
            this.makeToast(
              "danger",
              this.$t("This_password_reset_token_is_invalid"),
              this.$t("Failed")
            );
          }
          NProgress.done();
          this.loading = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          NProgress.done();
          this.loading = false;
        });
    }
  }
};
</script>