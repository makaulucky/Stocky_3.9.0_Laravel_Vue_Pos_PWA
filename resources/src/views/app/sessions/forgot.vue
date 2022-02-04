<template>
  <div class="auth-layout-wrap">
    <div class="auth-content">
      <div class="card o-hidden">
        <div class="row">
          <div class="col-md-12">
            <div class="p-4">
              <div class="auth-logo text-center mb-30">
                <img :src="'/images/logo.png'" alt>
              </div>
              <h1 class="mb-3 text-18">{{$t('Forgot_Password')}}</h1>
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
                  <button
                    type="submit"
                    :disabled="loading"
                    class="btn btn-primary btn-block btn-rounded mt-3"
                  >{{$t('Reset_Password')}}</button>
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
  metaInfo: {
    // if no subcomponents specify a metaInfo.title, this title will be used
    title: "Forgot Password"
  },
  data() {
    return {
      email: "",
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
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.loading = true;
      axios
        .post("/api/password/create", { email: this.email }
        )
        .then(result => {
          // this.response = result.data;

          if (result.data.status) {
            this.makeToast(
              "success",
              this.$t("We_have_emailed_your_password_reset_link"),
              this.$t("Success")
            );
       
          } else if (!result.data.status) {
            this.makeToast(
              "danger",
              this.$t("We_cant_find_a_user_with_that_email_addres"),
              this.$t("Failed")
            );
          }
          NProgress.done();
          this.loading = false;
        })
        .catch(error => {
          this.makeToast(
            "danger",
            this.$t("Failed_to_authenticate_on_SMTP_server"),
            this.$t("Failed")
          );
          NProgress.done();
          this.loading = false;
        });
    }
  }
};
</script>
